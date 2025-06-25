<script setup>
import { ref } from 'vue'
import { Head, Link, useForm } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import Input from '@/components/ui/input/Input.vue'
import Button from '@/components/ui/button/Button.vue'
import { toast } from 'vue-sonner'
import PermissionSelect from '@/components/PermissionSelect.vue'

const props = defineProps({
  form: Object,
  resource: Object
})

console.log(props.form.permissions.options.options)
const form = useForm({
  name: '',
  guard_name: 'web',
  permissions: []
})

function submitForm() {
  form.post(props.resource.routes.store, {
    onSuccess: () => {
      toast.success(`${props.resource.label} créé avec succès`)
    },
    onError: () => {
      toast.error('Erreur lors de la création')
    }
  })
}

</script>

<template>
  <Head :title="`Create ${resource.label}`" />

  <AppLayout>
    <div class="flex flex-col gap-6 p-4">
      <div class="flex justify-between items-center">
        <h1 class="text-2xl font-bold">Create {{ resource.label }}</h1>
        <Link :href="resource.routes.index">
          <Button variant="default">
            Retour à la liste
          </Button>
        </Link>
      </div>

      <form @submit.prevent="submitForm" class="space-y-4">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div class="space-y-2">
            <label class="block font-medium">Nom du rôle</label>
            <Input v-model="form.name" required />
            <span v-if="form.errors.name" class="text-sm text-red-600">
              {{ form.errors.name }}
            </span>
          </div>

          <div class="space-y-2">
            <label class="block font-medium">Guard</label>
            <Input v-model="form.guard_name" required />
            <span v-if="form.errors.guard_name" class="text-sm text-red-600">
              {{ form.errors.guard_name }}
            </span>
          </div>

          <div class="md:col-span-2 space-y-2">
            <label class="block font-medium">Permissions</label>
            <PermissionSelect 
              v-model="form.permissions" 
              :options="props.form.permissions.options.options" 
              multiple
            />
            <span v-if="form.errors.permissions" class="text-sm text-red-600">
              {{ form.errors.permissions }}
            </span>
          </div>
        </div>

        <div class="flex gap-2">
          <Button type="submit" :disabled="form.processing">
            Enregistrer
          </Button>
          <Link :href="resource.routes.index">
            <Button variant="default">
              Annuler
            </Button>
          </Link>
        </div>
      </form>
    </div>
  </AppLayout>
</template>