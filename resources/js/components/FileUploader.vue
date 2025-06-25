<script setup>
import { ref, watch } from 'vue'
import vueFilePond from 'vue-filepond'
import 'filepond/dist/filepond.min.css'
import FilePondPluginFileValidateType from 'filepond-plugin-file-validate-type'
import FilePondPluginImagePreview from 'filepond-plugin-image-preview'
import 'filepond-plugin-image-preview/dist/filepond-plugin-image-preview.min.css'

const emit = defineEmits(['update:modelValue'])
const props = defineProps({
  modelValue: [String, null],
  currentFile: String,
  name: {
    type: String,
    default: 'file',
  }
})

const FilePond = vueFilePond(
  FilePondPluginFileValidateType,
  FilePondPluginImagePreview
)

const serverConfig = {
  process: {
    url: '/admin/filepond/process',
    method: 'POST',
    headers: {
      'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content
    },
    onload: (res) => {
      const response = JSON.parse(res)
      emit('update:modelValue', response.file)
      return response.id
    }
  },
  revert: {
    url: '/admin/filepond/revert',
    method: 'DELETE',
    headers: {
      'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content
    }
  },
  restore: '/admin/filepond/restore/',
  load: '/admin/filepond/load/',
  fetch: '/admin/filepond/fetch/',
  remove: {
    url: '/admin/filepond/remove',
    method: 'DELETE',
    headers: {
      'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content
    }
  }
}

const files = ref([])

watch(
  () => props.currentFile,
  (newVal) => {
    files.value = newVal
      ? [{ source: newVal, options: { type: 'local' } }]
      : []
  },
  { immediate: true }
)
</script>

<template>
  <file-pond
    :name="name"
    :files="files"
    allow-multiple="false"
    accepted-file-types="image/*,application/pdf"
    label-idle="Déposez votre fichier ici ou cliquez"
    :server="serverConfig"
    @addfile="(error, file) => !error && emit('update:modelValue', file.file)"
    @removefile="() => emit('update:modelValue', null)"
  />
</template>
