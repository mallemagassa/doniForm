<script setup>
import { ref } from 'vue'
import { Head, Link, useForm } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import Input from '@/components/ui/input/Input.vue'
import Button from '@/components/ui/button/Button.vue'
import { toast } from 'vue-sonner'
import Select from '@/components/ui/select/Select.vue'
import SelectItem from '@/components/ui/select/SelectItem.vue'
import { SelectTrigger, SelectValue, SelectContent } from '@/components/ui/select'
import Textarea from '@/components/ui/textarea/Textarea.vue'

const props = defineProps({
  form: Object,
  resource: Object,
  formPrograms: Array,
})

// Initialiser les valeurs du formulaire
const form = useForm({
  ...Object.fromEntries(
    Object.keys(props.form).map(key => [
      key,
      props.form[key].type === 'checkbox' ? false : ''
    ])
  ),
  form_fields: [{
    label: '',
    field_type: 'text',
    required: false, // Initialisé comme booléen
    options: null
  }]
})

const addFormField = () => {
  form.form_fields.push({
    label: '',
    field_type: 'text',
    required: false, // Toujours initialiser comme booléen
    options: null
  })
}

const removeFormField = (index) => {
  form.form_fields.splice(index, 1)
}

// Gestion spécifique pour les checkboxes
const toggleRequired = (field) => {
  field.required = !field.required
}

const submitForm = async () => {
  form.transform(data => ({
    ...data,
    form_fields: data.form_fields.map(field => ({
      ...field,
      options: Array.isArray(field.options)
        ? field.options.join('\n')
        : field.options || ''
    }))
  }))

  try {
    await form.post(props.resource.routes.store, {
      onSuccess: () => {
        toast.success(`${props.resource.label} créé avec succès`)
      },
      onError: (errors) => {
        console.error('Erreurs:', errors)
        toast.error('Erreur lors de la création')
      }
    })
  } catch (error) {
    console.error('Erreur capturée:', error)
  }
}

</script>

<template>
  <Head :title="`Create ${resource.label}`" />

  <AppLayout>
    <div class="flex flex-col gap-6 p-4">
      <div class="flex justify-between items-center">
        <h1 class="text-2xl font-bold">Create {{ resource.label }}</h1>
        <Link :href="resource.routes.index">
          <Button variant="outline">
            Retour à la liste
          </Button>
        </Link>
      </div>

      <form @submit.prevent="submitForm" class="space-y-4">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <template v-for="(field, key) in props.form" :key="key">
            <div v-if="field.type === 'select'" class="space-y-2">
              <label :for="key" class="block font-medium capitalize">
                {{ key }}
              </label>
              <Select v-model="form[key]" :required="field.options?.required">
                <SelectTrigger class="w-full">
                  <SelectValue placeholder="Sélectionnez une option" />
                </SelectTrigger>
                <SelectContent>
                  <SelectItem
                    v-for="(name, id) in field.options?.options || {}"
                    :key="id"
                    :value="id"
                  >
                    {{ name }}
                  </SelectItem>
                </SelectContent>
              </Select>

              <span v-if="form.errors[key]" class="text-sm text-red-600">
                {{ form.errors[key] }}
              </span>
            </div>

            <div v-else-if="field.type === 'textarea'" class="space-y-2">
              <label :for="key" class="block font-medium capitalize">
                {{ key }}
              </label>
              <Textarea
                :id="key"
                v-model="form[key]"
                :required="field.options?.required"
                class=""
              ></Textarea>
              <span v-if="form.errors[key]" class="text-sm text-red-600">
                {{ form.errors[key] }}
              </span>
            </div>

            <div v-else class="space-y-2">
              <label :for="key" class="block font-medium capitalize">
                {{ key }}
              </label>
              <Input
                :id="key"
                v-model="form[key]"
                :type="field.type"
                :required="field.options?.required"
              />
              <span v-if="form.errors[key]" class="text-sm text-red-600">
                {{ form.errors[key] }}
              </span>
            </div>
          </template>
          
          <!-- Form Repeater pour les FormFields -->
          <div class="col-span-full space-y-4">
            <h2 class="text-lg font-semibold">Champs du formulaire</h2>
            <div v-for="(field, index) in form.form_fields" :key="index" class="p-4 border rounded-lg space-y-4">
              <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="space-y-2">
                  <label class="block font-medium">Label</label>
                  <Input v-model="field.label" type="text" required />
                </div>
                
                <div class="space-y-2">
                  <label class="block font-medium">Type de champ</label>
                  <Select v-model="field.field_type">
                    <SelectTrigger class="w-full">
                      <SelectValue placeholder="Sélectionnez un type" />
                    </SelectTrigger>
                    <SelectContent>
                      <SelectItem value="text">Texte</SelectItem>
                      <SelectItem value="textarea">Zone de texte</SelectItem>
                      <SelectItem value="select">Liste déroulante</SelectItem>
                      <SelectItem value="checkbox">Case à cocher</SelectItem>
                      <SelectItem value="radio">Bouton radio</SelectItem>
                    </SelectContent>
                  </Select>
                </div>
                
                <div class="space-y-2 flex items-center">
                  <label class="flex items-center gap-2">
                    <Input 
                      type="checkbox" 
                      :checked="field.required" 
                      @change="toggleRequired(field)"
                    />
                    <span>Required</span>
                  </label>
                </div>
              </div>
              
              <div v-if="['select', 'checkbox', 'radio'].includes(field.field_type)" class="space-y-2">
                <label class="block font-medium">Options (une par ligne)</label>
                <Textarea 
                  v-model="field.options" 
                  placeholder="Option1&#10;Option2&#10;Option3"
                  class="min-h-[100px]"
                />
                <p class="text-sm text-gray-500">Séparez les options par des retours à la ligne</p>
              </div>
              
              <div class="flex justify-end">
                <Button 
                  type="button" 
                  variant="destructive" 
                  size="sm"
                  @click="removeFormField(index)"
                >
                  Supprimer
                </Button>
              </div>
            </div>
            
            <Button 
              type="button" 
              variant="outline" 
              class="w-full mt-2"
              @click="addFormField"
            >
              Ajouter un champ
            </Button>
          </div>
        </div>

        <div class="flex gap-2">
          <Button type="submit" :disabled="form.processing">
            Enregistrer
          </Button>
          <Link :href="resource.routes.index">
            <Button variant="outline">
              Annuler
            </Button>
          </Link>
        </div>
      </form>
    </div>
  </AppLayout>
</template>