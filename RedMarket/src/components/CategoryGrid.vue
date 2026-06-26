<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { apiFetch } from '../composables/useApi'

interface Categoria {
  id: number
  nombre: string
}

const iconMap: Record<string, { icono: string; color: string }> = {
  'Abarrotes': { icono: '🍚', color: 'bg-amber-50 text-amber-700 border-amber-200' },
  'Bebidas': { icono: '🥤', color: 'bg-blue-50 text-blue-700 border-blue-200' },
  'Lácteos': { icono: '🥛', color: 'bg-green-50 text-green-700 border-green-200' },
  'Limpieza': { icono: '🧹', color: 'bg-purple-50 text-purple-700 border-purple-200' },
  'Panadería': { icono: '🥖', color: 'bg-orange-50 text-orange-700 border-orange-200' },
  'Carnes y Embutidos': { icono: '🥩', color: 'bg-red-50 text-red-700 border-red-200' },
  'Frutas y Verduras': { icono: '🥦', color: 'bg-lime-50 text-lime-700 border-lime-200' },
  'Snacks y Golosinas': { icono: '🍪', color: 'bg-pink-50 text-pink-700 border-pink-200' },
}

const categorias = ref<(Categoria & { icono: string; color: string })[]>([])

onMounted(async () => {
  try {
    const resp = await apiFetch('/categorias', { skipAuth: true })
    const items: Categoria[] = resp.data ?? resp
    categorias.value = items.map(c => ({
      ...c,
      ...(iconMap[c.nombre] ?? { icono: '📦', color: 'bg-gray-50 text-gray-700 border-gray-200' }),
    }))
  } catch {
    categorias.value = Object.entries(iconMap).map(([nombre, val], i) => ({
      id: i + 1, nombre, ...val,
    }))
  }
})
</script>

<template>
  <section class="mb-8">
    <h2 class="text-lg font-semibold text-gray-800 mb-4">Categorías</h2>
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4">
      <RouterLink v-for="cat in categorias" :key="cat.id" :to="`/tienda?categoria=${cat.id}`"
        class="flex flex-col items-center gap-2 p-5 rounded-xl border no-underline transition-all duration-200 hover:shadow-md hover:-translate-y-0.5"
        :class="cat.color">
        <span class="text-3xl">{{ cat.icono }}</span>
        <span class="font-semibold text-sm text-center">{{ cat.nombre }}</span>
      </RouterLink>
    </div>
  </section>
</template>
