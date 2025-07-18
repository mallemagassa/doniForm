<script setup lang="ts">
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import KizzaTable from '@/components/ui/data-table/KizzaTable.vue';
import KizzaModal from '@/components/ui/KizzaModal.vue';
import Button from '@/components/ui/button/Button.vue';
import { ArrowUpDown } from 'lucide-vue-next';
import { computed, ref, h, watch } from 'vue';

const props = defineProps({
  table: {
    type: Object,
    required: true
  },
  resource: {
    type: Object,
    required: true
  }
});

const editingNote = ref(null);
const selectedItem = ref(null);
const currentEditingIndex = ref(null);
const isLoading = ref(false);
const flash = computed(() => usePage().props.flash);
const titleModal = "Grille d évaluation - Préséselection"

const noteForm = useForm({
  note: '0',
  note_item_id: null,
  grille_item_id: null,
  jury_index: null
});

// Watch for flash messages to handle success
watch(flash, (newFlash) => {
  if (newFlash.success) {
    // Update local data when note is saved
    if (newFlash.noteItem && selectedItem.value?.program?.grille_labels) {
      updateLocalNotes(newFlash.noteItem);
    }
    editingNote.value = null;
    currentEditingIndex.value = null;
    noteForm.reset();
  }
});

const onRowClick = (item: any) => {
  selectedItem.value = item;
}

const closeModal = () => {
  selectedItem.value = null;
  editingNote.value = null;
  currentEditingIndex.value = null;
}

const formattedColumns = computed(() => {
  const defaultSelectColumn = {
    id: 'select',
    header: ({ table }) =>
      h('input', {
        type: 'checkbox',
        checked: table.getIsAllPageRowsSelected(),
        onChange: () => table.toggleAllPageRowsSelected()
      }),
    cell: ({ row }) =>
      h('input', {
        type: 'checkbox',
        checked: row.getIsSelected(),
        onChange: () => row.toggleSelected()
      }),
    enableSorting: false,
    enableHiding: false,
  }

  const dynamicColumns = Object.entries(props.table.columns).map(([key, label]) => {
    const isRelation = key.includes('.');
    return {
      accessorKey: key,
      header: ({ column }) =>
        h(Button, {
          variant: 'ghost',
          class: 'bg-[#2755a1] text-white',
          onClick: () => column.toggleSorting(column.getIsSorted() === 'asc'),
        }, () => [label, h(ArrowUpDown, { class: 'ml-2 h-4 w-4' })]),
      cell: ({ row }) => isRelation ? row.original[key] ?? 'N/A' : row.getValue(key)
    }
  })

  return [defaultSelectColumn, ...dynamicColumns]
})

// Calcul de la moyenne pour un item
const calculateItemAverage = (item) => {
  if (!item.note_items || item.note_items.length === 0) return '0.00';
  const sum = item.note_items.reduce((total, note) => total + Number(note.note), 0);
  return (sum / item.note_items.length).toFixed(2);
};

// Calcul de la moyenne pour une grille
const calculateGrilleAverage = (items) => {
  if (!items || items.length === 0) return '0.00';
  
  const sum = items.reduce((total, item) => {
    if (!item.note_items || item.note_items.length === 0) return total;
    const itemSum = item.note_items.reduce((sum, note) => sum + Number(note.note), 0);
    return total + (itemSum / item.note_items.length);
  }, 0);
  
  return (sum / items.length).toFixed(2);
};

// Calcul de la moyenne globale
const calculateGlobalAverage = (grilles) => {
  let totalSum = 0;
  let totalItems = 0;

  grilles.forEach(grille => {
    if (grille.grille_items && grille.grille_items.length > 0) {
      grille.grille_items.forEach(item => {
        if (item.note_items && item.note_items.length > 0) {
          const itemSum = item.note_items.reduce((sum, note) => sum + Number(note.note), 0);
          totalSum += itemSum / item.note_items.length;
          totalItems++;
        }
      });
    }
  });

  return totalItems > 0 ? (totalSum / totalItems).toFixed(2) : '0.00';
};

const startEditing = (noteItem, grilleItem, juryIndex) => {
  editingNote.value = noteItem ? noteItem.id : `new-${grilleItem.id}-${juryIndex}`;
  currentEditingIndex.value = juryIndex;
  
  noteForm.note = noteItem ? noteItem.note : '0';
  noteForm.note_item_id = noteItem ? noteItem.id : null;
  noteForm.grille_item_id = grilleItem.id;
  noteForm.jury_index = juryIndex;
};

const validateNote = (note, maxNote) => {
  const num = parseFloat(note);
  return !isNaN(num) && num >= 0 && num <= maxNote;
};

const saveNote = (grilleItem) => {
  if (!validateNote(noteForm.note, grilleItem.base_notation)) {
    alert(`La note doit être entre 0 et ${grilleItem.base_notation}`);
    return;
  }

  if (isLoading.value) return;
  isLoading.value = true;

  const url = noteForm.note_item_id 
    ? `/note-items/${noteForm.note_item_id}` 
    : '/note-items';
  
  const method = noteForm.note_item_id ? 'put' : 'post';
  
  // Fermer immédiatement le champ en cours d'édition
  editingNote.value = null;
  currentEditingIndex.value = null;
  
  noteForm[method](url, {
    preserveScroll: true,
    preserveState: true,
    onSuccess: () => {
      isLoading.value = false;
    },
    onError: (errors) => {
      console.error('Erreur lors de la sauvegarde:', errors);
      alert("Une erreur s'est produite lors de l'enregistrement");
      // Rétablir l'édition en cas d'erreur
      editingNote.value = noteForm.note_item_id || `new-${noteForm.grille_item_id}-${noteForm.jury_index}`;
      currentEditingIndex.value = noteForm.jury_index;
      isLoading.value = false;
    }
  });
};

const updateLocalNotes = (newNoteItem) => {
  if (!selectedItem.value?.program?.grille_labels) return;

  selectedItem.value.program.grille_labels.forEach(grille => {
    grille.grille_items.forEach(item => {
      if (item.id === newNoteItem.grille_item_id) {
        if (!item.note_items) item.note_items = [];
        
        if (newNoteItem.id) {
          // Mettre à jour ou ajouter la note
          const existingIndex = item.note_items.findIndex(n => n.id === newNoteItem.id);
          if (existingIndex >= 0) {
            item.note_items[existingIndex] = newNoteItem;
          } else {
            item.note_items.push(newNoteItem);
          }
        }
      }
    });
  });
};

const cancelEditing = () => {
  editingNote.value = null;
  currentEditingIndex.value = null;
  noteForm.reset();
};
</script>

<template>
  <Head :title="resource.label" />
  <AppLayout>
    <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
      <div v-if="flash.message" class="mb-4 p-2 bg-green-100 text-green-800 rounded">
        {{ flash.message }}
      </div>
      
      <div class="flex justify-between items-center">
        <h1 class="text-2xl font-bold">{{ resource.label }}</h1>
      </div>
      <KizzaTable 
        :data="table.records.data"
        :columns="formattedColumns"
        :routes="resource.routes"
        @row-click="onRowClick"
      />
    </div>

    <KizzaModal :open="!!selectedItem" @close="closeModal" :title="titleModal" size="xl">
      <div v-if="selectedItem" class="p-6">
        <div v-if="!selectedItem.program?.grille_labels" class="text-red-500 mb-4">
          Aucune grille d'évaluation trouvée pour ce programme
        </div>

        <template v-else>
          <div class="mb-4">
            <span class="font-semibold">Nombre de membres du jury:</span> 
            {{ selectedItem.program.nbr_membre_jury }}
          </div>
          
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-4 py-2 text-left text-sm min-w-[200px]">Critère</th>
                <th 
                  v-for="n in selectedItem.program.nbr_membre_jury" 
                  :key="n"
                  class="px-4 py-2 text-left text-sm min-w-[100px]"
                >
                  Note {{ n }}
                </th>
                <th class="px-4 py-2 text-left text-sm min-w-[100px]"></th>
                <th class="px-4 py-2 text-left text-sm min-w-[100px]">Moyenne</th>
              </tr>
            </thead>
            <tbody>
              <template v-for="grille in selectedItem.program.grille_labels" :key="grille.id">
                <tr class="bg-gray-100">
                  <td colspan="4" class="px-4 py-2 font-semibold">
                    {{ grille.nom }}
                  </td>
                  <td colspan="2" class="px-4 py-2 text-right font-medium">
                    Moyenne: {{ calculateGrilleAverage(grille.grille_items) }}
                  </td>
                </tr>
                
                <template v-if="grille.grille_items && grille.grille_items.length > 0">
                  <tr v-for="item in grille.grille_items" :key="item.id" class="hover:bg-gray-50 border-b">
                    <td class="px-4 py-2">{{ item.titre }}</td>
                    
                    <template v-for="(_, juryIndex) in selectedItem.program.nbr_membre_jury" :key="juryIndex">
                      <td class="px-4 py-2">
                        <template v-if="editingNote === (item.note_items[juryIndex]?.id || `new-${item.id}-${juryIndex}`)">
                          <div class="flex items-center">
                            <input
                              v-model="noteForm.note"
                              type="number"
                              min="0"
                              :max="item.base_notation"
                        
                              class="w-20 border rounded px-2 py-1"
                              @keyup.enter="saveNote(item)"
                              @blur="saveNote(item)"
                              autofocus
                              :disabled="isLoading"
                            />
                            <button 
                              @click="saveNote(item)"
                              class="ml-2 bg-blue-500 text-white px-2 py-1 rounded"
                              :disabled="isLoading"
                            >
                              <span v-if="!isLoading">✓</span>
                              <span v-else>...</span>
                            </button>
                            <button 
                              @click="cancelEditing"
                              class="ml-2 bg-gray-300 text-gray-800 px-2 py-1 rounded"
                              :disabled="isLoading"
                            >
                              ×
                            </button>
                          </div>
                        </template>
                        <template v-else>
                          <span 
                            @click="startEditing(item.note_items[juryIndex], item, juryIndex)"
                            class="cursor-pointer hover:bg-gray-200 px-2 py-1 rounded"
                            :class="{ 'text-gray-400': !item.note_items[juryIndex] }"
                          >
                            {{ item.note_items[juryIndex]?.note || 0 }}
                          </span>
                        </template>
                      </td>
                    </template>
                    
                    <td class="px-4 py-2">/{{ item.base_notation }}</td>
                    <td class="px-4 py-2">{{ calculateItemAverage(item) }}</td>
                  </tr>
                </template>
                <tr v-else>
                  <td :colspan="3 + selectedItem.program.nbr_membre_jury" class="px-4 py-2 text-gray-500 text-center">
                    Aucun critère défini dans cette grille
                  </td>
                </tr>
              </template>
            </tbody>
            <tfoot>
              <tr class="bg-blue-50 font-semibold border-t-2 border-blue-200">
                <td :colspan="1 + selectedItem.program.nbr_membre_jury" class="px-4 py-3">Total Général</td>
                <td class="px-4 py-3">/5</td>
                <td class="px-4 py-3">{{ calculateGlobalAverage(selectedItem.program.grille_labels) }}</td>
              </tr>
            </tfoot>
          </table>
        </template>
      </div>
    </KizzaModal>
  </AppLayout>
</template>