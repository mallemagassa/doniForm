<script setup lang="ts">
import { Button } from '@/components/ui/button'
import { DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuLabel, DropdownMenuSeparator, DropdownMenuTrigger } from '@/components/ui/dropdown-menu'
import { MoreHorizontal } from 'lucide-vue-next'
import { Edit, Trash, Eye } from 'lucide-vue-next'
import { router } from '@inertiajs/vue3'
import { toast } from 'vue-sonner'


const props = defineProps<{
  payment: any,
  routes: {
    edit: string,
    show: string,
    destroy: string
    index: string
  }
}>()

console.log(props.routes)

function edit() {
  router.visit(props.routes.edit.replace(':id', props.payment.id))
}

function show() {
  router.visit(props.routes.show.replace(':id', props.payment.id))
}

function deleteEntity() {
  if (confirm('Confirmer la suppression ?')) {
    router.delete(props.routes.destroy.replace(':id', props.payment.id), {
      onSuccess: () => {
        toast.success('Utilisateur supprimé')
        router.visit(props.routes.index) // redirige vers la liste
      },
      onError: () => {
        toast.error('Erreur lors de la suppression')
      }
    })
  }
}



</script>

<template>
  <DropdownMenu>
    <DropdownMenuTrigger as-child>
      <Button variant="ghost" class="w-8 h-8 p-0">
        <span class="sr-only">Open menu</span>
        <MoreHorizontal class="w-4 h-4" />
      </Button>
    </DropdownMenuTrigger>
    <DropdownMenuContent align="end">
      <DropdownMenuLabel>Actions</DropdownMenuLabel>
      <DropdownMenuSeparator />
      <DropdownMenuItem @click="show">
        <Eye class="mr-2 h-4 w-4" />
        Voir
      </DropdownMenuItem>
      <DropdownMenuItem @click="edit">
        <Edit class="mr-2 h-4 w-4" />
        Modifier
      </DropdownMenuItem>
      <DropdownMenuItem @click="deleteEntity">
        <Trash class="mr-2 h-4 w-4" />
        Supprimer
      </DropdownMenuItem>

    </DropdownMenuContent>
  </DropdownMenu>
</template>