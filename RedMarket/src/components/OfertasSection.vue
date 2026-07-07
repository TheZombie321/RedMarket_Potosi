<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useCarritoStore } from '../stores/carrito'
import { useToastStore } from '../stores/toast'
import { apiFetch } from '../composables/useApi'

interface Producto {
  id: number
  nombre: string
  precio_venta: number
  precio_oferta?: number
  en_descuento?: boolean
  disponible: boolean
  imagen_url?: string
  categoria?: { nombre: string }
}

const carrito = useCarritoStore()
const toast = useToastStore()
const ofertas = ref<Producto[]>([])
const cargando = ref(true)

onMounted(async () => {
  try {
    const resp = await apiFetch('/productos?descuento=1&per_page=6', { skipAuth: true })
    ofertas.value = resp.data ?? resp
  } catch {} finally {
    cargando.value = false
  }
})

const descuentoPorcentaje = (p: Producto) =>
  Math.round((1 - p.precio_oferta! / p.precio_venta) * 100)
</script>

<template>
  <section v-if="ofertas.length" class="mb-8">
    <div class="flex items-center justify-between mb-4">
      <h2 class="text-lg font-semibold text-gray-800">🔥 Ofertas especiales</h2>
      <RouterLink to="/tienda?descuento=1" class="text-sm text-accent-amber hover:text-amber-600 font-medium no-underline">Ver todas →</RouterLink>
    </div>

    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3">
      <div v-for="p in ofertas" :key="p.id"
        class="bg-white rounded-xl border border-amber-200/60 p-3 flex flex-col items-center text-center hover:shadow-lg hover:shadow-amber-200/30 transition-all duration-200 hover:-translate-y-0.5 glow-amber">
        <RouterLink :to="`/producto/${p.id}`" class="no-underline w-full">
          <div class="h-24 flex items-center justify-center mb-2">
            <img :src="p.imagen_url" :alt="p.nombre" loading="lazy" class="max-h-full max-w-full object-contain" />
          </div>
          <p class="text-xs font-semibold text-gray-800 line-clamp-2 leading-snug h-8">{{ p.nombre }}</p>
          <div class="mt-1">
            <span class="text-xs line-through text-gray-400">Bs. {{ p.precio_venta }}</span>
            <span class="text-sm font-bold text-accent-amber ml-1">Bs. {{ p.precio_oferta }}</span>
          </div>
          <span class="inline-block mt-1 text-[10px] bg-amber-100 text-amber-800 font-bold px-1.5 py-0.5 rounded-full">
            -{{ descuentoPorcentaje(p) }}%
          </span>
        </RouterLink>
        <button @click="carrito.agregarAlCarrito(p); toast.add(`${p.nombre} añadido`, 'success')"
          class="mt-2 w-full bg-accent-amber text-white text-xs py-1.5 rounded-lg font-semibold cursor-pointer border-none hover:bg-amber-600 transition-colors"
          :disabled="!p.disponible">
          Añadir
        </button>
      </div>
    </div>
  </section>
</template>
