<script setup lang="ts">
defineProps<{
  actual: number
  total: number
}>()
const emit = defineEmits<{
  anterior: []
  siguiente: []
}>()
</script>

<template>
  <div class="flex items-center justify-center gap-3 mt-4">
    <button @click="emit('anterior')" :disabled="actual <= 1"
      class="px-4 py-2 text-sm rounded border border-gray-300 text-gray-700 hover:bg-gray-100 disabled:opacity-40 disabled:cursor-not-allowed cursor-pointer bg-white min-h-[44px]">
      ← Anterior
    </button>
    <span class="text-sm text-gray-600">Pág. {{ actual }} / {{ total }}</span>
    <button @click="emit('siguiente')" :disabled="actual >= total"
      class="px-4 py-2 text-sm rounded border border-gray-300 text-gray-700 hover:bg-gray-100 disabled:opacity-40 disabled:cursor-not-allowed cursor-pointer bg-white min-h-[44px]">
      Siguiente →
    </button>
  </div>
</template>
