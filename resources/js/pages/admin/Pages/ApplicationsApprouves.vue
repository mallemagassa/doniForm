<!--- index.vue.stub -->
<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import KizzaTable from '@/components/ui/data-table/KizzaTable.vue';
import KizzaModal from '@/components/ui/KizzaModal.vue';
import Button from '@/components/ui/button/Button.vue';
import { ArrowUpDown } from 'lucide-vue-next';
import { computed, ref, h } from 'vue';

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

const selectedItem = ref(null)
const onRowClick = (item: any) => {
  selectedItem.value = item
  console.log('Selected item:', selectedItem.value)
}
const closeModal = () => {
  selectedItem.value = null
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

const formatDate = (dateString) => {
  if (!dateString) return 'N/A';
  return new Date(dateString).toLocaleDateString('fr-FR');
};

// Calcul de la moyenne pour un item
const calculateItemAverage = (item) => {
  const average = (Number(item.note_1) + Number(item.note_2) + Number(item.note_3)) / 3;
  return average.toFixed(2);
};

// Calcul de la moyenne pour une grille
const calculateGrilleAverage = (items) => {
  if (!items || items.length === 0) return '0.00';
  
  const sum = items.reduce((total, item) => {
    return total + (Number(item.note_1) + Number(item.note_2) + Number(item.note_3)) / 3;
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
        totalSum += (Number(item.note_1) + Number(item.note_2) + Number(item.note_3)) / 3;
        totalItems++;
      });
    }
  });

  return totalItems > 0 ? (totalSum / totalItems).toFixed(2) : '0.00';
};



</script>


<template>
  <Head :title="resource.label" />
  <AppLayout>
    <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
      <div class="flex justify-between items-center">
        <h1 class="text-2xl font-bold">{{ resource.label }}</h1>
        <!-- <Link :href="resource.routes.create">
          <Button variant="default">
            Créer {{ resource.label }}
          </Button>
        </Link> -->
      </div>
      <KizzaTable 
        :data="table.records.data"
        :columns="formattedColumns"
        :routes="resource.routes"
        @row-click="onRowClick"
      />
    </div>

    <!-- Modale -->
    <KizzaModal :open="!!selectedItem" @close="closeModal" size="xl">
  <div v-if="selectedItem" class="p-6">
    <!-- Vérification des données -->
    <div v-if="!selectedItem.program?.grille_labels" class="text-red-500 mb-4">
      Aucune grille d'évaluation trouvée pour ce programme
    </div>

    <!-- Tableau unifié pour toutes les grilles -->
    <template v-else>
      <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
          <tr>
            <th class="px-4 py-2 text-left text-sm min-w-[200px]">Critère</th>
            <th class="px-4 py-2 text-left text-sm min-w-[100px]">Note 1</th>
            <th class="px-4 py-2 text-left text-sm min-w-[100px]">Note 2</th>
            <th class="px-4 py-2 text-left text-sm min-w-[100px]">Note 3</th>
            <th class="px-4 py-2 text-left text-sm min-w-[100px]"></th>
            <th class="px-4 py-2 text-left text-sm min-w-[100px]">Moyenne</th>
          </tr>
        </thead>
        <tbody>
          <template v-for="grille in selectedItem.program.grille_labels" :key="grille.id">
            <!-- Ligne de groupe pour le nom de la grille -->
            <tr class="bg-gray-100">
              <td colspan="4" class="px-4 py-2 font-semibold">
                {{ grille.nom }}
              </td>
              <td colspan="2" class="px-4 py-2 text-right font-medium">
                Moyenne: {{ calculateGrilleAverage(grille.grille_items) }}/5
              </td>
            </tr>
            
            <!-- Items de la grille -->
            <template v-if="grille.grille_items && grille.grille_items.length > 0">
              <tr v-for="item in grille.grille_items" :key="item.id" class="hover:bg-gray-50 border-b">
                <td class="px-4 py-2">{{ item.titre }}</td>
                <td class="px-4 py-2">{{ item.note_1 }}</td>
                <td class="px-4 py-2">{{ item.note_2 }}</td>
                <td class="px-4 py-2">{{ item.note_3 }}</td>
                <td class="px-4 py-2">/5</td>
                <td class="px-4 py-2">{{ calculateItemAverage(item) }}</td>
              </tr>
            </template>
            <tr v-else>
              <td colspan="6" class="px-4 py-2 text-gray-500 text-center">
                Aucun critère défini dans cette grille
              </td>
            </tr>
          </template>
        </tbody>
        <tfoot>
          <tr class="bg-blue-50 font-semibold border-t-2 border-blue-200">
            <td colspan="4" class="px-4 py-3">Total Général</td>
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
