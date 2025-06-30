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
import Repeater from '@/components/ui/repeater/Repeater.vue'

const props = defineProps({
  evaluationCriteria: Object,
  form: Object,
  resource: Object,
})

// Normalisation des données
function normalizeData(evaluationCriteria, formFields) {
  const result = { ...evaluationCriteria }

  // Ajouter les items au format attendu par le repeater
  if (evaluationCriteria.evaluation_criteria_items) {
    result.items = evaluationCriteria.evaluation_criteria_items.map(item => ({
      id: item.id,
      title: item.title,
      description: item.description
    }))
  } else {
    result.items = []
  }

  // Formatage des dates si nécessaire
  for (const key in formFields) {
    const field = formFields[key]
    if (field.type === 'date' && result[key]) {
      const isoDate = new Date(result[key])
      result[key] = isoDate.toISOString().slice(0, 10)
    }
  }

  return result
}

const form = useForm(normalizeData(props.evaluationCriteria, props.form))

function submitForm() {
  form.put(props.resource.routes.update.replace(':id', props.evaluationCriteria.id), {
    onSuccess: () => toast.success(`${props.resource.label} mis à jour avec succès`),
    onError: () => toast.error('Erreur lors de la mise à jour')
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
                :id="key"
                v-model="form[key]"
                :required="field.options?.required"
              />
              <span v-if="form.errors[key]" class="text-sm text-red-600">
                {{ form.errors[key] }}
              </span>
            </div>

            <div v-else-if="field.type === 'repeater'" class="space-y-2 col-span-full">
              <label class="block font-medium capitalize">
                {{ key }}
              </label>
              <Repeater 
                v-model="form[key]"
                :fields="field.options.fields"
              />
              <span v-if="form.errors[key]" class="text-sm text-red-600">
                {{ form.errors[key] }}
              </span>
              <span v-if="form.errors[`${key}.*`]" class="text-sm text-red-600">
                {{ form.errors[`${key}.*`] }}
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
        </div>

        <div class="flex gap-2">
          <Button type="submit" :disabled="form.processing">Enregistrer</Button>
          <Link :href="resource.routes.index">
            <Button variant="default">Annuler</Button>
          </Link>
        </div>
      </form>
    </div>
  </AppLayout>
</template>