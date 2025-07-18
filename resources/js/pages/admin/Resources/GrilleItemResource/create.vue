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
import { Plus, Minus } from 'lucide-vue-next'

const props = defineProps({
  form: Object,
  resource: Object
})

// Initialiser les valeurs du formulaire
const form = useForm({
  ...Object.fromEntries(
    Object.keys(props.form).filter(key => props.form[key].type !== 'repeater').map(key => [
      key,
      props.form[key].type === 'checkbox' ? false : ''
    ])
  ),
  notes: props.form.notes?.value || [{}]
})

function addNote() {
  form.notes.push({ note: '' })
}

function removeNote(index) {
  form.notes.splice(index, 1)
}

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
          <template v-for="(field, key) in props.form" :key="key">
            <!-- Gestion du repeater -->
            <div v-if="field.type === 'repeater'" class="space-y-4 col-span-full">
              <label class="block font-medium capitalize">
                {{ key }}
              </label>
              
              <div v-for="(item, index) in form.notes" :key="index" class="flex items-end gap-2">
                <div class="flex-1 space-y-2">
                  <Input
                    v-model="item.note"
                    type="number"
                    :required="true"
                    :max="5"
                    placeholder="Note (0-5)"
                  />
                  <span v-if="form.errors[`notes.${index}.note`]" class="text-sm text-red-600">
                    {{ form.errors[`notes.${index}.note`] }}
                  </span>
                </div>
                
                <Button 
                  type="button" 
                  variant="destructive" 
                  size="sm" 
                  @click="removeNote(index)"
                  :disabled="form.notes.length <= 1"
                >
                  <Minus class="w-4 h-4" />
                </Button>
              </div>
              
              <Button 
                type="button" 
                variant="outline" 
                size="sm" 
                @click="addNote"
              >
                <Plus class="w-4 h-4 mr-2" />
                Ajouter une note
              </Button>
            </div>

            <!-- Gestion des autres champs -->
            <template v-else>
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
          </template>
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