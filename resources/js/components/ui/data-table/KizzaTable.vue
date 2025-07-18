<script setup lang="ts">
import type {
  ColumnDef,
  ColumnFiltersState,
  ExpandedState,
  SortingState,
  VisibilityState,
} from '@tanstack/vue-table'
import {
  FlexRender,
  getCoreRowModel,
  getExpandedRowModel,
  getFilteredRowModel,
  getPaginationRowModel,
  getSortedRowModel,
  useVueTable,
} from '@tanstack/vue-table'

import { h, ref, computed, watch } from 'vue'
import { debounce } from 'lodash-es'
import { valueUpdater } from '@/lib/utils'
import { router, usePage } from '@inertiajs/vue3'

import { ArrowUpDown, ChevronDown, Filter } from 'lucide-vue-next'
import { Button } from '@/components/ui/button'
import { Checkbox } from '@/components/ui/checkbox'
import {
  DropdownMenu,
  DropdownMenuCheckboxItem,
  DropdownMenuContent,
  DropdownMenuLabel,
  DropdownMenuSeparator,
  DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu'
import { Input } from '@/components/ui/input'
import {
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
} from '@/components/ui/table'

import type { PropType } from 'vue'

const emits = defineEmits(['row-click'])

const props = defineProps({
  data: {
    type: Array,
    required: true,
  },
  columns: {
    type: Array as PropType<ColumnDef<any>[]>,
    required: true,
  },
  routes: {
    type: Object as PropType<{ index: string; create?: string }>,
    required: true,
  },
  filters: {
    type: Object as PropType<Record<string, string>>,
    required: true
  },
  search: {
    type: String,
    default: '',
  },
  pagination: {
    type: Object as PropType<{
      current_page: number
      per_page: number
      total: number
      last_page?: number
    }>,
    default: () => ({ current_page: 1, per_page: 10, total: 0 }),
  },
})

const page = usePage()
const sorting = ref<SortingState>([])
const globalFilter = ref(page.props.search || '')
const columnVisibility = ref<VisibilityState>({})
const rowSelection = ref({})
const expanded = ref<ExpandedState>({})
const tableData = ref(props.data.data)


// Initialisation des filtres avec conversion des noms de colonnes
const columnFilters = ref<ColumnFiltersState>(
  Object.entries(props.filters || {}).map(([id, value]) => ({
    id: id.includes('.') ? id.replace(/\./g, '_') : id,
    value
  })
))

// Contrôle des mises à jour
const shouldUpdate = ref(false)
setTimeout(() => { shouldUpdate.value = true }, 500)

let isUpdating = false
const currentCancelToken = ref<any>(null)

const updateServerFilters = debounce(() => {
  if (!shouldUpdate.value || isUpdating) return;
  isUpdating = true;

  // Convertir les filtres en format backend
  const backendFilters = {} as Record<string, string>;
    // console.log("log log11111",columnFilters);
  columnFilters.value.forEach(filter => {
    if (filter.value && filter.value.toString().trim() !== '') {
      const originalKey = filter.id.includes('_') 
        ? filter.id.replace(/_/g, '_') 
        : filter.id;
      backendFilters[originalKey] = filter.value.toString();
    }
  });

  // Préparer les paramètres de requête
  const queryParams = {
    page: 1,
    per_page: props.pagination.per_page,
    search: globalFilter.value,
    ...(Object.keys(backendFilters).length > 0 && { filters: backendFilters })
  };

  router.get(props.routes.index, queryParams, {
    preserveState: true,
    preserveScroll: true,
    replace: true,
    only: ['table', 'filters', 'search', 'pagination'],
    onCancelToken: (cancelToken) => {
      currentCancelToken.value = cancelToken;
    },
    onFinish: () => {
      isUpdating = false;
    }
  });
}, 800);

// Watcher pour les props.filters
watch(() => props.filters, (newFilters) => {
  shouldUpdate.value = false
  // console.log("log log11111",columnFilters);
  columnFilters.value = Object.entries(newFilters || {}).map(([id, value]) => ({
    id: id.includes('.') ? id.replace(/\./g, '_') : id,
    value
  }))
  setTimeout(() => { shouldUpdate.value = true }, 100)
}, { deep: true })

// Watchers pour les changements locaux
watch([globalFilter, columnFilters], () => {
  if (!shouldUpdate.value) return
  if (currentCancelToken.value) {
    currentCancelToken.value.cancel('Nouvelle requête déclenchée')
  }
  updateServerFilters()
}, { deep: true })

const table = useVueTable({
  data: tableData,
  columns: props.columns,
  pageCount: computed(() => Math.ceil(props.pagination.total / props.pagination.per_page)),
  state: {
    get sorting() { return sorting.value },
    get columnFilters() { return columnFilters.value },
    get columnVisibility() { return columnVisibility.value },
    get rowSelection() { return rowSelection.value },
    get expanded() { return expanded.value },
    get globalFilter() { return globalFilter.value },
  },
  onSortingChange: updater => valueUpdater(updater, sorting),
  onColumnFiltersChange: updater => valueUpdater(updater, columnFilters),
  onColumnVisibilityChange: updater => valueUpdater(updater, columnVisibility),
  onRowSelectionChange: updater => valueUpdater(updater, rowSelection),
  onExpandedChange: updater => valueUpdater(updater, expanded),
  onGlobalFilterChange: updater => valueUpdater(updater, globalFilter),
  getCoreRowModel: getCoreRowModel(),
  getFilteredRowModel: getFilteredRowModel(),
  getSortedRowModel: getSortedRowModel(),
  getPaginationRowModel: getPaginationRowModel(),
  getExpandedRowModel: getExpandedRowModel(),
  manualPagination: true,
  manualFiltering: true,
})

const goToPage = (page: number) => {
  
  
  const filters = columnFilters.value.reduce((acc, filter) => {
    const originalKey = filter.id.includes('_') ? filter.id.replace(/_/g, '.') : filter.id
    if (filter.value) acc[originalKey] = filter.value
    return acc
  }, {} as Record<string, string>)

  router.get(props.routes.index, {
    filters,
    search: globalFilter.value,
    page,
    per_page: props.pagination.per_page,
  }, {
    preserveState: true,
    preserveScroll: true,
    replace: true,
    only: ['table', 'filters', 'search', 'pagination'],
  })
}

watch(() => props.data, (newData) => {
  console.log('Nouvelles données reçues:', newData);
  tableData.value = newData.data;
}, { deep: true });


watch(props.columns, (newColumns) => {
  table.setOptions(prev => ({
    ...prev,
    columns: newColumns
  }))
}, { immediate: true })


</script>

<template>
  <div class="w-full">
    <div class="flex flex-col sm:flex-row items-start sm:items-center gap-4 py-4">
      <Input
        v-model="globalFilter"
        placeholder="Recherche globale..."
        class="max-w-md"
      />

      <DropdownMenu>
        <DropdownMenuTrigger as-child>
          <Button variant="outline" class="ml-auto">
            <Filter class="mr-2 h-4 w-4" />
            Filtres
          </Button>
        </DropdownMenuTrigger>
        <DropdownMenuContent class="w-56">
          <DropdownMenuLabel>Filtres par colonne</DropdownMenuLabel>
          <DropdownMenuSeparator />
          <div v-for="column in table.getAllColumns()" :key="column.id">
            <div v-if="column.getCanFilter() && column.id !== 'select' && column.id !== 'actions'" class="px-2 py-1">
              <Input
                :model-value="column.getFilterValue() as string"
                @update:model-value="column.setFilterValue($event)"
                :placeholder="`Filtrer ${column.id}...`"
                class="h-8"
              />
            </div>
          </div>
        </DropdownMenuContent>
      </DropdownMenu>

      <DropdownMenu>
        <DropdownMenuTrigger as-child>
          <Button variant="default">
            Colonnes <ChevronDown class="ml-2 h-4 w-4" />
          </Button>
        </DropdownMenuTrigger>
        <DropdownMenuContent align="end">
          <DropdownMenuCheckboxItem
            v-for="column in table.getAllColumns().filter((col) => col.getCanHide())"
            :key="column.id"
            :checked="column.getIsVisible()"
            @update:checked="column.toggleVisibility($event)"
          >
            {{ column.id }}
          </DropdownMenuCheckboxItem>
        </DropdownMenuContent>
      </DropdownMenu>
    </div>

    <div class="rounded-md border">
      <Table>
        <TableHeader>
          <TableRow v-for="headerGroup in table.getHeaderGroups()" :key="headerGroup.id">
            <TableHead v-for="header in headerGroup.headers" :key="header.id">
              <FlexRender v-if="!header.isPlaceholder" :render="header.column.columnDef.header" :props="header.getContext()" />
            </TableHead>
          </TableRow>
        </TableHeader>
        <TableBody>
          <template v-if="table.getRowModel().rows.length">
            <TableRow
              v-for="row in table.getRowModel().rows"
              :key="row.id"
              class="cursor-pointer hover:bg-muted/30 transition"
              @click="$emit('row-click', row.original)"
              :data-state="row.getIsSelected() && 'selected'"
            >
              <TableCell v-for="cell in row.getVisibleCells()" :key="cell.id">
                <FlexRender :render="cell.column.columnDef.cell" :props="cell.getContext()" />
              </TableCell>
            </TableRow>
          </template>
          <TableRow v-else>
            <TableCell :colspan="columns.length" class="h-24 text-center">Aucun résultat</TableCell>
          </TableRow>
        </TableBody>
      </Table>
    </div>

    <div class="flex items-center justify-between py-4">
      <div class="text-sm text-muted-foreground">
        Affichage de {{ data.data.length }} sur {{ pagination.total }} éléments
      </div>
      <div class="flex gap-2">
        <Button
          variant="outline"
          size="sm"
          :disabled="pagination.current_page <= 1"
          @click="goToPage(pagination.current_page - 1)"
        >
          Précédent
        </Button>
        <Button
          variant="outline"
          size="sm"
          :disabled="pagination.current_page >= (pagination.last_page || 1)"
          @click="goToPage(pagination.current_page + 1)"
        >
          Suivant
        </Button>
      </div>
    </div>
  </div>
</template>