<script setup>
import { ref } from 'vue'
import Input from '@/components/ui/input/Input.vue'
import Button from '@/components/ui/button/Button.vue'
import Textarea from '@/components/ui/textarea/Textarea.vue'

const props = defineProps({
  modelValue: {
    type: Array,
    default: () => []
  },
  fields: {
    type: Object,
    required: true
  }
})

const emit = defineEmits(['update:modelValue'])

const items = ref(props.modelValue.length ? props.modelValue : [{}])

function addItem() {
  items.value.push({})
  emitUpdate()
}

function removeItem(index) {
  items.value.splice(index, 1)
  emitUpdate()
}

function emitUpdate() {
  emit('update:modelValue', items.value)
}
</script>

<template>
  <div class="space-y-4">
    <div v-for="(item, index) in items" :key="index" class="border rounded-lg p-4 space-y-4">
      <div class="flex justify-between items-center">
        <h3 class="font-medium">Critere {{ index + 1 }}</h3>
        <button
          type="button"
          @click="removeItem(index)"
          class="text-red-600 hover:text-red-800 text-sm"
        >
          Supprimer
        </button>
      </div>

      <div class="grid grid-cols-2 gap-4">
        <div v-for="(fieldConfig, fieldName) in fields" :key="fieldName" class="space-y-2">
          <label :for="`${fieldName}-${index}`" class="block font-medium capitalize">
            {{ fieldName }}
          </label>
          
          <template v-if="fieldConfig.type === 'textarea'">
            <Textarea
              :id="`${fieldName}-${index}`"
              v-model="item[fieldName]"
              :required="fieldConfig.required"
              @change="emitUpdate"
            />
          </template>
          
          <template v-else>
            <Input
              :id="`${fieldName}-${index}`"
              v-model="item[fieldName]"
              :type="fieldConfig.type"
              :required="fieldConfig.required"
              @change="emitUpdate"
              class=""
            />
          </template>
        </div>
      </div>
    </div>

    <Button type="button" variant="outline" @click="addItem">
      Ajouter un item
    </Button>
  </div>
</template>