<script setup lang="ts">
defineProps<{
  puntuacion: number
  size?: 'sm' | 'md' | 'lg'
}>()
</script>

<template>
  <span class="inline-flex gap-0.5" :class="size === 'sm' ? 'text-sm' : size === 'lg' ? 'text-xl' : 'text-base'">
    <span v-for="i in 5" :key="i"
      :class="i <= puntuacion ? 'text-yellow-400' : 'text-gray-300'">★</span>
  </span>
</template>
