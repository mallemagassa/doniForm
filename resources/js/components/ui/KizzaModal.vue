<!-- components/ui/KizzaModal.vue -->
<template>
    <div v-if="open" class="fixed inset-0 z-50 bg-black/50 flex items-center justify-center">
        <div class="bg-white p-6 rounded-xl shadow-xl w-[1400px] max-h-[150vh] overflow-auto">
        <div class="flex justify-between items-center mb-4">
            <div class="flex-1 justify-end">
              <h2 class="text-xl font-semibold text-center">{{ title }}</h2>
            </div>
            <button @click="$emit('close')" class="text-gray-600 hover:text-black">✖</button>
        </div>
        <slot />
        </div>
    </div>
</template>
  
  <script setup>
  defineProps({
    open: Boolean,
    title: String
  });
  defineEmits(['close']);
  </script>
  