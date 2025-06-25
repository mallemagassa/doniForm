<script setup>
import { Head, Link } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import Input from '@/components/ui/input/Input.vue'
import { computed } from 'vue'
import Button from '@/components/ui/button/Button.vue'

const props = defineProps({
  formprogram: Object,
  formFields: Array,
  resource: Object,
})

// Parser les options correctement
const parsedFormFields = computed(() => {
  return props.formFields.map(field => ({
    ...field,
    options: Array.isArray(field.options)
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
})
</script>

<template>
  <Head :title="`Détails ${resource.label}`" />

  <AppLayout>
    <div class="flex flex-col gap-6 p-4">
      <div class="flex justify-between items-center">
        <h1 class="text-2xl font-bold">Détails {{ resource.label }}</h1>
        <Link :href="resource.routes.index">
          <Button class="btn btn-secondary">Retour à la liste</button>
        </Link>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div class="space-y-2">
          <label class="block font-medium">Nom</label>
          <Input :modelValue="formprogram.name" readonly />
        </div>
        <div class="space-y-2">
          <label class="block font-medium">Programme</label>
          <Input :modelValue="formprogram.program?.title || '—'" readonly />
        </div>
      </div>

      <div class="space-y-4 mt-6">
        <h2 class="text-lg font-semibold">Champs du formulaire</h2>
        <div
          v-for="(field, index) in parsedFormFields"
          :key="field.id || index"
          class="p-4 border rounded-lg space-y-2"
        >
          <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
              <label class="block font-medium">Label</label>
              <Input :modelValue="field.label" readonly />
            </div>

            <div>
              <label class="block font-medium">Type de champ</label>
              <Input :modelValue="field.field_type" readonly />
            </div>

            <div class="flex items-center gap-2 mt-6">
              <input type="checkbox" :checked="field.required" disabled />
              <span>Obligatoire</span>
            </div>
          </div>

          <div v-if="['select', 'checkbox', 'radio'].includes(field.field_type)">
            <label class="block font-medium">Options</label>
            <ul class="list-disc list-inside text-gray-700">
              <li v-for="(option, i) in field.options" :key="i">{{ option }}</li>
            </ul>
          </div>
        </div>
        <div class="flex justify-end gap-2 pt-4">
          <Link :href="resource.routes.edit.replace(':id', formprogram.id)">
            <Button>
              Modifier
            </Button>
          </Link>
          <Link :href="resource.routes.index">
            <Button variant="default">
              Retour
            </Button>
          </Link>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
