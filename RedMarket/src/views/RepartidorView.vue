<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted, watch, nextTick } from 'vue'
import { apiFetch } from '../composables/useApi'
import { useAuthStore } from '../stores/auth'
import { useDeliveryStore } from '../stores/deliveryStore'
import { useToastStore } from '../stores/toast'
import { storeToRefs } from 'pinia'
import 'leaflet/dist/leaflet.css'
import L from 'leaflet'
import { estadoBadge } from '../utils/estados'

// Fix Leaflet default icon paths for Vite
delete (L.Icon.Default.prototype as any)._getIconUrl
L.Icon.Default.mergeOptions({
  iconRetinaUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/images/marker-icon-2x.png',
  iconUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/images/marker-icon.png',
  shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/images/marker-shadow.png',
})

const auth = useAuthStore()
const delivery = useDeliveryStore()
const toast = useToastStore()
const { lat, lng, isTracking } = storeToRefs(delivery)
const tab = ref<'disponibles' | 'mis_entregas'>('disponibles')
const disponibles = ref<any[]>([])
const misEntregas = ref<any[]>([])
const pedidoActivo = ref<any>(null)
const cargando = ref(true)
const error = ref('')
const mapContainer = ref<HTMLDivElement | null>(null)
const mapError = ref('')
let map: L.Map | null = null
let marker: L.Marker | null = null
let interval: ReturnType<typeof setInterval> | null = null
let fetchAbort: AbortController | null = null

// Test panel
const testLat = ref(-19.5836)
const testLng = ref(-65.7531)
const testPedidoId = ref<number | null>(null)
const testLog = ref<string[]>([])

const misActivas = computed(() =>
  misEntregas.value.filter((p: any) => p.estado === 'en_camino')
)

const misHistorial = computed(() =>
  misEntregas.value.filter((p: any) => !['en_camino', 'listo_despacho'].includes(p.estado))
)

const fetchPedidos = async () => {
  if (fetchAbort) fetchAbort.abort()
  fetchAbort = new AbortController()
  cargando.value = true
  try {
    const data = await apiFetch('/pedidos', { signal: fetchAbort.signal } as any)
    if (data.disponibles !== undefined) {
      disponibles.value = data.disponibles ?? []
      misEntregas.value = data.mis_pedidos ?? []
    } else if (data.data && Array.isArray(data.data)) {
      disponibles.value = data.data.filter((p: any) => p.estado === 'listo_despacho')
      misEntregas.value = data.data.filter((p: any) => p.repartidor_id === auth.user?.id)
    }
  } catch (e: any) { if (e.name !== 'AbortError') error.value = e.message || 'Error al cargar pedidos' } finally { cargando.value = false }
}

const iniciarMapa = async () => {
  mapError.value = ''
  await nextTick()
  // Wait for DOM to fully render
  await new Promise(r => setTimeout(r, 100))
  if (!mapContainer.value) { mapError.value = 'Contenedor del mapa no disponible'; return }
  const rect = mapContainer.value.getBoundingClientRect()
  if (rect.width === 0 || rect.height === 0) { mapError.value = 'El contenedor del mapa no tiene dimensiones'; return }
  if (map) { map.remove(); map = null; marker = null }
  try {
    map = L.map(mapContainer.value, { zoomControl: true }).setView([lat.value, lng.value], 15)
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
      maxZoom: 19,
      attribution: '&copy; <a href="https://openstreetmap.org/copyright">OSM</a>',
    }).addTo(map)
    const icon = L.divIcon({ html: '🏍️', className: '', iconSize: [32, 32], iconAnchor: [16, 16] })
    marker = L.marker([lat.value, lng.value], { icon }).addTo(map)
    setTimeout(() => map?.invalidateSize(), 100)
    setTimeout(() => map?.invalidateSize(), 500)
  } catch (e: any) {
    mapError.value = 'Error al crear mapa: ' + e.message
    console.error('Map init error:', e)
  }
}

const actualizarMarcador = () => {
  if (!marker || !map) return
  marker.setLatLng([lat.value, lng.value])
  map.panTo([lat.value, lng.value], { animate: true, duration: 0.5 })
}

const destruirMapa = () => {
  try { if (map) { map.remove(); map = null; marker = null } } catch {}
}

watch([lat, lng], actualizarMarcador)

const iniciarEntrega = async (pedido: any) => {
  try {
    const data = await apiFetch(`/pedidos/${pedido.id}`, {
      method: 'PUT',
      body: JSON.stringify({ estado: 'en_camino' }),
    })
    pedidoActivo.value = data
    testLat.value = lat.value
    testLng.value = lng.value
    testPedidoId.value = pedido.id
    delivery.startTracking(pedido.id)
    await iniciarMapa()
  } catch (e: any) { toast.add('Error: ' + e.message, 'error') }
}

const marcarEntregado = async () => {
  try {
    const data = await apiFetch(`/pedidos/${pedidoActivo.value.id}`, {
      method: 'PUT',
      body: JSON.stringify({ estado: 'entregado' }),
    })
    toast.add(`${data.codigo || '#' + data.id} entregado`, 'success')
    delivery.stopTracking()
    destruirMapa()
    pedidoActivo.value = null
    testPedidoId.value = null
    fetchPedidos()
  } catch (e: any) { toast.add('Error: ' + e.message, 'error') }
}

const continuarEntrega = async (p: any) => {
  pedidoActivo.value = p
  const ubi = p.ubicacion_actual ? (typeof p.ubicacion_actual === 'string' ? JSON.parse(p.ubicacion_actual) : p.ubicacion_actual) : null
  testLat.value = ubi?.lat || lat.value
  testLng.value = ubi?.lng || lng.value
  testPedidoId.value = p.id
  delivery.startTracking(p.id)
  await iniciarMapa()
}

// Test panel: enviar ubicacion manual
const enviarUbicacion = async () => {
  if (!testPedidoId.value) return
  lat.value = testLat.value
  lng.value = testLng.value
  try {
    await apiFetch(`/pedidos/${testPedidoId.value}/ubicacion`, {
      method: 'PUT',
      body: JSON.stringify({ lat: testLat.value, lng: testLng.value }),
    })
    testLog.value.unshift(`📍 Enviado: ${testLat.value.toFixed(4)}, ${testLng.value.toFixed(4)}`)
  } catch (e: any) {
    testLog.value.unshift(`❌ Error: ${e.message}`)
  }
}

onMounted(() => {
  fetchPedidos()
  interval = setInterval(fetchPedidos, 30000)
})

onUnmounted(() => {
  if (interval) clearInterval(interval)
  delivery.stopTracking()
  destruirMapa()
})
</script>

<template>
  <div>
    <h1 class="text-2xl font-bold text-ink mb-4">Repartidor — Gestión de Entregas</h1>

    <!-- Tabs -->
    <div v-if="!pedidoActivo" class="flex gap-2 mb-6 flex-wrap">
      <button @click="tab = 'disponibles'"
        class="px-4 py-2 rounded text-sm font-semibold cursor-pointer border-none"
        :class="tab === 'disponibles' ? 'bg-blue-600 text-white' : 'bg-gray-200 text-ink hover:bg-gray-300'">
        🚚 Disponibles ({{ disponibles.length }})
      </button>
      <button @click="tab = 'mis_entregas'"
        class="px-4 py-2 rounded text-sm font-semibold cursor-pointer border-none"
        :class="tab === 'mis_entregas' ? 'bg-blue-600 text-white' : 'bg-gray-200 text-ink hover:bg-gray-300'">
        📋 Mis Entregas ({{ misEntregas.length }})
      </button>
    </div>

    <div v-else-if="error && !pedidoActivo" class="text-center py-12">
      <p class="text-rojo-mercado-dark mb-3">{{ error }}</p>
      <button @click="fetchPedidos" class="bg-red-700 text-white px-4 py-2 rounded-lg cursor-pointer border-none hover:bg-red-800">Reintentar</button>
    </div>

    <div v-if="cargando && !pedidoActivo" class="py-8 space-y-4">
      <div class="skeleton h-6 w-48 mx-auto"></div>
      <div class="grid grid-cols-3 gap-4">
        <div class="skeleton h-36"></div>
        <div class="skeleton h-36"></div>
        <div class="skeleton h-36"></div>
      </div>
    </div>

    <!-- DISPONIBLES -->
    <div v-else-if="!pedidoActivo && tab === 'disponibles'">
      <div v-if="disponibles.length === 0" class="text-center py-8 text-ink-muted">No hay pedidos listos para entrega.</div>
      <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        <div v-for="p in disponibles" :key="p.id" class="bg-green-50 rounded-lg border border-green-200 p-4 hover:shadow-sm transition-shadow">
          <div class="flex justify-between items-center mb-2 border-b border-green-200 pb-2">
            <h4 class="font-bold text-lg text-green-700">{{ p.codigo || '#' + p.id }}</h4>
            <span class="bg-green-500 text-white text-xs px-2 py-0.5 rounded-full font-semibold">LISTO</span>
          </div>
          <p class="text-sm text-ink-muted"><span class="font-semibold">Cliente:</span> {{ p.user?.name || p.user_id }}</p>
          <p class="text-sm text-ink-muted"><span class="font-semibold">Dirección:</span> {{ p.direccion_texto }}</p>
          <p class="text-sm text-ink-muted"><span class="font-semibold">Total:</span> Bs. {{ p.total_final }}</p>
          <div class="flex gap-2 mt-3">
            <button @click="iniciarEntrega(p)" class="flex-1 bg-blue-600 text-white py-2 rounded text-sm font-semibold hover:bg-blue-700 cursor-pointer border-none">Iniciar Entrega</button>
          </div>
        </div>
      </div>
    </div>

    <!-- MIS ENTREGAS -->
    <div v-else-if="!pedidoActivo && tab === 'mis_entregas'">
      <div v-if="misEntregas.length === 0" class="text-center py-8 text-ink-muted">Aún no has realizado ninguna entrega.</div>

      <div v-if="misActivas.length" class="mb-8">
        <h3 class="text-md font-semibold text-ink mb-3 flex items-center gap-2">
          <span class="w-2.5 h-2.5 rounded-full bg-blue-500 inline-block"></span>
          En camino ({{ misActivas.length }})
        </h3>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
          <div v-for="p in misActivas" :key="p.id"
            class="bg-blue-50 rounded-lg border border-blue-200 p-4 hover:shadow-sm transition-shadow">
            <div class="flex justify-between items-center mb-2 border-b border-blue-200 pb-2">
              <h4 class="font-bold text-lg text-blue-700">{{ p.codigo || '#' + p.id }}</h4>
              <span class="text-xs px-2 py-0.5 rounded-full font-medium" :class="estadoBadge(p.estado)">{{ p.estado.replace(/_/g, ' ') }}</span>
            </div>
            <p class="text-sm text-ink-muted"><span class="font-semibold">Cliente:</span> {{ p.user?.name }}</p>
            <p class="text-sm text-ink-muted"><span class="font-semibold">Dirección:</span> {{ p.direccion_texto }}</p>
            <button @click="continuarEntrega(p)"
              class="mt-3 w-full bg-blue-600 text-white py-2 rounded text-sm font-semibold hover:bg-blue-700 cursor-pointer border-none">
              Continuar Entrega
            </button>
          </div>
        </div>
      </div>

      <div v-if="misHistorial.length">
        <h3 class="text-md font-semibold text-ink mb-3 flex items-center gap-2">
          <span class="w-2.5 h-2.5 rounded-full bg-gray-400 inline-block"></span>
          Completadas ({{ misHistorial.length }})
        </h3>
        <div class="space-y-2">
          <div v-for="p in misHistorial" :key="p.id"
            class="bg-white rounded-lg border border-platform-edge px-5 py-3 flex items-center justify-between hover:bg-gray-50 transition-colors">
            <div class="flex items-center gap-3">
              <span class="font-semibold text-ink">{{ p.codigo || '#' + p.id }}</span>
              <span class="text-xs font-medium px-2 py-0.5 rounded-full" :class="estadoBadge(p.estado)">{{ p.estado.replace(/_/g, ' ') }}</span>
            </div>
            <div class="text-sm text-ink-muted">
              {{ p.items?.length }} producto(s) — Bs. {{ p.total_final }}
              <span class="ml-2 text-xs">{{ new Date(p.created_at).toLocaleDateString('es-BO') }}</span>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Entrega activa con mapa + panel de pruebas -->
    <div v-else-if="pedidoActivo">
      <button @click="() => { delivery.stopTracking(); destruirMapa(); pedidoActivo = null; testPedidoId = null; fetchPedidos() }"
        class="text-sm text-ink-muted hover:text-ink mb-3 cursor-pointer bg-transparent border-none">⬅ Volver a disponibles</button>

      <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-4">
        <h3 class="text-xl font-bold text-blue-800">🏍️ En Camino — <span class="text-2xl">{{ pedidoActivo.codigo || '#' + pedidoActivo.id }}</span></h3>
        <p class="text-ink-muted mt-1"><span class="font-semibold">Cliente:</span> {{ pedidoActivo.user?.name }}</p>
        <p class="text-ink-muted"><span class="font-semibold">Dirección:</span> {{ pedidoActivo.direccion_texto }}</p>
      </div>

      <div v-if="mapError" class="bg-red-100 text-red-700 p-3 rounded mb-3 text-sm">{{ mapError }}</div>

      <div ref="mapContainer" class="w-full h-96 rounded-lg border-2 border-platform-edge mb-4 overflow-hidden bg-gray-100" />
      <p class="text-xs text-ink-muted mb-4 text-center">Lat: {{ lat.toFixed(4) }} / Lng: {{ lng.toFixed(4) }}</p>

      <!-- Panel de pruebas -->
      <details class="bg-yellow-50 border border-yellow-300 rounded-lg p-4 mb-4">
        <summary class="font-semibold text-yellow-800 cursor-pointer text-sm">🧪 Panel de Pruebas — Enviar ubicación manual</summary>
        <div class="mt-3 space-y-3">
          <div class="flex items-center gap-3">
            <label class="text-sm font-medium text-ink-muted w-16">Lat:</label>
            <input v-model.number="testLat" type="number" step="0.0001"
              class="flex-1 border border-platform-edge rounded px-2 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-yellow-400" />
            <label class="text-sm font-medium text-ink-muted w-16">Lng:</label>
            <input v-model.number="testLng" type="number" step="0.0001"
              class="flex-1 border border-platform-edge rounded px-2 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-yellow-400" />
          </div>
          <div class="flex gap-2">
            <button @click="enviarUbicacion"
              class="bg-yellow-600 text-white px-4 py-2 rounded text-sm font-semibold hover:bg-yellow-700 cursor-pointer border-none">
              📍 Enviar Ubicación
            </button>
            <button @click="() => { testLat = -19.5836; testLng = -65.7531 }"
              class="bg-gray-200 text-ink px-4 py-2 rounded text-sm hover:bg-gray-300 cursor-pointer border-none">
              Centro
            </button>
            <button @click="() => { testLat = -19.5790; testLng = -65.7610 }"
              class="bg-gray-200 text-ink px-4 py-2 rounded text-sm hover:bg-gray-300 cursor-pointer border-none">
              San Roque
            </button>
            <button @click="() => { testLat = -19.5750; testLng = -65.7500 }"
              class="bg-gray-200 text-ink px-4 py-2 rounded text-sm hover:bg-gray-300 cursor-pointer border-none">
              Universitaria
            </button>
          </div>
          <!-- Log -->
          <div v-if="testLog.length" class="bg-white rounded border p-2 max-h-32 overflow-y-auto">
            <div v-for="(msg, i) in testLog" :key="i" class="text-xs text-ink-muted py-0.5">{{ msg }}</div>
          </div>
        </div>
      </details>

      <div class="text-center">
        <button @click="marcarEntregado" class="bg-green-600 text-white px-8 py-3 rounded-lg font-bold text-lg hover:bg-green-700 cursor-pointer border-none">✅ Marcar como Entregado</button>
      </div>
    </div>
  </div>
</template>