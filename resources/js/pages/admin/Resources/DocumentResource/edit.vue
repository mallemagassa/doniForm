<!--  edit.vue -->
<script setup>
import { ref, onMounted } from 'vue'
import { Head, useForm, Link } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import Input from '@/components/ui/input/Input.vue'
import Button from '@/components/ui/button/Button.vue'
import Select from '@/components/ui/select/Select.vue'
import { SelectItem, SelectTrigger, SelectValue, SelectContent } from '@/components/ui/select'
import { toast } from 'vue-sonner'
import Textarea from '@/components/ui/textarea/Textarea.vue'
import FileUploader from '@/components/FileUploader.vue'
const props = defineProps({
  document: Object,
  form: Object,
  resource: Object,
})

// Initialisation dynamique du formulaire basée sur les champs du formBuilder
const initialFormData = () => {
  const data = { _method: 'PUT' }
  Object.keys(props.form).forEach(key => {
    if (key === 'file_path' || key === 'file') {
      data.file = null // Initialisation spécifique pour le champ fichier
    } else {
      data[key] = props.document[key] || ''
    }
  })
  return data
}

const form = useForm(initialFormData())

function submitForm() {
  const formData = new FormData()
  
  // Ajout de tous les champs sauf le fichier
  Object.keys(props.form).forEach(key => {
    if (key !== 'file_path' && key !== 'file' && form[key] !== undefined) {
      formData.append(key, form[key])
    }
  })

  // Ajout spécifique du fichier s'il a été modifié
  if (form.file instanceof File) {
    formData.append('file', form.file)
  }

  form.post(props.resource.routes.update.replace(':id', props.document.id), {
    preserveScroll: true,
    forceFormData: true, // Important pour les fichiers
    onSuccess: () => toast.success(`${props.resource.label} mis à jour`),
    onError: (errors) => {
      toast.error('Erreur lors de la mise à jour')
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
  <Head :title="`Modifier ${resource.label}`" />

  <AppLayout>
    <div class="flex flex-col gap-6 p-4">
      <div class="flex justify-between items-center">
        <h1 class="text-2xl font-bold">Modifier {{ resource.label }}</h1>
        <Link :href="resource.routes.index">
          <Button variant="default">Retour à la liste</Button>
        </Link>
      </div>

      <form @submit.prevent="submitForm" class="space-y-4">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <template v-for="(field, key) in props.form" :key="key">
            <!-- Champ select -->
            <div v-if="field.type === 'select'" class="space-y-2">
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

            <!-- Champ fichier -->
            <div v-else-if="field.type === 'file'" class="space-y-2">
              <label class="block font-medium capitalize">
                {{ field.label || key }}
              </label>
              <FileUploader 
                v-model="form.file" 
                :name="key"
                :current-file="field.options?.value || props.document.file_path" 
              />

              <span v-if="form.errors.file" class="text-sm text-red-600">
                {{ form.errors.file }}
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
            <Button variant="default">Annuler</Button>
          </Link>
        </div>
      </form>
    </div>
  </AppLayout>
</template>