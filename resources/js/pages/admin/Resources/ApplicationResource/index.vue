<!-- index.vue -->
<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import KizzaTable from '@/components/ui/data-table/KizzaTable.vue';
import type { ColumnDef } from '@tanstack/vue-table';
import { computed, h, ref, watch } from 'vue'
import { Checkbox } from '@/components/ui/checkbox'
import DropdownAction from '@/components/ui/data-table/DataTableDemoColumn.vue'
import Button from '@/components/ui/button/Button.vue';
import { ArrowUpDown } from 'lucide-vue-next'
import KizzaModal from '@/components/ui/KizzaModal.vue';
import axios from 'axios'
import { debounce } from 'lodash-es'

interface Application {
  user_id: string;
  program_id: string;
  submitted_at: string | Date;
  status: string;
  notes: string;
  form_data: any;
}

interface Program {
  id: string;
  title: string;
}

interface FormField {
  id: string;
  label: string;
  field_type: string;
  required: boolean;
  options?: any[];
}

const currentProgramFields = ref<FormField[]>([])

const updateFileRequirements = async () => {
  if (!importData.value.program_id) {
    currentProgramFields.value = []
    return
  }

  try {
    const response = await axios.get(`/api/programs/${importData.value.program_id}/fields`)
    currentProgramFields.value = response.data.fields
  } catch (error) {
    console.error("Erreur lors de la récupération des champs:", error)
    currentProgramFields.value = []
  }
}

const isModalOpen = ref(false)
const isImporting = ref(false)
const fileInput = ref<HTMLInputElement | null>(null)
const titleModal = "Importation des candidatures"

const importData = ref({
  program_id: '',
  status: 'pending',
  notes: '',
  file: null as File | null
})

const handleFileChange = (e: Event) => {
  const target = e.target as HTMLInputElement
  if (target.files && target.files[0]) {
    importData.value.file = target.files[0]
  }
}

const handleImport = async () => {
  if (!importData.value.file || !importData.value.program_id) return
  
  isImporting.value = true
  
  try {
    const formData = new FormData()
    formData.append('file', importData.value.file)
    formData.append('program_id', importData.value.program_id)
    formData.append('status', importData.value.status)
    if (importData.value.notes) {
      formData.append('notes', importData.value.notes)
    }

    await router.post('/import-app', formData, {
      onSuccess: () => {
        closeModal()
      },
      onError: (errors) => {
        console.error('Erreurs:', errors)
      }
    })
  } catch (error) {
    console.error('Erreur lors de l\'import:', error)
  } finally {
    isImporting.value = false
  }
}

const toggleModal = () => {
  isModalOpen.value = !isModalOpen.value
}

const closeModal = () => {
  isModalOpen.value = false
  importData.value = {
    program_id: '',
    status: 'pending',
    notes: '',
    file: null
  }
  if (fileInput.value) {
    fileInput.value.value = ''
  }
}

const props = defineProps({
  table: {
    type: Object,
    required: true
  },
  programs: {
    type: Array,
    required: true
  },
  resource: {
    type: Object,
    required: true
  },
  search: {
    type: String,
    default: ''
  },
  sort: {
    type: Object,
    default: () => ({ field: 'created_at', direction: 'desc' })
  }
});

const formattedColumns = computed(() => {
  const defaultSelectColumn = {
    id: 'select',
    header: ({ table }) =>
      h(Checkbox, {
        modelValue: table.getIsAllPageRowsSelected() || 
          (table.getIsSomePageRowsSelected() && 'indeterminate'),
        'onUpdate:modelValue': value => table.toggleAllPageRowsSelected(!!value),
        ariaLabel: 'Select all',
      }),
    cell: ({ row }) =>
      h(Checkbox, {
        modelValue: row.getIsSelected(),
        'onUpdate:modelValue': value => row.toggleSelected(!!value),
        ariaLabel: 'Select row',
      }),
    enableSorting: false,
    enableHiding: false,
  };

  const dynamicColumns = Object.entries(props.table.columns ?? {}).map(([key, label]) => {
      const originalKey = key.replace(/_/g, '.');
      const isRelation = originalKey.includes('.');
      
      return {
          accessorKey: key,
          filterFn: 'includesString',
          header: ({ column }) =>
              h(Button, {
                  variant: 'ghost',
                  class: 'bg-[#2755a1] text-white',
                  onClick: () => column.toggleSorting(column.getIsSorted() === 'asc'),
              }, () => [label, h(ArrowUpDown, { class: 'ml-2 h-4 w-4' })]),
          cell: ({ row }) => {
              if (isRelation) {
                  return row.original[key] ?? 'N/A';
              }
              return row.getValue(key);
          },
      };
  });

  const defaultActionsColumn = {
    id: 'actions',
    enableHiding: false,
    cell: ({ row }) => {
      const application = row.original
      return h(DropdownAction, {
          payment: application,
          routes: {
            destroy: props.resource.routes.destroy,
            index: props.resource.routes.index,
            show: props.resource.routes.show
          },
          onExpand: row.toggleExpanded
      })
    }
  }

  return [defaultSelectColumn, ...dynamicColumns, defaultActionsColumn];
});

console.log(props.table)
</script>

<template>
  <Head :title="resource.label" />
  <AppLayout>
    <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
      <div class="flex justify-between items-center">
        <h1 class="text-2xl font-bold">{{ resource.label }}</h1>
        <div class="flex gap-2">
          <Button @click="toggleModal" variant="default">
            Importer Candidatures
          </Button>
        </div>
      </div>
      <KizzaTable 
        :data="table.records.data"
        :columns="formattedColumns"
        :routes="resource.routes"
      />

      <!-- Dans votre template, avant le KizzaModal -->
    <div class="fixed inset-0 flex items-end px-4 py-6 pointer-events-none sm:p-6 sm:items-start z-50">
      <div class="w-full flex flex-col items-center space-y-4">
        <transition
          enter-active-class="transform ease-out duration-300 transition"
          enter-from-class="translate-y-2 opacity-0 sm:translate-y-0 sm:translate-x-2"
          enter-to-class="translate-y-0 opacity-100 sm:translate-x-0"
          leave-active-class="transition ease-in duration-100"
          leave-from-class="opacity-100"
          leave-to-class="opacity-0"
        >
          <div 
            v-if="$page.props.flash.success"
            class="max-w-sm w-full bg-white shadow-lg rounded-lg pointer-events-auto ring-1 ring-black ring-opacity-5 overflow-hidden"
          >
            <div class="p-4">
              <div class="flex items-start">
                <div class="flex-shrink-0">
                  <svg class="h-6 w-6 text-green-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                  </svg>
                </div>
                <div class="ml-3 w-0 flex-1 pt-0.5">
                  <p class="text-sm font-medium text-gray-900">
                    {{ $page.props.flash.success }}
                  </p>
                  <p v-if="$page.props.flash.imported_count" class="mt-1 text-sm text-gray-500">
                    {{ $page.props.flash.imported_count }} candidatures importées avec succès
                  </p>
                </div>
                <div class="ml-4 flex-shrink-0 flex">
                  <button @click="$page.props.flash.success = null" class="bg-white rounded-md inline-flex text-gray-400 hover:text-gray-500 focus:outline-none">
                    <span class="sr-only">Fermer</span>
                    <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                      <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                    </svg>
                  </button>
                </div>
              </div>
            </div>
          </div>
        </transition>
      </div>
    </div>

    <transition
      enter-active-class="transform ease-out duration-300 transition"
      enter-from-class="translate-y-2 opacity-0 sm:translate-y-0 sm:translate-x-2"
      enter-to-class="translate-y-0 opacity-100 sm:translate-x-0"
      leave-active-class="transition ease-in duration-100"
      leave-from-class="opacity-100"
      leave-to-class="opacity-0"
    >
      <div 
        v-if="$page.props.flash.error"
        class="max-w-sm w-full bg-white shadow-lg rounded-lg pointer-events-auto ring-1 ring-black ring-opacity-5 overflow-hidden"
      >
        <div class="p-4">
          <div class="flex items-start">
            <div class="flex-shrink-0">
              <svg class="h-6 w-6 text-red-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
            </div>
            <div class="ml-3 w-0 flex-1 pt-0.5">
              <p class="text-sm font-medium text-gray-900">
                Erreur lors de l'importation
              </p>
              <p class="mt-1 text-sm text-gray-500">
                {{ $page.props.flash.error }}
              </p>
            </div>
            <div class="ml-4 flex-shrink-0 flex">
              <button @click="$page.props.flash.error = null" class="bg-white rounded-md inline-flex text-gray-400 hover:text-gray-500 focus:outline-none">
                <span class="sr-only">Fermer</span>
                <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                  <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                </svg>
              </button>
            </div>
          </div>
        </div>
      </div>
    </transition>

      <KizzaModal :open="isModalOpen" @close="closeModal" :title="titleModal" size="xl">
        <form @submit.prevent="handleImport" class="space-y-4">
          <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
            <div>
              <label for="program_id" class="block text-sm font-medium text-gray-700">Programme *</label>
              <select 
                id="program_id" 
                v-model="importData.program_id"
                class="mt-1 block w-full rounded-md border border-gray-300 py-2 px-3 shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-indigo-500 sm:text-sm"
                required
                @change="updateFileRequirements"
              >
                <option value="" disabled>Sélectionnez un programme</option>
                <option 
                  v-for="program in programs" 
                  :key="program.id" 
                  :value="program.id"
                >
                  {{ program.title }}
                </option>
              </select>
            </div>
            
            <div>
              <label for="status" class="block text-sm font-medium text-gray-700">Statut *</label>
              <select 
                id="status" 
                v-model="importData.status"
                class="mt-1 block w-full rounded-md border border-gray-300 py-2 px-3 shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-indigo-500 sm:text-sm"
                required
              >
                <option value="pending">En attente</option>
                <option value="approved">Approuvé</option>
                <option value="rejected">Rejeté</option>
              </select>
            </div>
          </div>

          <div>
            <label for="notes" class="block text-sm font-medium text-gray-700">Notes</label>
            <textarea
              id="notes"
              v-model="importData.notes"
              rows="3"
              class="mt-1 block w-full rounded-md border border-gray-300 py-2 px-3 shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-indigo-500 sm:text-sm"
              placeholder="Notes optionnelles sur l'import..."
            ></textarea>
          </div>

          <div>
            <label for="file" class="block text-sm font-medium text-gray-700">Fichier Excel *</label>
            <input
              type="file"
              id="file"
              ref="fileInput"
              accept=".xlsx,.xls,.csv"
              class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100"
              required
              @change="handleFileChange"
            />
          </div>

          <!-- Section Exigences et Exemples -->
          <div class="rounded-lg bg-gray-50 p-4">
            <h3 class="font-medium text-gray-900 mb-2">Exigences du fichier</h3>
            
            <div v-if="currentProgramFields.length > 0">
              <p class="text-sm text-gray-700 mb-2">
                Pour le programme sélectionné, votre fichier doit contenir ces colonnes :
              </p>
              <ul class="list-disc pl-5 text-sm text-gray-600 mb-3">
                <li v-for="field in currentProgramFields" :key="field.id">
                  <span class="font-mono bg-gray-200 px-1 rounded">{{ field.label }}</span>
                  ({{ field.field_type }})
                </li>
                <li class="font-mono bg-gray-200 px-1 rounded">num_candidat</li>
              </ul>
            </div>
            <p v-else class="text-sm text-gray-700 mb-3">
              Sélectionnez un programme pour voir les champs requis.
            </p>

            <div class="mt-3">
              <h4 class="text-sm font-medium text-gray-900 mb-1">Télécharger des exemples :</h4>
              <div class="flex gap-2">
                <a 
                  href="/templates/import_candidatures_exemple.xlsx" 
                  download
                  class="inline-flex items-center px-3 py-1 border border-transparent text-xs font-medium rounded shadow-sm text-white bg-green-600 hover:bg-green-700 focus:outline-none"
                >
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                  </svg>
                  Modèle Excel
                </a>
                <a 
                  href="/templates/import_candidatures_exemple.csv" 
                  download
                  class="inline-flex items-center px-3 py-1 border border-transparent text-xs font-medium rounded shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none"
                >
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                  </svg>
                  Modèle CSV
                </a>
              </div>
            </div>

            <div class="mt-3 text-xs text-gray-500">
              <p><strong>Note importante :</strong></p>
              <ul class="list-disc pl-5 mt-1">
                <li>La première ligne doit contenir les en-têtes de colonnes</li>
                <li>Les noms de colonnes doivent correspondre exactement aux champs requis</li>
                <li>Le fichier ne doit pas dépasser 2 Mo</li>
                <li>Les formats acceptés sont .xlsx, .xls et .csv</li>
              </ul>
            </div>
          </div>

          <div class="flex justify-end space-x-3 pt-4">
            <Button
              type="button"
              @click="closeModal"
              variant="outline"
            >
              Annuler
            </Button>
            <Button
              type="submit"
              variant="default"
              :disabled="isImporting || !importData.program_id"
            >
              <span v-if="isImporting">Importation en cours...</span>
              <span v-else>Importer</span>
            </Button>
          </div>
        </form>
      </KizzaModal>
    </div>
  </AppLayout>
</template>