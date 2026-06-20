<script setup lang="ts">
import { useRouter } from 'vue-router'
const router = useRouter()
</script>

<template>
  <div class="min-h-[70vh] flex items-center justify-center">
    <div class="text-center">
      <div class="text-7xl font-bold text-gray-200 mb-4">404</div>
      <h1 class="text-2xl font-bold text-ink mb-2">Página no encontrada</h1>
      <p class="text-ink-muted mb-6">La página que buscas no existe.</p>
      <button @click="router.push('/')"
        class="bg-red-700 text-white px-6 py-2.5 rounded-lg font-semibold hover:bg-red-800 cursor-pointer border-none">
        Volver a la tienda
      </button>
    </div>
  </div>
</template>
