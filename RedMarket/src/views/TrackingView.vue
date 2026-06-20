<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted, watch, nextTick } from 'vue'
import { apiFetch } from '../composables/useApi'
import { useAuthStore } from '../stores/auth'
import { useToastStore } from '../stores/toast'
import { useRouter } from 'vue-router'
import { estadoBadge, LABELS as labels, PASOS as pasos, pasoActual } from '../utils/estados'
import 'leaflet/dist/leaflet.css'
import L from 'leaflet'
import ConfirmDialog from '../components/ConfirmDialog.vue'

// Fix Leaflet default icon paths for Vite
delete (L.Icon.Default.prototype as any)._getIconUrl
L.Icon.Default.mergeOptions({
  iconRetinaUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/images/marker-icon-2x.png',
  iconUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/images/marker-icon.png',
  shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/images/marker-shadow.png',
})

const auth = useAuthStore()
const toast = useToastStore()
const router = useRouter()
const todosPedidos = ref<any[]>([])
const cargando = ref(true)
const error = ref('')
let interval: ReturnType<typeof setInterval> | null = null
let fetchAbort: AbortController | null = null

// Staff no debería ver tracking de clientes
if (auth.user?.roles?.some((r: any) => ['Administrador', 'Encargado', 'Picking', 'Repartidor'].includes(r.name ?? r))) {
  router.replace('/')
}

// Mapas para pedidos en_camino
const mapRefs = ref<Record<number, HTMLDivElement | null>>({})
const maps = ref<Record<number, L.Map>>({})
const markers = ref<Record<number, L.Marker>>({})

const enProceso = computed(() =>
  todosPedidos.value.filter((p: any) => ['pendiente', 'en_preparacion', 'listo_despacho', 'en_camino'].includes(p.estado))
)

const historial = computed(() =>
  todosPedidos.value.filter((p: any) => ['entregado', 'cancelado'].includes(p.estado))
)

const pedidosEnCamino = computed(() =>
  todosPedidos.value.filter((p: any) => p.estado === 'en_camino')
)

const puedeCancelar = (p: any) => p.estado === 'pendiente'
const confirmData = ref<{ pedido: any } | null>(null)

const estadoHeaderBg = (e: string) => {
  const map: Record<string, string> = { pendiente: 'bg-yellow-50', en_preparacion: 'bg-orange-50', listo_despacho: 'bg-blue-50', en_camino: 'bg-blue-50' }
  return map[e] || 'bg-blue-50'
}

const cancelarPedido = async (p: any) => {
  confirmData.value = { pedido: p }
}

const confirmarCancelacion = async () => {
  if (!confirmData.value) return
  const p = confirmData.value.pedido
  confirmData.value = null
  try {
    await apiFetch(`/pedidos/${p.id}`, {
      method: 'PUT',
      body: JSON.stringify({ estado: 'cancelado' }),
    })
    p.estado = 'cancelado'
  } catch (e: any) { toast.add('Error: ' + e.message, 'error') }
}

const fetchPedidos = async () => {
  if (!auth.token) { cargando.value = false; return }
  if (fetchAbort) fetchAbort.abort()
  fetchAbort = new AbortController()
  try {
    todosPedidos.value = await apiFetch('/pedidos', { signal: fetchAbort.signal } as any)
    await nextTick()
    setTimeout(() => initPendingMaps(), 150)
  } catch (e: any) {
    if (e.name !== 'AbortError') { error.value = e.message || 'Error al cargar pedidos' }
  } finally { cargando.value = false }
}

const parseUbicacion = (u: any): { lat: number; lng: number } | null => {
  if (!u) return null
  if (typeof u === 'string') { try { return JSON.parse(u) } catch { return null } }
  if (typeof u.lat === 'number' && typeof u.lng === 'number') return u
  return null
}

const initPendingMaps = () => {
  const pendientes = pedidosEnCamino.value
  pendientes.forEach(p => {
    const ubi = parseUbicacion(p.ubicacion_actual)
    if (!ubi || maps.value[p.id]) return
    const el = mapRefs.value[p.id]
    if (el) initMapForPedido(p, ubi)
  })
}

const initMapForPedido = async (pedido: any, ubi: { lat: number; lng: number }) => {
  await nextTick()
  // Double wait to ensure container has dimensions
  await new Promise(r => setTimeout(r, 100))
  const el = mapRefs.value[pedido.id]
  if (!el || maps.value[pedido.id]) return
  const rect = el.getBoundingClientRect()
  if (rect.width === 0 || rect.height === 0) return
  try {
    const m = L.map(el, { zoomControl: true }).setView([ubi.lat, ubi.lng], 15)
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
      maxZoom: 19,
      attribution: '&copy; <a href="https://openstreetmap.org/copyright">OSM</a>',
    }).addTo(m)
    const icon = L.divIcon({ html: '🏍️', className: '', iconSize: [32, 32], iconAnchor: [16, 16] })
    const marker = L.marker([ubi.lat, ubi.lng], { icon }).addTo(m)
    maps.value[pedido.id] = m
    markers.value[pedido.id] = marker
    setTimeout(() => m.invalidateSize(), 100)
    setTimeout(() => m.invalidateSize(), 500)
  } catch (e) { console.error('Error init map for pedido', pedido.id, e) }
}

const updateMarkerForPedido = (pedido: any) => {
  const ubi = parseUbicacion(pedido.ubicacion_actual)
  const marker = markers.value[pedido.id]
  const m = maps.value[pedido.id]
  if (!marker || !m || !ubi) return
  try {
    marker.setLatLng([ubi.lat, ubi.lng])
    m.panTo([ubi.lat, ubi.lng], { animate: true, duration: 0.5 })
  } catch (e) { console.error('Error update marker', e) }
}

const destroyAllMaps = () => {
  Object.values(maps.value).forEach(m => { try { m.remove() } catch {} })
  maps.value = {}
  markers.value = {}
}

// Watch: update markers when ubicacion changes in existing maps
watch(() => todosPedidos.value, (nuevos) => {
  nuevos.forEach(p => {
    if (p.estado === 'en_camino' && maps.value[p.id]) {
      updateMarkerForPedido(p)
    }
  })
}, { deep: true })

const tiempoTranscurrido = (created: string) => {
  const diff = Date.now() - new Date(created).getTime()
  const mins = Math.floor(diff / 60000)
  if (mins < 60) return `${mins} min`
  const hrs = Math.floor(mins / 60)
  return `${hrs}h ${mins % 60}min`
}

const tiempoEstimado = (created: string) => {
  const entrega = new Date(new Date(created).getTime() + 30 * 60000)
  return entrega.toLocaleTimeString('es-BO', { hour: '2-digit', minute: '2-digit' })
}

onMounted(() => {
  fetchPedidos()
  interval = setInterval(fetchPedidos, 30000)
})

onUnmounted(() => {
  if (interval) clearInterval(interval)
  destroyAllMaps()
})
</script>

<template>
  <div class="max-w-3xl mx-auto">
    <h1 class="text-2xl font-bold text-ink mb-6">Mis Pedidos</h1>

    <div v-if="!auth.token" class="text-center py-12">
      <p class="text-ink-muted mb-4">Inicia sesión para ver tus pedidos.</p>
      <RouterLink to="/login" class="bg-rojo-mercado-dark text-white px-4 py-2 rounded text-sm no-underline hover:bg-rojo-mercado-darker">Iniciar Sesión</RouterLink>
    </div>

    <div v-else-if="error" class="text-center py-12">
      <p class="text-rojo-mercado-dark mb-3">{{ error }}</p>
      <button @click="fetchPedidos" class="bg-red-700 text-white px-4 py-2 rounded-lg cursor-pointer border-none hover:bg-red-800">Reintentar</button>
    </div>

    <div v-else-if="cargando" class="py-8 space-y-4">
      <div class="skeleton h-6 w-48 mx-auto"></div>
      <div class="skeleton h-32 w-full max-w-xl mx-auto"></div>
    </div>

    <div v-else-if="todosPedidos.length === 0" class="text-center py-12 text-ink-dim">
      No tienes pedidos aún. <RouterLink to="/" class="text-rojo-mercado-dark hover:underline">Ir a la tienda</RouterLink>
    </div>

    <div v-else class="space-y-8">
      <!-- En proceso -->
      <div>
        <h3 class="text-lg font-semibold text-ink mb-3 flex items-center gap-2">
          <span class="w-2.5 h-2.5 rounded-full bg-success inline-block"></span>
          En Proceso ({{ enProceso.length }})
        </h3>

        <div v-if="enProceso.length === 0" class="text-sm text-ink-dim pl-5">Sin pedidos activos.</div>

        <div v-else class="space-y-4">
          <div v-for="p in enProceso" :key="p.id" class="bg-white rounded-lg shadow border overflow-hidden">

            <div class="flex items-center justify-between px-5 py-3 border-b" :class="estadoHeaderBg(p.estado)">
              <div class="flex items-center gap-3">
                <span class="text-lg font-bold text-ink">{{ p.codigo || '#' + p.id }}</span>
                <span class="text-xs font-semibold px-2 py-0.5 rounded-full" :class="estadoBadge(p.estado)">{{ labels[p.estado] || p.estado }}</span>
              </div>
              <div class="flex items-center gap-2">
                <span class="text-xs text-ink-muted">{{ tiempoTranscurrido(p.created_at) }}</span>
                <button v-if="puedeCancelar(p)" @click="cancelarPedido(p)"
                  class="text-xs text-rojo-mercado hover:text-red-800 hover:underline cursor-pointer bg-transparent border-none">Cancelar</button>
              </div>
            </div>

            <div class="p-5">
              <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between mb-5 gap-2 lg:gap-0">
                <div v-for="(paso, i) in pasos" :key="paso" class="flex items-center lg:flex-1 gap-3 lg:gap-0">
                  <div class="flex items-center gap-2">
                    <div class="w-8 h-8 shrink-0 rounded-full flex items-center justify-center text-sm font-bold border-2 transition-colors"
                      :class="pasoActual(p.estado) >= i ? 'bg-success border-green-500 text-white' : 'bg-white border-platform-edge text-ink-dim'">
                      {{ pasoActual(p.estado) >= i ? '✔' : i + 1 }}
                    </div>
                    <span class="text-sm lg:text-xs lg:mt-1 lg:text-center" :class="pasoActual(p.estado) >= i ? 'text-success-dark font-medium' : 'text-ink-dim'">{{ labels[paso] }}</span>
                  </div>
                  <div v-if="i < pasos.length - 1" class="hidden lg:block flex-1 h-0.5 mx-1"
                    :class="pasoActual(p.estado) > i ? 'bg-success' : 'bg-gray-200'"></div>
                </div>
              </div>

              <!-- Mapa para pedidos en_camino -->
              <div v-if="p.estado === 'en_camino' && p.ubicacion_actual && (typeof p.ubicacion_actual === 'object' || typeof p.ubicacion_actual === 'string')" class="mb-4">
                <p class="text-sm font-medium text-ink-muted mb-2">🏍️ Ubicación del repartidor</p>
                <div :ref="(el: any) => { if (el) mapRefs[p.id] = el as HTMLDivElement }" class="w-full h-56 rounded-lg border border-platform-edge overflow-hidden bg-gray-100" />
              </div>

              <div class="grid grid-cols-2 gap-3 text-sm text-ink-muted">
                <p><span class="font-medium text-ink">Productos:</span> {{ p.items?.length }} tipo(s)</p>
                <p><span class="font-medium text-ink">Total:</span> Bs. {{ p.total_final }}</p>
                <p class="col-span-2"><span class="font-medium text-ink">Dirección:</span> {{ p.direccion_texto || '—' }}</p>
                <p v-if="p.estado !== 'entregado' && p.estado !== 'cancelado'">
                  <span class="font-medium text-ink">Est. entrega:</span> ≈ {{ tiempoEstimado(p.created_at) }}
                </p>
              </div>

              <details class="mt-3">
                <summary class="text-sm text-ink-muted hover:text-ink cursor-pointer">Ver productos</summary>
                <div class="mt-2 space-y-2">
                  <div v-for="item in p.items" :key="item.id" class="flex items-center gap-3 bg-potos-stone rounded p-2">
                    <img :src="item.producto?.imagen_url" :alt="item.producto?.nombre || 'Producto'" loading="lazy" class="w-10 h-10 object-contain" />
                    <div class="flex-1 text-sm">
                      <span class="font-medium">{{ item.producto?.nombre }}</span>
                      <span class="text-ink-muted ml-2">{{ item.cantidad }}x Bs. {{ item.precio_unitario }}</span>
                    </div>
                  </div>
                </div>
              </details>
            </div>
          </div>
        </div>
      </div>

      <!-- Historial -->
      <div>
        <h3 class="text-lg font-semibold text-ink mb-3 flex items-center gap-2">
          <span class="w-2.5 h-2.5 rounded-full bg-gray-400 inline-block"></span>
          Historial ({{ historial.length }})
        </h3>

        <div v-if="historial.length === 0" class="text-sm text-ink-dim pl-5">Sin pedidos anteriores.</div>

        <div v-else class="space-y-2">
          <div v-for="p in historial" :key="p.id"
            class="bg-white rounded-lg border border-platform-edge px-5 py-3 flex items-center justify-between hover:bg-potos-stone transition-colors">
            <div class="flex items-center gap-3">
              <span class="font-semibold text-ink">{{ p.codigo || '#' + p.id }}</span>
              <span class="text-xs font-medium px-2 py-0.5 rounded-full" :class="estadoBadge(p.estado)">{{ labels[p.estado] }}</span>
            </div>
            <div class="text-sm text-ink-muted">
              {{ p.items?.length }} producto(s) — Bs. {{ p.total_final }}
              <span class="ml-2 text-xs">{{ new Date(p.created_at).toLocaleDateString('es-BO') }}</span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <ConfirmDialog :open="!!confirmData" title="Cancelar pedido" :message="`¿Cancelar ${confirmData?.pedido.codigo || '#' + confirmData?.pedido.id}?`" variant="danger" @confirm="confirmarCancelacion" @cancel="confirmData = null" />
</template>