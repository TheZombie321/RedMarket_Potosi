<script setup lang="ts">
import { ref, onMounted, computed, onUnmounted } from 'vue'
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
  'Despensa': { icono: '🛒️', gradiente: 'from-orange-900/70 to-orange-700/30' },
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
}

interface CategoriaConImagen extends Categoria {
  icono: string
  gradiente: string
  imagenes: string[]
  currentIdx: number
}

const categorias = ref<CategoriaConImagen[]>([])
const expandida = ref(false)
const VISIBLES = 5
let timer: ReturnType<typeof setInterval> | null = null

const categoriasVisibles = computed(() =>
  expandida.value ? categorias.value : categorias.value.slice(0, VISIBLES)
)

function avanzaRotacion() {
  categorias.value = categorias.value.map(c => {
    if (c.imagenes.length > 1) {
      return { ...c, currentIdx: (c.currentIdx + 1) % c.imagenes.length }
    }
    return c
  })
}

onMounted(async () => {
  timer = setInterval(avanzaRotacion, 3500)
  try {
    const resp = await apiFetch('/categorias', { skipAuth: true })
    const items: Categoria[] = resp.data ?? resp

    const resultados = await Promise.allSettled(
      items.map(cat =>
        apiFetch(`/productos?categoria=${cat.id}&per_page=5&random=1`, { skipAuth: true, silent: true })
          .then(r => {
            const prods: Producto[] = r.data ?? r
            return { id: cat.id, imagenes: prods.map(p => p.imagen_url).filter(Boolean) as string[] }
          })
          .catch(() => ({ id: cat.id, imagenes: [] as string[] }))
      )
    )

    const imagenMap: Record<number, string[]> = {}
    resultados.forEach(r => {
      if (r.status === 'fulfilled') {
        imagenMap[r.value.id] = r.value.imagenes
      }
    })

    categorias.value = items.map(c => ({
      ...c,
      ...(iconMap[c.nombre] ?? { icono: '📦', gradiente: 'from-gray-900/70 to-gray-700/30' }),
      imagenes: imagenMap[c.id] ?? [],
      currentIdx: 0,
    }))
  } catch {
    categorias.value = Object.entries(iconMap).map(([nombre, val], i) => ({
      id: i + 1, nombre: nombre, ...val,
      imagenes: [] as string[], currentIdx: 0,
    }))
  }
})

onUnmounted(() => {
  if (timer) clearInterval(timer)
})
</script>

<template>
  <section class="mb-8">
    <h2 class="text-lg font-semibold text-gray-800 mb-4">Categorías</h2>
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-4">
      <RouterLink
        v-for="cat in categoriasVisibles" :key="cat.id"
        :to="`/tienda?categoria=${cat.id}`"
        class="relative flex flex-col items-center justify-end p-4 rounded-xl no-underline overflow-hidden group transition-all duration-300 hover:-translate-y-1 hover:shadow-lg"
        style="aspect-ratio: 4/3"
      >
        <div class="absolute inset-0">
          <img
            v-for="(img, i) in cat.imagenes.slice(0, 5)" :key="i"
            :src="img" :alt="cat.nombre"
            class="absolute inset-0 w-full h-full object-cover transition-opacity duration-700"
            :class="i === cat.currentIdx ? 'opacity-100' : 'opacity-0'"
          />
        </div>
        <div class="absolute inset-0 bg-gradient-to-t transition-opacity duration-300" :class="cat.gradiente"></div>
        <span class="text-3xl relative z-10 mb-1 drop-shadow-sm">{{ cat.icono }}</span>
        <span class="font-semibold text-sm text-white text-center relative z-10 drop-shadow-sm">{{ cat.nombre }}</span>
      </RouterLink>
    </div>
    <button
      v-if="categorias.length > VISIBLES"
      @click="expandida = !expandida"
      class="mt-4 mx-auto block px-6 py-2 rounded-lg border border-gray-300 text-sm font-medium text-gray-600 hover:bg-gray-50 hover:border-gray-400 transition-colors cursor-pointer"
    >
      {{ expandida ? 'Ver menos' : `Ver más (${categorias.length - VISIBLES} más)` }}
    </button>
  </section>
</template>
