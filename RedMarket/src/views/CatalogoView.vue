<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { apiFetch } from '../composables/useApi'
import { useCarritoStore } from '../stores/carrito'
import { useSearchStore } from '../stores/search'
import { useToastStore } from '../stores/toast'

const carrito = useCarritoStore()
const search = useSearchStore()
const toast = useToastStore()
const productos = ref([])
const cargando = ref(true)
const error = ref('')
const mostrarTop = ref(false)

const filtrados = computed(() => {
  const q = search.query.toLowerCase().trim()
  if (!q) return productos.value
  return productos.value.filter((p: any) =>
    p.nombre.toLowerCase().includes(q) ||
    p.categoria?.nombre?.toLowerCase().includes(q)
  )
})

const obtenerProductos = async () => {
  try {
    const resp = await apiFetch('/productos', { skipAuth: true })
    productos.value = resp.data ?? resp
  } catch (e: any) {
    error.value = e.message || 'Error al cargar productos'
  } finally {
    cargando.value = false
  }
}

const irArriba = () => window.scrollTo({ top: 0, behavior: 'smooth' })
const onScroll = () => { mostrarTop.value = window.scrollY > 400 }

onMounted(() => {
  obtenerProductos()
  window.addEventListener('scroll', onScroll, { passive: true })
})

onUnmounted(() => window.removeEventListener('scroll', onScroll))
</script>

<template>
  <div>
    <h1 class="text-2xl font-bold text-ink mb-6">Catálogo RedMarket Potosí</h1>

    <div v-if="cargando" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-5">
      <div v-for="i in 8" :key="i" class="bg-blanco-mercado rounded-xl shadow-[0_1px_3px_rgba(0,0,0,0.06)] border border-platform-edge p-4">
        <div class="skeleton h-44 mb-3 rounded-lg"></div>
        <div class="skeleton h-4 w-3/4 mb-2"></div>
        <div class="skeleton h-3 w-1/2 mb-2"></div>
        <div class="skeleton h-5 w-1/3 mb-3"></div>
        <div class="flex gap-2">
          <div class="skeleton h-9 flex-1 rounded-lg"></div>
          <div class="skeleton h-9 flex-1 rounded-lg"></div>
        </div>
      </div>
    </div>

    <div v-else-if="error" class="text-center py-12">
      <p class="text-rojo-mercado-dark mb-3">{{ error }}</p>
      <button @click="obtenerProductos" class="bg-red-700 text-white px-4 py-2 rounded-lg cursor-pointer border-none hover:bg-red-800">Reintentar</button>
    </div>

    <div v-else-if="!filtrados.length" class="text-center py-12 text-ink-dim">Sin resultados para «{{ search.query }}»</div>

    <div v-else class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-5">
      <div v-for="p in filtrados" :key="p.id"
        :class="[
          'bg-blanco-mercado rounded-xl border border-platform-edge p-4 flex flex-col relative overflow-hidden',
          'transition-all duration-200 ease-out',
          p.stock_actual > 0
            ? 'shadow-[0_1px_3px_rgba(0,0,0,0.06)] hover:shadow-[0_8px_24px_rgba(0,0,0,0.08)] hover:-translate-y-0.5 cursor-pointer'
            : 'opacity-60 grayscale-[30%] cursor-not-allowed'
        ]">

        <!-- Badge sin stock -->
        <div v-if="p.stock_actual <= 0"
          class="absolute top-3 right-3 bg-ink/80 text-white text-[10px] font-semibold uppercase tracking-wider px-2 py-0.5 rounded-full z-10">
          Agotado
        </div>

        <!-- Badge descuento -->
        <div v-if="p.en_descuento && p.precio_oferta"
          class="absolute top-3 right-3 bg-rojo-mercado text-white text-[10px] font-bold px-2 py-0.5 rounded-full z-10">
          −{{ Math.round((1 - p.precio_oferta / p.precio_venta) * 100) }}%
        </div>

        <div class="h-44 flex items-center justify-center mb-3">
          <img :src="p.imagen_url" :alt="p.nombre" loading="lazy" class="max-h-full max-w-full object-contain transition-transform duration-200" :class="p.stock_actual > 0 && 'group-hover:scale-105'" />
        </div>

        <div class="flex-1">
          <h3 class="text-sm font-semibold text-ink line-clamp-2 leading-snug">{{ p.nombre }}</h3>
          <span class="text-[11px] text-ink-muted uppercase tracking-wide">{{ p.categoria?.nombre }}</span>
          <div class="flex items-center gap-2 mt-1.5">
            <p class="text-lg font-bold text-price">Bs. {{ p.precio_venta }}</p>
          </div>
        </div>

        <div class="mt-3 flex gap-2">
          <RouterLink :to="p.stock_actual > 0 ? `/producto/${p.id}` : '#'"
            :class="[
              'flex-1 text-center border text-sm font-medium no-underline rounded-lg py-2 px-3',
              'transition-all duration-150 ease-out',
              p.stock_actual > 0
                ? 'border-platform-edge text-ink hover:bg-potos-stone hover:border-ink-dim active:scale-[0.97]'
                : 'border-platform-edge/50 text-ink-dim pointer-events-none'
            ]">
            Ver Detalle
          </RouterLink>
          <button v-if="p.stock_actual > 0"
            @click="carrito.agregarAlCarrito(p); toast.add(`${p.nombre} añadido al carrito`, 'success')"
            class="flex-1 bg-ink text-white py-2 px-3 rounded-lg text-sm font-semibold cursor-pointer border-none
              transition-all duration-150 ease-out
              hover:bg-ink/90 hover:shadow-[0_2px_8px_rgba(55,65,81,0.25)]
              active:scale-[0.97] active:shadow-none
              focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-ink">
            Añadir
          </button>
          <button v-else
            disabled
            class="flex-1 bg-platform-edge/60 text-ink-dim py-2 px-3 rounded-lg text-sm font-semibold border-none cursor-not-allowed">
            Sin Stock
          </button>
        </div>
      </div>
    </div>

    <!-- Back to top -->
    <button v-if="mostrarTop" @click="irArriba" aria-label="Volver arriba"
      class="fixed bottom-6 right-6 w-12 h-12 bg-ink text-white rounded-full shadow-[0_4px_12px_rgba(0,0,0,0.15)] flex items-center justify-center text-lg
        transition-all duration-200 ease-out
        hover:bg-ink/90 hover:shadow-[0_6px_20px_rgba(0,0,0,0.2)] hover:-translate-y-0.5
        active:scale-95 active:shadow-[0_2px_8px_rgba(0,0,0,0.15)]
        cursor-pointer border-none z-dropdown
        focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-white">
      <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 20 20" fill="currentColor">
        <path fill-rule="evenodd" d="M14.707 12.707a1 1 0 01-1.414 0L10 9.414l-3.293 3.293a1 1 0 01-1.414-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 010 1.414z" clip-rule="evenodd" />
      </svg>
    </button>
  </div>
</template>