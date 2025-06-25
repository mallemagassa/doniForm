<script setup>
import { ref, Text } from 'vue'
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
  resource: Object
})

// Initialiser les valeurs du formulaire
const form = useForm(
  Object.fromEntries(
    Object.keys(props.form).map(key => [
      key,
      props.form[key].type === 'checkbox' ? false : ''
    ])
  )
)

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
