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
import { fetchRoute, haversineDistance } from '../composables/useOsrm'

delete (L.Icon.Default.prototype as any)._getIconUrl
L.Icon.Default.mergeOptions({
  iconRetinaUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/images/marker-icon-2x.png',
  iconUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/images/marker-icon.png',
  shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/images/marker-shadow.png',
})

const auth = useAuthStore()
const delivery = useDeliveryStore()
const toast = useToastStore()
const turnoActivo = ref(false)
const cargandoTurno = ref(false)
const { lat, lng, isTracking } = storeToRefs(delivery)
const tab = ref<'disponibles' | 'mis_entregas'>('disponibles')
const disponibles = ref<any[]>([])
const misEntregas = ref<any[]>([])
const pedidoActivo = ref<any>(null)
const cargando = ref(true)
const error = ref('')
let fetchAbort: AbortController | null = null

// Map for active delivery
const mapContainer = ref<HTMLDivElement | null>(null)
const mapError = ref('')
let map: L.Map | null = null
let marker: L.Marker | null = null
let activeRoutePoly: L.Polyline | null = null
let clientMarker: L.Marker | null = null
let routeRefreshInterval: ReturnType<typeof setInterval> | null = null

// Map for disponible previews
const mapDisponibles = ref<HTMLDivElement | null>(null)
let mapDisp: L.Map | null = null
let dispMarkersLayer: L.LayerGroup | null = null
let dispRoutePoly: L.Polyline | null = null
let dispRepartidorMarker: L.Marker | null = null

// Route info
const routeInfo = ref<{ distance: number; duration: number } | null>(null)
const calculandoRuta = ref(false)
let lastRouteLat = lat.value
let lastRouteLng = lng.value

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

function obtenerLatCliente(p: any): number | null {
  const v = p.user?.latitud ?? p.latitud
  return v != null ? Number(v) : null
}

function obtenerLngCliente(p: any): number | null {
  const v = p.user?.longitud ?? p.longitud
  return v != null ? Number(v) : null
}

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
    await nextTick()
    setTimeout(() => initMapDisponibles(), 200)
  } catch (e: any) { if (e.name !== 'AbortError') error.value = e.message || 'Error al cargar pedidos' } finally { cargando.value = false }
}

// ─── Mapa de Disponibles ───

function initMapDisponibles() {
  if (pedidoActivo.value || !mapDisponibles.value || mapDisp) return
  const rect = mapDisponibles.value.getBoundingClientRect()
  if (rect.width === 0 || rect.height === 0) return
  try {
    mapDisp = L.map(mapDisponibles.value, { zoomControl: true }).setView([lat.value, lng.value], 14)
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
      maxZoom: 19,
      attribution: '&copy; <a href="https://openstreetmap.org/copyright">OSM</a>',
    }).addTo(mapDisp)
    const repIcon = L.divIcon({ html: '🏍️', className: '', iconSize: [32, 32], iconAnchor: [16, 16] })
    dispRepartidorMarker = L.marker([lat.value, lng.value], { icon: repIcon }).addTo(mapDisp)
    dispMarkersLayer = L.layerGroup().addTo(mapDisp)
    actualizarMarcadoresDisponibles()
    setTimeout(() => mapDisp?.invalidateSize(), 100)
    setTimeout(() => mapDisp?.invalidateSize(), 500)
  } catch (e) { console.error('Error init disponibles map:', e) }
}

function actualizarMarcadoresDisponibles() {
  if (!dispMarkersLayer || !mapDisp) return
  dispMarkersLayer.clearLayers()
  const clientIcon = L.divIcon({ html: '📍', className: '', iconSize: [24, 24], iconAnchor: [12, 24] })

  disponibles.value.forEach(p => {
    const clat = obtenerLatCliente(p)
    const clng = obtenerLngCliente(p)
    if (!clat || !clng) return
    const dist = haversineDistance(lat.value, lng.value, clat, clng)
    const m = L.marker([clat, clng], { icon: clientIcon })
    m.bindPopup(`
      <div style="min-width:200px;font-family:sans-serif;font-size:13px">
        <b>${p.codigo || '#' + p.id}</b><br/>
        <span>${p.user?.name || 'Cliente'}</span><br/>
        <span style="color:#666">📏 ${dist.toFixed(1)} km</span><br/>
        <span style="color:#666">📍 ${p.direccion_texto?.slice(0, 60) || ''}</span><br/>
        <button onclick="window.dispatchEvent(new CustomEvent('preview-route', {detail:{id:${p.id}}}))"
          style="margin-top:6px;padding:4px 12px;background:#2563eb;color:white;border:none;border-radius:4px;cursor:pointer;font-size:12px">
          Ver Ruta
        </button>
      </div>
    `)
    m.on('click', () => {
      previewRoute(p, clat, clng)
    })
    dispMarkersLayer!.addLayer(m)
  })
}

async function previewRoute(pedido: any, clat: number, clng: number) {
  if (!mapDisp) return
  calculandoRuta.value = true
  if (dispRoutePoly) { mapDisp.removeLayer(dispRoutePoly); dispRoutePoly = null }
  try {
    const abort = new AbortController()
    const route = await fetchRoute([lat.value, lng.value], [clat, clng], abort.signal)
    if (route && mapDisp) {
      const latlngs = route.geometry.coordinates.map((c: number[]) => [c[1], c[0]] as [number, number])
      dispRoutePoly = L.polyline(latlngs, { color: '#2563eb', weight: 4, opacity: 0.8 }).addTo(mapDisp)
      mapDisp.fitBounds(dispRoutePoly.getBounds().pad(0.15))
      routeInfo.value = { distance: route.distance, duration: route.duration }
    }
  } catch {}
  calculandoRuta.value = false
}

function cerrarRuta() {
  if (dispRoutePoly && mapDisp) {
    mapDisp.removeLayer(dispRoutePoly)
    dispRoutePoly = null
    routeInfo.value = null
  }
}

let previewAbort: AbortController | null = null

function onPreviewRoute(e: Event) {
  const ce = e as CustomEvent
  const p = disponibles.value.find((x: any) => x.id === ce.detail.id)
  if (p) {
    const clat = obtenerLatCliente(p)
    const clng = obtenerLngCliente(p)
    if (clat && clng) previewRoute(p, clat, clng)
  }
}

// Listen for custom event from popup
if (typeof window !== 'undefined') {
  window.addEventListener('preview-route', onPreviewRoute)
}

watch([lat, lng], () => {
  if (dispRepartidorMarker && mapDisp) {
    dispRepartidorMarker.setLatLng([lat.value, lng.value])
  }
})

// ─── Mapa de Entrega Activa ───

const iniciarMapa = async () => {
  mapError.value = ''
  await nextTick()
  await new Promise(r => setTimeout(r, 100))
  if (!mapContainer.value) { mapError.value = 'Contenedor del mapa no disponible'; return }
  const rect = mapContainer.value.getBoundingClientRect()
  if (rect.width === 0 || rect.height === 0) { mapError.value = 'El contenedor del mapa no tiene dimensiones'; return }
  if (map) { map.remove(); map = null; marker = null; clientMarker = null; activeRoutePoly = null }
  try {
    const clat = pedidoActivo.value ? obtenerLatCliente(pedidoActivo.value) : null
    const clng = pedidoActivo.value ? obtenerLngCliente(pedidoActivo.value) : null
    const bounds: [number, number][] = [[lat.value, lng.value]]
    if (clat && clng) bounds.push([clat, clng])

    map = L.map(mapContainer.value, { zoomControl: true }).setView([lat.value, lng.value], 15)
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
      maxZoom: 19,
      attribution: '&copy; <a href="https://openstreetmap.org/copyright">OSM</a>',
    }).addTo(map)

    const repIcon = L.divIcon({ html: '🏍️', className: '', iconSize: [32, 32], iconAnchor: [16, 16] })
    marker = L.marker([lat.value, lng.value], { icon: repIcon }).addTo(map)

    if (clat && clng) {
      const homeIcon = L.divIcon({ html: '🏠', className: '', iconSize: [28, 28], iconAnchor: [14, 14] })
      clientMarker = L.marker([clat, clng], { icon: homeIcon }).addTo(map)
      map.fitBounds(bounds, { padding: [50, 50] })
      await dibujarRutaActiva([lat.value, lng.value], [clat, clng])
    }

    setTimeout(() => map?.invalidateSize(), 100)
    setTimeout(() => map?.invalidateSize(), 500)
  } catch (e: any) {
    mapError.value = 'Error al crear mapa: ' + e.message
    console.error('Map init error:', e)
  }
}

async function dibujarRutaActiva(origen: [number, number], destino: [number, number]) {
  if (activeRoutePoly && map) { map.removeLayer(activeRoutePoly); activeRoutePoly = null }
  try {
    const abort = new AbortController()
    const route = await fetchRoute(origen, destino, abort.signal)
    if (route && map) {
      const latlngs = route.geometry.coordinates.map((c: number[]) => [c[1], c[0]] as [number, number])
      activeRoutePoly = L.polyline(latlngs, { color: '#16a34a', weight: 4, opacity: 0.85 }).addTo(map)
      routeInfo.value = { distance: route.distance, duration: route.duration }
    }
  } catch {}
}

const actualizarMarcador = () => {
  if (!marker || !map) return
  marker.setLatLng([lat.value, lng.value])
  if (pedidoActivo.value) {
    const clat = obtenerLatCliente(pedidoActivo.value)
    const clng = obtenerLngCliente(pedidoActivo.value)
    if (clat && clng) {
      const moved = haversineDistance(lastRouteLat, lastRouteLng, lat.value, lng.value)
      if (moved > 0.1) {
        lastRouteLat = lat.value
        lastRouteLng = lng.value
        dibujarRutaActiva([lat.value, lng.value], [clat, clng])
      }
    }
  }
}

const destruirMapa = () => {
  try { if (map) { map.remove(); map = null; marker = null; clientMarker = null; activeRoutePoly = null; routeInfo.value = null } } catch {}
}

const destruirMapaDisponibles = () => {
  try { if (mapDisp) { mapDisp.remove(); mapDisp = null; dispMarkersLayer = null; dispRoutePoly = null; dispRepartidorMarker = null; routeInfo.value = null } } catch {}
}

watch([lat, lng], () => {
  actualizarMarcador()
  if (dispRepartidorMarker && mapDisp) {
    dispRepartidorMarker.setLatLng([lat.value, lng.value])
  }
})

// ─── Acciones ───

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
    // Set destination for simulated movement
    const clat = obtenerLatCliente(pedido)
    const clng = obtenerLngCliente(pedido)
    if (clat && clng) delivery.setDestino(clat, clng)
    delivery.startTracking(pedido.id)
    await nextTick()
    destruirMapaDisponibles()
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
  const clat = obtenerLatCliente(p)
  const clng = obtenerLngCliente(p)
  if (clat && clng) delivery.setDestino(clat, clng)
  delivery.startTracking(p.id)
  await nextTick()
  destruirMapaDisponibles()
  await iniciarMapa()
}

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

const checkTurno = async () => {
  try {
    const shift = await apiFetch<any>('/shifts/actual')
    turnoActivo.value = !!shift?.id
  } catch { turnoActivo.value = false }
}

const toggleTurno = async () => {
  cargandoTurno.value = true
  try {
    if (turnoActivo.value) {
      await apiFetch('/shifts/finalizar', { method: 'POST' })
      toast.add('Turno finalizado', 'success')
      turnoActivo.value = false
    } else {
      await apiFetch('/shifts/iniciar', { method: 'POST' })
      toast.add('Turno iniciado', 'success')
      turnoActivo.value = true
    }
  } catch (e: any) { toast.add('Error: ' + e.message, 'error') }
  finally { cargandoTurno.value = false }
}

onMounted(() => {
  checkTurno()
  fetchPedidos()
  interval = setInterval(fetchPedidos, 15000)
})

let interval: ReturnType<typeof setInterval> | null = null

onUnmounted(() => {
  if (interval) clearInterval(interval)
  delivery.stopTracking()
  destruirMapa()
  destruirMapaDisponibles()
  if (typeof window !== 'undefined') {
    window.removeEventListener('preview-route', onPreviewRoute)
  }
})
</script>

<template>
  <div>
    <div class="flex items-center justify-between mb-4">
      <h1 class="text-2xl font-bold text-ink">Repartidor — Gestión de Entregas</h1>
      <button @click="toggleTurno" :disabled="cargandoTurno"
        class="px-4 py-2 rounded-lg text-sm font-semibold cursor-pointer border-none flex items-center gap-2"
        :class="turnoActivo ? 'bg-green-600 text-white hover:bg-green-700' : 'bg-gray-300 text-gray-700 hover:bg-gray-400'">
        <span class="w-2.5 h-2.5 rounded-full" :class="turnoActivo ? 'bg-green-200 animate-pulse' : 'bg-gray-500'"></span>
        {{ cargandoTurno ? '...' : turnoActivo ? 'Finalizar Turno' : 'Iniciar Turno' }}
      </button>
    </div>

    <!-- Tabs (only when no active delivery) -->
    <div v-if="!pedidoActivo" class="flex gap-2 mb-4 flex-wrap">
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

    <div v-if="error && !pedidoActivo" class="text-center py-12">
      <p class="text-rojo-mercado-dark mb-3">{{ error }}</p>
      <button @click="fetchPedidos" class="bg-red-700 text-white px-4 py-2 rounded-lg cursor-pointer border-none hover:bg-red-800">Reintentar</button>
    </div>

    <div v-if="cargando && !pedidoActivo" class="py-8 space-y-4">
      <div class="skeleton h-6 w-48 mx-auto"></div>
      <div class="skeleton h-64 w-full"></div>
    </div>

    <!-- ─── TAB: DISPONIBLES ─── -->
    <div v-else-if="!pedidoActivo && tab === 'disponibles'">
      <!-- Mapa de disponibles -->
      <div ref="mapDisponibles" class="w-full h-72 rounded-lg border-2 border-platform-edge mb-4 overflow-hidden bg-gray-100" />

      <!-- Route info when previewing -->
      <div v-if="routeInfo && !pedidoActivo" class="bg-blue-50 border border-blue-200 rounded-lg px-4 py-2 mb-3 text-sm flex items-center gap-4">
        <span class="text-blue-700">📏 <strong>{{ (routeInfo.distance ?? 0).toFixed(1) }} km</strong></span>
        <span class="text-blue-700">⏱ ~{{ Math.round(routeInfo.duration ?? 0) }} min</span>
        <button @click="cerrarRuta"
          class="ml-auto text-xs text-blue-600 hover:underline cursor-pointer bg-transparent border-none">Cerrar ruta</button>
      </div>

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

    <!-- ─── TAB: MIS ENTREGAS ─── -->
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

    <!-- ─── ENTREGA ACTIVA ─── -->
    <div v-else-if="pedidoActivo">
      <button @click="() => { delivery.stopTracking(); destruirMapa(); pedidoActivo = null; testPedidoId = null; fetchPedidos() }"
        class="text-sm text-ink-muted hover:text-ink mb-3 cursor-pointer bg-transparent border-none">⬅ Volver a disponibles</button>

      <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-4">
        <h3 class="text-xl font-bold text-blue-800">🏍️ En Camino — <span class="text-2xl">{{ pedidoActivo.codigo || '#' + pedidoActivo.id }}</span></h3>
        <p class="text-ink-muted mt-1"><span class="font-semibold">Cliente:</span> {{ pedidoActivo.user?.name }}</p>
        <p class="text-ink-muted"><span class="font-semibold">Dirección:</span> {{ pedidoActivo.direccion_texto }}</p>
      </div>

      <!-- Route info -->
      <div v-if="routeInfo" class="bg-green-50 border border-green-200 rounded-lg px-4 py-3 mb-3 flex flex-wrap items-center gap-4 text-sm">
        <span class="text-green-700 font-medium">🚚 En ruta</span>
        <span class="text-green-700">📏 <strong>{{ (routeInfo.distance ?? 0).toFixed(1) }} km</strong></span>
        <span class="text-green-700">⏱ ~{{ Math.round(routeInfo.duration ?? 0) }} min restantes</span>
        <span class="text-green-700">🕐 Llegada aprox. ≈ {{ new Date(Date.now() + routeInfo.duration * 60000).toLocaleTimeString('es-BO', { hour: '2-digit', minute: '2-digit' }) }}</span>
      </div>

      <div v-if="mapError" class="bg-red-100 text-red-700 p-3 rounded mb-3 text-sm">{{ mapError }}</div>

      <div ref="mapContainer" class="w-full h-96 rounded-lg border-2 border-platform-edge mb-4 overflow-hidden bg-gray-100" />
      <p class="text-xs text-ink-muted mb-4 text-center">
        🏍️ {{ (lat ?? 0).toFixed(4) }}, {{ (lng ?? 0).toFixed(4) }}
        <span v-if="pedidoActivo && obtenerLatCliente(pedidoActivo) != null">— 🏠 {{ obtenerLatCliente(pedidoActivo)?.toFixed(4) }}, {{ obtenerLngCliente(pedidoActivo)?.toFixed(4) }}</span>
      </p>

      <!-- Test panel -->
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
          <div class="flex gap-2 flex-wrap">
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
