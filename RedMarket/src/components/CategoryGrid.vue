<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { apiFetch } from '../composables/useApi'

interface Categoria {
  id: number
  nombre: string
}

interface Producto {
  id: number
  imagen_url?: string
}

const iconMap: Record<string, { icono: string; gradiente: string }> = {
  'Accesorios de Cocina': { icono: '🍳', gradiente: 'from-amber-900/70 to-amber-700/30' },
  'Accesorios Deportivos': { icono: '⚽', gradiente: 'from-lime-900/70 to-lime-700/30' },
  'Accesorios Móviles': { icono: '📱', gradiente: 'from-sky-900/70 to-sky-700/30' },
  'Belleza': { icono: '💄', gradiente: 'from-pink-900/70 to-pink-700/30' },
  'Blusas': { icono: '👚', gradiente: 'from-rose-900/70 to-rose-700/30' },
  'Camisetas Hombre': { icono: '👕', gradiente: 'from-blue-900/70 to-blue-700/30' },
  'Carteras Mujer': { icono: '👛', gradiente: 'from-purple-900/70 to-purple-700/30' },
  'Cuidado de la Piel': { icono: '🧴', gradiente: 'from-orange-900/70 to-orange-700/30' },
  'Decoración del Hogar': { icono: '🖼️', gradiente: 'from-teal-900/70 to-teal-700/30' },
  'Fragancias': { icono: '🧴', gradiente: 'from-violet-900/70 to-violet-700/30' },
  'Joyería Mujer': { icono: '💍', gradiente: 'from-yellow-900/70 to-yellow-700/30' },
  'Laptops': { icono: '💻', gradiente: 'from-gray-900/70 to-gray-700/30' },
  'Lentes de Sol': { icono: '🕶️', gradiente: 'from-slate-900/70 to-slate-700/30' },
  'Motocicletas': { icono: '🏍️', gradiente: 'from-neutral-900/70 to-neutral-700/30' },
  'Muebles': { icono: '🛋️', gradiente: 'from-stone-900/70 to-stone-700/30' },
  'Relojes Hombre': { icono: '⌚', gradiente: 'from-cyan-900/70 to-cyan-700/30' },
  'Relojes Mujer': { icono: '⌚', gradiente: 'from-indigo-900/70 to-indigo-700/30' },
  'Smartphones': { icono: '📱', gradiente: 'from-emerald-900/70 to-emerald-700/30' },
  'Tablets': { icono: '📟', gradiente: 'from-green-900/70 to-green-700/30' },
  'Vehículos': { icono: '🚗', gradiente: 'from-red-900/70 to-red-700/30' },
  'Vestidos Mujer': { icono: '👗', gradiente: 'from-fuchsia-900/70 to-fuchsia-700/30' },
  'Zapatos Hombre': { icono: '👞', gradiente: 'from-blue-900/70 to-blue-700/30' },
  'Zapatos Mujer': { icono: '👠', gradiente: 'from-pink-900/70 to-pink-700/30' },
  'Bebidas': { icono: '🥤', gradiente: 'from-sky-900/70 to-sky-700/30' },
  'Lácteos': { icono: '🥛', gradiente: 'from-green-900/70 to-green-700/30' },
  'Carnes y Embutidos': { icono: '🥩', gradiente: 'from-red-900/70 to-red-700/30' },
  'Frutas y Verduras': { icono: '🥦', gradiente: 'from-lime-900/70 to-lime-700/30' },
  'Snacks y Golosinas': { icono: '🍪', gradiente: 'from-pink-900/70 to-pink-700/30' },
}

interface CategoriaConImagen extends Categoria {
  icono: string
  gradiente: string
  imagen_url?: string
}

const categorias = ref<CategoriaConImagen[]>([])

onMounted(async () => {
  try {
    const resp = await apiFetch('/categorias', { skipAuth: true })
    const items: Categoria[] = resp.data ?? resp

    const resultados = await Promise.allSettled(
      items.map(cat =>
        apiFetch(`/productos?categoria=${cat.id}&per_page=1`, { skipAuth: true, silent: true })
          .then(r => {
            const prods: Producto[] = r.data ?? r
            return { id: cat.id, imagen: prods[0]?.imagen_url }
          })
          .catch(() => ({ id: cat.id, imagen: undefined }))
      )
    )

    const imagenMap: Record<number, string | undefined> = {}
    resultados.forEach(r => {
      if (r.status === 'fulfilled') {
        imagenMap[r.value.id] = r.value.imagen
      }
    })

    categorias.value = items.map(c => ({
      ...c,
      ...(iconMap[c.nombre] ?? { icono: '📦', gradiente: 'from-gray-900/70 to-gray-700/30' }),
      imagen_url: imagenMap[c.id],
    }))
  } catch {
    categorias.value = Object.entries(iconMap).map(([nombre, val], i) => ({
      id: i + 1, nombre: nombre, ...val,
    }))
  }
})
</script>

<template>
  <section class="mb-8">
    <h2 class="text-lg font-semibold text-gray-800 mb-4">Categorías</h2>
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4">
      <RouterLink v-for="cat in categorias" :key="cat.id" :to="`/tienda?categoria=${cat.id}`"
        class="relative flex flex-col items-center justify-end p-4 rounded-xl no-underline overflow-hidden group"
        style="aspect-ratio: 4/3">
        <img v-if="cat.imagen_url" :src="cat.imagen_url" :alt="cat.nombre"
          class="absolute inset-0 w-full h-full object-cover transition-transform duration-500 ease-out group-hover:scale-110" />
        <div class="absolute inset-0 bg-gradient-to-t transition-opacity duration-300" :class="cat.gradiente"></div>
        <span class="text-3xl relative z-10 mb-1 drop-shadow-sm">{{ cat.icono }}</span>
        <span class="font-semibold text-sm text-white text-center relative z-10 drop-shadow-sm">{{ cat.nombre }}</span>
      </RouterLink>
    </div>
  </section>
</template>
