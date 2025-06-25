<!--  create.vue -->
<script setup>
import { ref } from 'vue'
import { Head, Link, useForm } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import Input from '@/components/ui/input/Input.vue'
import Button from '@/components/ui/button/Button.vue'
import Select from '@/components/ui/select/Select.vue'
import { SelectItem, SelectTrigger, SelectValue, SelectContent } from '@/components/ui/select'
import Textarea from '@/components/ui/textarea/Textarea.vue'
import FileUploader from '@/components/FileUploader.vue'
import { toast } from 'vue-sonner'

const props = defineProps({
  form: Object,
  resource: Object,
})

// Initialisation dynamique avec gestion des fichiers
const form = useForm(
  Object.fromEntries(
    Object.keys(props.form).map(key => [
      key,
      props.form[key].type === 'checkbox' ? false : 
      props.form[key].type === 'file' ? null : ''
    ])
  )
)

function submitForm() {
  form.post(props.resource.routes.store, {
    preserveScroll: true,
    forceFormData: true,
    onSuccess: () => {
      toast.success(`${props.resource.label} créé avec succès`)
    },
    onError: (errors) => {
      toast.error('Erreur lors de la création')
      console.error(errors)
    }
  })
}

function getSelectValue(id) {
  if (id === 'true') return true
  if (id === 'false') return false
  if (!isNaN(id)) return Number(id)
  return id
}
</script>

<template>
  <Head :title="`Créer ${resource.label}`" />

  <AppLayout>
    <div class="flex flex-col gap-6 p-4">
      <div class="flex justify-between items-center">
        <h1 class="text-2xl font-bold">Créer {{ resource.label }}</h1>
        <Link :href="resource.routes.index">
          <Button variant="outline">Retour à la liste</Button>
        </Link>
      </div>

      <form @submit.prevent="submitForm" class="space-y-4">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <template v-for="(field, key) in props.form" :key="key">
            <!-- Champ fichier -->
            <div v-if="field.type === 'file'" class="space-y-2">
              <label class="block font-medium capitalize">
                {{ field.label || key }}
              </label>
              <FileUploader 
                v-model="form[key]" 
                :name="key"
                :current-file="null" 
              />

              <span v-if="form.errors[key]" class="text-sm text-red-600">
                {{ form.errors[key] }}
              </span>
            </div>

            <!-- Champ select -->
            <div v-else-if="field.type === 'select'" class="space-y-2">
              <label class="block font-medium capitalize">
                {{ field.label || key }}
              </label>
              <Select 
                v-model="form[key]" 
                :required="field.options?.required"
              >
                <SelectTrigger class="w-full">
                  <SelectValue :placeholder="field.placeholder || 'Sélectionnez une option'" />
                </SelectTrigger>
                <SelectContent>
                  <SelectItem
                    v-for="(name, id) in field.options?.options || {}"
                    :key="id"
                    :value="getSelectValue(id)"
                  >
                    {{ name }}
                  </SelectItem>
                </SelectContent>
              </Select>
              <span v-if="form.errors[key]" class="text-sm text-red-600">
                {{ form.errors[key] }}
              </span>
            </div>

            <!-- Champ textarea -->
            <div v-else-if="field.type === 'textarea'" class="space-y-2">
              <label class="block font-medium capitalize">
                {{ field.label || key }}
              </label>
              <Textarea
                v-model="form[key]"
                :required="field.options?.required"
              />
              <span v-if="form.errors[key]" class="text-sm text-red-600">
                {{ form.errors[key] }}
              </span>
            </div>

            <!-- Champ input standard -->
            <div v-else class="space-y-2">
              <label class="block font-medium capitalize">
                {{ field.label || key }}
              </label>
              <Input
                v-model="form[key]"
                :type="field.type"
                :required="field.options?.required"
              />
              <span v-if="form.errors[key]" class="text-sm text-red-600">
                {{ form.errors[key] }}
              </span>
            </div>
          </template>
        </div>

        <div class="flex gap-2">
          <Button type="submit" :disabled="form.processing">
            Enregistrer
          </Button>
          <Link :href="resource.routes.index">
            <Button variant="outline">Annuler</Button>
          </Link>
        </div>
      </form>
    </div>
  </AppLayout>
</template>