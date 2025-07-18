<script setup lang="ts">
import type {
  ColumnDef,
  ColumnFiltersState,
  ExpandedState,
  SortingState,
  VisibilityState,
  PaginationState,
} from '@tanstack/vue-table'
import { route } from 'ziggy-js'
import { router } from '@inertiajs/vue3'
import {
  FlexRender,
  getCoreRowModel,
  getExpandedRowModel,
  getFilteredRowModel,
  getPaginationRowModel,
  getSortedRowModel,
  useVueTable,
} from '@tanstack/vue-table'
import { ArrowUpDown, ChevronDown } from 'lucide-vue-next'
import { h, ref, watch } from 'vue'
import { valueUpdater } from '@/lib/utils'
import { Link } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button'
import { Checkbox } from '@/components/ui/checkbox'
import {
  DropdownMenu,
  DropdownMenuCheckboxItem,
  DropdownMenuContent,
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
import DropdownAction from './DataTableDemoColumn.vue'
import type { PropType } from 'vue'
import { debounce } from 'lodash-es'

interface TableMeta {
  current_page: number
  last_page: number
  per_page: number
  total: number
  from: number
  to: number
}

const emits = defineEmits(['row-click'])

const props = defineProps({
  data: {
    type: Array as PropType<any[]>,
    required: true,
  },
  columns: {
    type: Array as PropType<ColumnDef<any>[]>,
    required: true,
  },
  meta: {
    type: Object as PropType<TableMeta>,
    required: true,
  },
  routes: {
    type: Object as PropType<{ create: string }>,
    required: false,
    default: () => ({})
  },
  
  filterableColumns: {
    type: Array as PropType<string[]>,
    default: () => []
  },
  initialSearch: {
    type: String,
    default: ''
  },
  initialFilters: {
    type: Object,
    default: () => ({})
  }
})

// Table state
const sorting = ref<SortingState>([])
// const columnFilters = ref<ColumnFiltersState>([])
const columnVisibility = ref<VisibilityState>({})
const rowSelection = ref({})
const expanded = ref<ExpandedState>({})
const pageSizes = [5, 10, 20, 30, 40, 50]
// const globalFilter = ref('')
const tableData = ref(props.data)


// Initialize pagination from props
const pagination = ref<PaginationState>({
  pageIndex: props.meta.current_page - 1,
  pageSize: props.meta.per_page,
})



// Modifiez la fonction debouncedFilter pour inclure un indicateur de fin de frappe
const typingTimeout = ref<number | null>(null)
const isTyping = ref(false)

// Initialisez les états avec les valeurs des props
const globalFilter = ref(props.initialSearch)
const columnFilters = ref<ColumnFiltersState>(
  Object.entries(props.initialFilters).map(([id, value]) => ({ id, value }))
)

// Modifiez la fonction debouncedFilter pour préserver les valeurs
const debouncedFilter = debounce((filterValue, filterType, columnId = null) => {
  isTyping.value = false
  
  if (filterType === 'global') {
    router.get(
      route(route().current()),
      { search: filterValue, page: 1, per_page: pagination.value.pageSize },
      { 
        preserveState: true,
        preserveScroll: true,
        replace: true,
        onSuccess: () => {
          // Maintient la valeur après la requête
          globalFilter.value = filterValue
        }
      }
    )
  } else {
    const filters = { [columnId]: filterValue }
    router.get(
      route(route().current()),
      { 
        ...Object.fromEntries(Object.entries(filters).map(([k, v]) => [`filters[${k}]`, v])),
        page: 1,
        per_page: pagination.value.pageSize
      },
      { 
        preserveState: true,
        preserveScroll: true,
        replace: true,
        onSuccess: () => {
          // Maintient la valeur après la requête
          if (columnId) {
            const existingFilter = columnFilters.value.find(f => f.id === columnId)
            if (existingFilter) {
              existingFilter.value = filterValue
            }
          }
        }
      }
    )
  }
}, 800)

// Modifiez les watchers pour gérer l'état de frappe
watch(globalFilter, (newFilter) => {
  isTyping.value = true
  debouncedFilter(newFilter, 'global')
})

watch(columnFilters, (newFilters) => {
  isTyping.value = true
  newFilters.forEach(filter => {
    debouncedFilter(filter.value, 'column', filter.id)
  })
}, { deep: true })
// Modifiez le watcher pour props.data
watch(() => props.data, (newData) => {
  tableData.value = newData
}, { deep: true })


// Modifiez le watcher de pagination
watch(pagination, (newPagination) => {
  router.get(
    route(route().current()), // reste sur la même route
    {
      page: newPagination.pageIndex + 1,
      per_page: newPagination.pageSize,
      sort_field: sorting.value[0]?.id,
      sort_direction: sorting.value[0]?.desc ? 'desc' : 'asc',
    },
    {
      preserveState: true,
      preserveScroll: true,
      replace: true,
    }
  )
}, { deep: true })



// Initialize table
const table = useVueTable({
  data: tableData.value,
  columns: props.columns,
  getCoreRowModel: getCoreRowModel(),
  getSortedRowModel: getSortedRowModel(),
  getExpandedRowModel: getExpandedRowModel(),
  manualPagination: true,
  manualFiltering: true,
  pageCount: props.meta.last_page,
  onGlobalFilterChange: updaterOrValue => valueUpdater(updaterOrValue, globalFilter),
  onSortingChange: updaterOrValue => valueUpdater(updaterOrValue, sorting),
  onColumnFiltersChange: updaterOrValue => valueUpdater(updaterOrValue, columnFilters),
  onColumnVisibilityChange: updaterOrValue => valueUpdater(updaterOrValue, columnVisibility),
  onRowSelectionChange: updaterOrValue => valueUpdater(updaterOrValue, rowSelection),
  onExpandedChange: updaterOrValue => valueUpdater(updaterOrValue, expanded),
  onPaginationChange: updaterOrValue => valueUpdater(updaterOrValue, pagination),
  state: {
    get sorting() { return sorting.value },
    get columnFilters() { return columnFilters.value },
    get columnVisibility() { return columnVisibility.value },
    get rowSelection() { return rowSelection.value },
    get expanded() { return expanded.value },
    get pagination() { return pagination.value },
    get globalFilter() { return globalFilter.value },
  },
})

</script>

<template>
  <div class="w-full">
    <div class="flex items-center py-4">
       <!-- Filtre global avec indicateur de chargement -->
       <div class="relative max-w-sm">
        <Input
          placeholder="Recherche globale..."
          v-model="globalFilter"
        />
        <div v-if="isTyping" class="absolute inset-y-0 right-0 flex items-center pr-3">
          <div class="w-4 h-4 border-2 border-blue-500 border-t-transparent rounded-full animate-spin"></div>
        </div>
      </div>

      <!-- Filtres par colonne -->
      <template v-for="column in props.filterableColumns" :key="column">
        <div class="relative max-w-sm">
          <Input
            :placeholder="`Filtrer ${table.getColumn(column)?.columnDef.header}`"
            :model-value="table.getColumn(column)?.getFilterValue()"
            @update:model-value="value => table.getColumn(column)?.setFilterValue(value)"
          />
          <div v-if="isTyping" class="absolute inset-y-0 right-0 flex items-center pr-3">
            <div class="w-4 h-4 border-2 border-blue-500 border-t-transparent rounded-full animate-spin"></div>
          </div>
        </div>
      </template>


      <DropdownMenu>
        <DropdownMenuTrigger as-child>
          <Button variant="default" class="ml-auto">
            Les colonnes <ChevronDown class="ml-2 h-4 w-4" />
          </Button>
        </DropdownMenuTrigger>
        <DropdownMenuContent align="end">
          <DropdownMenuCheckboxItem
            v-for="column in table.getAllColumns().filter((column) => column.getCanHide())"
            :key="column.id"
            class="capitalize"
            :model-value="column.getIsVisible()"
            @update:model-value="(value) => column.toggleVisibility(!!value)"
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
          <template v-if="table.getRowModel().rows?.length">
            <TableRow
              v-for="row in table.getRowModel().rows"
              :key="row.id"
              :data-state="row.getIsSelected() && 'selected'"
              class="cursor-pointer hover:bg-muted/30 transition"
              @click="$emit('row-click', row.original)"
            >
              <TableCell v-for="cell in row.getVisibleCells()" :key="cell.id">
                <FlexRender :render="cell.column.columnDef.cell" :props="cell.getContext()" />
              </TableCell>
            </TableRow>
          </template>
          <TableRow v-else>
            <TableCell :colspan="columns.length" class="h-24 text-center">
              No results.
            </TableCell>
          </TableRow>
        </TableBody>
      </Table>
    </div>

    <div class="flex items-center justify-between py-4">
      <div class="flex-1 text-sm text-muted-foreground">
        Showing {{ meta.from }} to {{ meta.to }} of {{ meta.total }} results
      </div>
      
      <div class="flex items-center space-x-6">
        <div class="flex items-center space-x-2">
          <p class="text-sm font-medium">Rows per page</p>
          <select
            class="h-8 rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-sm transition-colors focus:outline-none focus:ring-1 focus:ring-ring"
            :value="table.getState().pagination.pageSize"
            @change="table.setPageSize(Number($event.target.value))"
          >
            <option v-for="pageSize in pageSizes" :key="pageSize" :value="pageSize">
              {{ pageSize }}
            </option>
          </select>
        </div>
        
        <div class="flex space-x-2">
          <Button
            variant="outline"
            size="sm"
            :disabled="!table.getCanPreviousPage()"
            @click="table.previousPage()"
          >
            Previous
          </Button>
          <Button
            variant="outline"
            size="sm"
            :disabled="!table.getCanNextPage()"
            @click="table.nextPage()"
          >
            Next
          </Button>
        </div>
      </div>
    </div>
  </div>
</template>