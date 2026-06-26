<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { apiFetch } from '../composables/useApi'
import { useCarritoStore } from '../stores/carrito'
import { useSearchStore } from '../stores/search'
import { useToastStore } from '../stores/toast'
import Paginador from '../components/Paginador.vue'

interface Producto {
  id: number
  nombre: string
  descripcion?: string
  precio_venta: number
  precio_oferta?: number
  en_descuento?: boolean
  stock_actual: number
  imagen_url?: string
  categoria_id?: number
  categoria?: { id: number; nombre: string }
}

interface PaginationMeta {
  current_page: number
  last_page: number
  total: number
  per_page: number
}

interface CategoriaItem {
  id: number
  nombre: string
}

const carrito = useCarritoStore()
const search = useSearchStore()
const toast = useToastStore()
const route = useRoute()
const router = useRouter()

const productos = ref<Producto[]>([])
const cargando = ref(true)
const error = ref('')
const mostrarTop = ref(false)
const categoriaActiva = ref<number | null>(null)
const soloDescuento = ref(false)
const page = ref(1)
const meta = ref<PaginationMeta | null>(null)
const categorias = ref<CategoriaItem[]>([])

const nombreCategoria = computed(() => {
  const id = categoriaActiva.value
  if (!id) return ''
  const cat = categorias.value.find(c => c.id === id)
  return cat?.nombre ?? `Categoria ${id}`
})

const descuentoPorcentaje = (p: Producto) =>
  p.en_descuento && p.precio_oferta
    ? Math.round((1 - p.precio_oferta / p.precio_venta) * 100)
    : 0

const irArriba = () => window.scrollTo({ top: 0, behavior: 'smooth' })
const onScroll = () => { mostrarTop.value = window.scrollY > 400 }

const limpiarFiltros = () => {
  categoriaActiva.value = null
  soloDescuento.value = false
  search.query = ''
  router.push('/tienda')
}

const buildQuery = (pg: number) => {
  const params = new URLSearchParams()
  params.set('page', String(pg))
  params.set('per_page', '20')
  if (categoriaActiva.value) params.set('categoria', String(categoriaActiva.value))
  if (soloDescuento.value) params.set('descuento', '1')
  if (search.query.trim()) params.set('search', search.query.trim())
  return params.toString()
}

const obtenerPagina = async (pg: number) => {
  cargando.value = true
  window.scrollTo({ top: 0, behavior: 'auto' })
  try {
    const query = buildQuery(pg)
    const resp = await apiFetch(`/productos?${query}`, { skipAuth: true })
    productos.value = resp.data ?? []
    meta.value = {
      current_page: resp.current_page,
      last_page: resp.last_page,
      total: resp.total,
      per_page: resp.per_page
    }
  } catch (e: any) {
    error.value = e.message || 'Error al cargar productos'
    productos.value = []
  } finally {
    cargando.value = false
  }
}

const irPagina = (pg: number) => {
  if (pg < 1 || (meta.value && pg > meta.value.last_page)) return
  page.value = pg
  obtenerPagina(pg)
}

const actualizarFiltros = () => {
  page.value = 1
  obtenerPagina(1)
}

watch(categoriaActiva, actualizarFiltros)
watch(soloDescuento, actualizarFiltros)
watch(() => search.query, actualizarFiltros)

onMounted(async () => {
  try {
    const catResp = await apiFetch('/categorias', { skipAuth: true })
    categorias.value = catResp.data ?? catResp
  } catch {}
  const catParam = route.query.categoria
  if (catParam) {
    const parsed = parseInt(catParam as string, 10)
    if (!isNaN(parsed)) categoriaActiva.value = parsed
  }
  const descParam = route.query.descuento
  if (descParam === '1') soloDescuento.value = true
  const searchParam = route.query.search
  if (searchParam) search.query = searchParam as string
  page.value = 1
  obtenerPagina(1)
  window.addEventListener('scroll', onScroll, { passive: true })
})

onUnmounted(() => window.removeEventListener('scroll', onScroll))
</script>

<template>
  <div>
    <div class="flex items-center gap-3 mb-6 flex-wrap">
      <h1 class="text-2xl font-bold text-ink">Catalogo RedMarket Potosi</h1>
      <span v-if="soloDescuento" @click="soloDescuento = false"
        class="text-xs bg-red-100 text-red-700 px-3 py-1 rounded-full cursor-pointer hover:bg-red-200 transition-colors">
        X Solo ofertas
      </span>
      <span v-if="categoriaActiva" @click="categoriaActiva = null"
        class="text-xs bg-blue-100 text-blue-700 px-3 py-1 rounded-full cursor-pointer hover:bg-blue-200 transition-colors">
        X {{ nombreCategoria }}
      </span>
    </div>

    <div v-if="cargando" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-5">
      <div v-for="i in 8" :key="i" class="bg-blanco-mercado rounded-xl shadow-sm border border-platform-edge p-4">
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
      <button @click="obtenerPagina(1)" class="bg-red-700 text-white px-4 py-2 rounded-lg cursor-pointer border-none hover:bg-red-800">Reintentar</button>
    </div>

    <div v-else-if="!productos.length" class="text-center py-12">
      <p class="text-ink-dim mb-3">Sin resultados</p>
      <button v-if="categoriaActiva || soloDescuento || search.query" @click="limpiarFiltros"
        class="bg-gray-200 text-gray-700 px-4 py-2 rounded-lg cursor-pointer border-none hover:bg-gray-300">Limpiar filtros</button>
    </div>

    <template v-else>
      <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-5">
        <div v-for="p in productos" :key="p.id"
          :class="[
            'bg-blanco-mercado rounded-xl border border-platform-edge p-4 flex flex-col relative overflow-hidden',
            'transition-all duration-200 ease-out',
            p.stock_actual > 0
              ? 'shadow-sm hover:shadow-md hover:-translate-y-0.5 cursor-pointer'
              : 'opacity-60 grayscale-[30%] cursor-not-allowed'
          ]">

          <div v-if="p.stock_actual <= 0"
            class="absolute top-3 right-3 bg-ink/80 text-white text-[10px] font-semibold uppercase tracking-wider px-2 py-0.5 rounded-full z-10">
            Agotado
          </div>

          <div v-if="p.en_descuento && p.precio_oferta"
            class="absolute top-3 right-3 bg-rojo-mercado text-white text-[10px] font-bold px-2 py-0.5 rounded-full z-10">
            -{{ descuentoPorcentaje(p) }}%
          </div>

          <div class="h-44 flex items-center justify-center mb-3">
            <img :src="p.imagen_url" :alt="p.nombre" loading="lazy" class="max-h-full max-w-full object-contain transition-transform duration-200" :class="p.stock_actual > 0 && 'group-hover:scale-105'" />
          </div>

          <div class="flex-1">
            <h3 class="text-sm font-semibold text-ink line-clamp-2 leading-snug">{{ p.nombre }}</h3>
            <span class="text-[11px] text-ink-muted uppercase tracking-wide">{{ p.categoria?.nombre }}</span>
            <div class="flex items-center gap-2 mt-1.5">
              <p class="text-lg font-bold text-price">Bs. {{ p.precio_venta }}</p>
              <span v-if="p.en_descuento && p.precio_oferta" class="text-sm text-rojo-mercado font-semibold">Bs. {{ p.precio_oferta }}</span>
            </div>
          </div>

          <div class="mt-3 flex gap-2">
            <RouterLink :to="p.stock_actual > 0 ? `/producto/${p.id}` : '#'"
              class="flex-1 text-center border text-sm font-medium no-underline rounded-lg py-2 px-3 transition-all duration-150 ease-out"
              :class="p.stock_actual > 0
                ? 'border-platform-edge text-ink hover:bg-potos-stone hover:border-ink-dim active:scale-[0.97]'
                : 'border-platform-edge/50 text-ink-dim pointer-events-none'">
              Ver Detalle
            </RouterLink>
            <button v-if="p.stock_actual > 0"
              @click="carrito.agregarAlCarrito(p); toast.add(`${p.nombre} anadido al carrito`, 'success')"
              class="flex-1 bg-ink text-white py-2 px-3 rounded-lg text-sm font-semibold cursor-pointer border-none
                transition-all duration-150 ease-out
                hover:bg-ink/90 hover:shadow-sm
                active:scale-[0.97] active:shadow-none
                focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-ink">
              Anadir
            </button>
            <button v-else disabled
              class="flex-1 bg-platform-edge/60 text-ink-dim py-2 px-3 rounded-lg text-sm font-semibold border-none cursor-not-allowed">
              Sin Stock
            </button>
          </div>
        </div>
      </div>

      <Paginador v-if="meta" :actual="meta.current_page" :total="meta.last_page"
        @anterior="irPagina(page - 1)" @siguiente="irPagina(page + 1)" />
    </template>

    <button v-if="mostrarTop" @click="irArriba" aria-label="Volver arriba"
      class="fixed bottom-6 right-6 w-12 h-12 bg-ink text-white rounded-full shadow-lg flex items-center justify-center text-lg
        transition-all duration-200 ease-out
        hover:bg-ink/90 hover:shadow-xl hover:-translate-y-0.5
        active:scale-95 active:shadow-md
        cursor-pointer border-none z-dropdown
        focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-white">
      <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 20 20" fill="currentColor">
        <path fill-rule="evenodd" d="M14.707 12.707a1 1 0 01-1.414 0L10 9.414l-3.293 3.293a1 1 0 01-1.414-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 010 1.414z" clip-rule="evenodd" />
      </svg>
    </button>
  </div>
</template>
