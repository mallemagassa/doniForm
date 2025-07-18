<!--- index.vue.stub -->
<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import KizzaTable from '@/components/ui/data-table/KizzaTable.vue';
import type { ColumnDef } from '@tanstack/vue-table';
import { computed, h } from 'vue'
import { Checkbox } from '@/components/ui/checkbox'
import DropdownAction from '@/components/ui/data-table/DataTableDemoColumn.vue'
import Button from '@/components/ui/button/Button.vue';
import { ArrowUpDown, ChevronDown } from 'lucide-vue-next'

interface User {
name: string;
  email: string;
  email_verified_at: string | Date;
  password: string;
  remember_token: string;
}

// const props = defineProps({
//   table: {
//     type: Object as () => {
//       records: {
//         data: User[]
//       }
//       columns: ColumnDef<User>[]
//     },
//     required: true
//   },
//   resource: {
//     type: Object as () => {
//       label: string
//       routes: {
//         edit: string
//         destroy: string
//         index: string
//         create: string
//       }
//       relations?: Record<string, any>
//     },
//     required: true
//   }
// });

const props = defineProps({
  table: {
    type: Object as () => {
      records: any[];
      columns: Record<string, string>;
      current_page?: number;
      per_page?: number;
      total?: number;
      last_page?: number;
    },
    required: true
  },
  columns: {
    type: Object,
    default: () => ({})
  },
  resource: {
    type: Object as () => {
      label: string
      routes: {
        destroy: string
        index: string
        create: string
        show: string
      }
      relations?: Record<string, any>
    },
    required: true
  },
  filters: {
    type: Object,
    default: () => ({})
  },
  search: {
    type: String,
    default: ''
  },
  pagination: {
    type: Object,
    default: () => ({ current_page: 1, per_page: 10, total: 0 })
  }

});



const formattedColumns = computed(() => {
  const defaultSelectColumn = {
    id: 'select',
    header: ({ table }) =>
      h(Checkbox, {
        class: 'custom-checkbox',
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

  const dynamicColumns = Object.entries(props.table.columns).map(([key, label]) => {
      // Remplacer les underscores par des points pour l'accès aux données
      const originalKey = key.replace(/_/g, '.');
      const isRelation = originalKey.includes('.');
      
      return {
          accessorKey: key, // Utiliser la clé transformée (avec underscore)
          header: ({ column }) =>
              h(Button, {
                  variant: 'ghost',
                  class: 'bg-[#2755a1] text-white',
                  onClick: () => column.toggleSorting(column.getIsSorted() === 'asc'),
              }, () => [label, h(ArrowUpDown, { class: 'ml-2 h-4 w-4' })]),
          cell: ({ row }) => {
              if (isRelation) {
                  return row.original[key] || 'N/A';
              }
              return row.getValue(key);
          },
      };
  });

   const defaultActionsColumn = {
    id: 'actions',
    enableHiding: false,
    cell: ({ row }) => {
    const payment = row.original
    return h(DropdownAction, {
        payment,
        routes: {
        edit: props.resource.routes.edit,
        destroy: props.resource.routes.destroy,
        index: props.resource.routes.index
        },
        onExpand: row.toggleExpanded
    })
    }

  }

  return [defaultSelectColumn, ...dynamicColumns, defaultActionsColumn];
});
</script>

<template>
  <Head :title="resource.label" />
  <AppLayout>
    <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
      <div class="flex justify-between items-center">
        <h1 class="text-2xl font-bold">{{ resource.label }}</h1>
        <Link :href="resource.routes.create">
          <Button variant="default">
            Créer {{ resource.label }}
          </Button>
        </Link>
      </div>
      <KizzaTable 
        :data="table.records" 
        :columns="formattedColumns"
        :routes="{
          index: resource.routes.index,
          create: resource.routes.create,
          show: resource.routes.show,
          destroy: resource.routes.destroy
        }"
        :filters="filters"
        :search="search"
        :pagination="{
          current_page: table.current_page ?? 1,
          per_page: table.per_page ?? 10,
          total: table.total ?? 0,
          last_page: table.last_page ?? 1
        }"
      />
    </div>
  </AppLayout>
</template>