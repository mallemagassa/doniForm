<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import Input from '@/components/ui/input/Input.vue'
import Button from '@/components/ui/button/Button.vue'
import { toast } from 'vue-sonner'
import Select from '@/components/ui/select/Select.vue'
import SelectItem from '@/components/ui/select/SelectItem.vue'
import { SelectTrigger, SelectValue, SelectContent } from '@/components/ui/select'
import Textarea from '@/components/ui/textarea/Textarea.vue'
import { watch, nextTick } from 'vue'

const props = defineProps({
  formProgram: Object,
  form: Object,
  resource: Object,
  formFields: Array
})

console.log(props.formFields.map(field => ({
      id: field.id,
      label: field.label,
      field_type: field.field_type,
      required: Boolean(field.required),
      options:Array.isArray(field.options) })))

const form = useForm({
  ...Object.fromEntries(
    Object.keys(props.form).map(key => [
      key,
      props.form[key].type === 'checkbox' 
        ? Boolean(props.formProgram[key])
        : props.formProgram[key] || ''
    ])
  ),
  form_fields: props.formFields.length > 0
  ? props.formFields.map(field => ({
      id: field.id,
      label: field.label,
      field_type: field.field_type,
      required: Boolean(field.required),
      options:Array.isArray(field.options) 
  ? field.options 
  : typeof field.options === 'string'
    ? (
        field.options.trim().startsWith('[')
          ? (() => {
              try {
                return JSON.parse(field.options)
              } catch (e) {
                return field.options.split('\n').filter(opt => opt.trim())
              }
            })()
          : field.options.split('\n').filter(opt => opt.trim())
      )
    : []
    }))
  : [{
      label: '',
      field_type: 'text',
      required: false,
      options: []
    }]

})

const forceFormUpdate = () => {
  const newData = { ...form.data() }
  form.defaults(newData)
  form.reset()
}


nextTick(() => {
  forceFormUpdate()
})

watch(() => props.form, () => {
  nextTick(forceFormUpdate)
}, { deep: true })

const addFormField = () => {
  form.form_fields.push({
    label: '',
    field_type: 'text',
    required: false,
    options: []
  })
}

const removeFormField = (index) => {
  form.form_fields.splice(index, 1)
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

  await form.put(props.resource.routes.update.replace(':id', props.formProgram.id), {
    onSuccess: () => {
      toast.success(`${props.resource.label} mis à jour avec succès`)
    },
    onError: (errors) => {
      console.error('Erreurs:', errors)
      toast.error('Erreur lors de la mise à jour')
    }
  })
}


const updateFieldOptions = (index, value) => {
  form.form_fields[index].options = value
    ? value.split('\n').map(o => o.trim()).filter(o => o)
    : []
}

function getSelectValue(id) {
  // Gestion des booléens
  if (id === 'true') return true
  if (id === 'false') return false
  
  // Gestion des nombres
  if (!isNaN(id)) return Number(id)
  
  // Valeur par défaut
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
                {{ field.label || key }}
              </label>
              <Select v-model="form[key]" :required="field.options?.required">
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


            <div v-else-if="field.type === 'textarea'" class="space-y-2">
              <label :for="key" class="block font-medium capitalize">
                {{ key }}
              </label>
              <Textarea 
                :value="field.options ? field.options.join('\n') : ''"
                @input="field.options = $event.target.value ? $event.target.value.split('\n') : null"
                placeholder="Option1&#10;Option2&#10;Option3"
                class="min-h-[100px]"
              />
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
            <div v-for="(field, index) in form.form_fields" :key="field.id || index" class="p-4 border rounded-lg space-y-4">
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
                    <input v-model="field.required" type="checkbox" class="h-4 w-4" />

                    <span>Required</span>
                  </label>
                </div>
              </div>
              
              <div v-if="['select', 'checkbox', 'radio'].includes(field.field_type)" class="space-y-2">
                <label class="block font-medium">Options (une par ligne)</label>
                <Textarea 
                        :modelValue="Array.isArray(field.options) ? field.options.join('\n') : ''"
                        @update:modelValue="updateFieldOptions(index, $event)"
                        placeholder="Option1&#10;Option2&#10;Option3"
                        class="min-h-[100px] whitespace-pre"
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