<script setup lang="ts">
import { ref, onMounted, onUnmounted } from 'vue'
import { apiFetch } from '../composables/useApi'

interface Producto {
  id: number
  nombre: string
  precio_venta: number
  precio_oferta?: number
  en_descuento?: boolean
  imagen_url?: string
  categoria?: { nombre: string }
}

const slides = ref<Producto[]>([])
const current = ref(0)
let timer: ReturnType<typeof setInterval> | null = null

onMounted(async () => {
  try {
    const resp = await apiFetch('/productos?per_page=5', { skipAuth: true })
    const items: Producto[] = resp.data ?? resp
    if (items.length) {
      slides.value = items.sort(() => Math.random() - 0.5).slice(0, 4)
    }
  } catch {}
  timer = setInterval(() => next(), 5000)
})

onUnmounted(() => { if (timer) clearInterval(timer) })

const next = () => { current.value = (current.value + 1) % slides.value.length }
const prev = () => { current.value = (current.value - 1 + slides.value.length) % slides.value.length }
const goTo = (i: number) => { current.value = i }
</script>

<template>
  <div v-if="slides.length" class="relative overflow-hidden rounded-xl bg-gray-900 mb-8" style="aspect-ratio: 2.5 / 1">
    <div class="absolute inset-0 flex transition-transform duration-500 ease-out"
      :style="{ transform: `translateX(-${current * 100}%)` }">
      <div v-for="(slide, i) in slides" :key="slide.id"
        class="w-full h-full shrink-0 relative">
        <img v-if="slide.imagen_url" :src="slide.imagen_url" :alt="slide.nombre"
          class="w-full h-full object-cover" />
        <div class="absolute inset-0 bg-gradient-to-t from-black/70 via-black/20 to-transparent"></div>
        <div class="absolute bottom-0 left-0 right-0 z-10 p-6 sm:p-10 text-white max-w-lg">
          <span v-if="slide.categoria" class="text-xs uppercase tracking-wider text-white/70">{{ slide.categoria.nombre }}</span>
          <h3 class="text-xl sm:text-3xl font-bold mt-1 leading-tight">{{ slide.nombre }}</h3>
          <div class="mt-2 flex items-center gap-3">
            <p v-if="slide.en_descuento && slide.precio_oferta" class="text-lg sm:text-xl font-bold text-green-400">
              Bs. {{ slide.precio_oferta }}
              <span class="text-sm line-through text-white/50 ml-2">Bs. {{ slide.precio_venta }}</span>
            </p>
            <p v-else class="text-lg sm:text-xl font-bold">Bs. {{ slide.precio_venta }}</p>
          </div>
          <RouterLink :to="`/producto/${slide.id}`"
            class="inline-block mt-3 bg-white text-gray-900 px-4 py-2 rounded-lg text-sm font-semibold no-underline hover:bg-gray-100 transition-colors">
            Ver producto
          </RouterLink>
        </div>
      </div>
    </div>

    <button @click="prev"
      class="absolute left-3 top-1/2 -translate-y-1/2 w-10 h-10 rounded-full bg-white/20 hover:bg-white/40 text-white flex items-center justify-center text-lg cursor-pointer border-none backdrop-blur-sm transition-colors z-20">
      ‹
    </button>
    <button @click="next"
      class="absolute right-3 top-1/2 -translate-y-1/2 w-10 h-10 rounded-full bg-white/20 hover:bg-white/40 text-white flex items-center justify-center text-lg cursor-pointer border-none backdrop-blur-sm transition-colors z-20">
      ›
    </button>

    <div class="absolute bottom-3 left-1/2 -translate-x-1/2 flex gap-2 z-20">
      <button v-for="(_, i) in slides" :key="i" @click="goTo(i)"
        class="w-2 h-2 rounded-full border-none cursor-pointer transition-all"
        :class="i === current ? 'bg-white w-5' : 'bg-white/40 hover:bg-white/60'">
      </button>
    </div>
  </div>

  <div v-else class="rounded-xl bg-gradient-to-br from-red-800 to-red-600 mb-8 p-8 sm:p-12 text-white"
    style="aspect-ratio: 2.5 / 1">
    <div class="flex flex-col justify-center h-full">
      <h2 class="text-2xl sm:text-4xl font-bold leading-tight">RedMarket Potosí</h2>
      <p class="text-white/80 mt-2 text-sm sm:text-base max-w-md">Tu tienda virtual de confianza. Productos de calidad al mejor precio, entregados en tu domicilio.</p>
      <RouterLink to="/tienda"
        class="inline-block mt-4 bg-white text-red-700 px-5 py-2 rounded-lg text-sm font-semibold no-underline w-fit hover:bg-gray-100 transition-colors">
        Comprar ahora
      </RouterLink>
    </div>
  </div>
</template>
