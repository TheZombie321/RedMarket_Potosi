<script setup lang="ts">
import { ref, onMounted, onUnmounted, nextTick } from 'vue'
import L from 'leaflet'

const emit = defineEmits<{
  select: [value: { direccion: string; lat: number; lng: number }]
}>()

const props = defineProps<{
  initialAddress?: string
  initialLat?: number
  initialLng?: number
}>()

// Fix Leaflet icons
delete (L.Icon.Default.prototype as any)._getIconUrl
L.Icon.Default.mergeOptions({
  iconRetinaUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/images/marker-icon-2x.png',
  iconUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/images/marker-icon.png',
  shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/images/marker-shadow.png',
})

const mapContainer = ref<HTMLDivElement | null>(null)
const searchQuery = ref(props.initialAddress || '')
const resultadosBusqueda = ref<any[]>([])
const buscando = ref(false)
const reverseGeocoding = ref(false)
const mostrarResultados = ref(false)
const direccion = ref(props.initialAddress || '')
const lat = ref(props.initialLat || -19.5836)
const lng = ref(props.initialLng || -65.7531)

let map: L.Map | null = null
let marker: L.Marker | null = null
let resizeObserver: ResizeObserver | null = null
let initTimers: ReturnType<typeof setTimeout>[] = []

const clearInitTimers = () => {
  initTimers.forEach(t => clearTimeout(t))
  initTimers = []
}

const initMap = () => {
  if (!mapContainer.value || map) return

  const rect = mapContainer.value.getBoundingClientRect()
  if (rect.width === 0 || rect.height === 0) return

  map = L.map(mapContainer.value, {
    center: [lat.value, lng.value],
    zoom: 15,
    zoomControl: true,
  })

  L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '&copy; OpenStreetMap contributors',
    maxZoom: 19,
  }).addTo(map)

  marker = L.marker([lat.value, lng.value], { draggable: true }).addTo(map)
  marker.bindPopup('📍 Tu ubicación de entrega')

  marker.on('dragend', () => {
    const pos = marker!.getLatLng()
    lat.value = pos.lat
    lng.value = pos.lng
    reverseGeocode(pos.lat, pos.lng)
  })

  map.on('click', (e: L.LeafletMouseEvent) => {
    marker!.setLatLng(e.latlng)
    lat.value = e.latlng.lat
    lng.value = e.latlng.lng
    reverseGeocode(e.latlng.lat, e.latlng.lng)
  })

  initTimers.push(setTimeout(() => map?.invalidateSize(), 100))
  initTimers.push(setTimeout(() => map?.invalidateSize(), 500))
  initTimers.push(setTimeout(() => map?.invalidateSize(), 1000))
}

const waitForDimensionsAndInit = () => {
  if (!mapContainer.value) return

  const rect = mapContainer.value.getBoundingClientRect()
  if (rect.width > 0 && rect.height > 0) {
    nextTick(initMap)
    return
  }

  resizeObserver = new ResizeObserver((entries) => {
    for (const entry of entries) {
      const { width, height } = entry.contentRect
      if (width > 0 && height > 0) {
        resizeObserver?.disconnect()
        resizeObserver = null
        nextTick(initMap)
        return
      }
    }
  })
  resizeObserver.observe(mapContainer.value)
}

// Búsqueda: solo mostrar resultados si hay 3+ caracteres
let searchTimer: ReturnType<typeof setTimeout> | null = null
const onSearchInput = () => {
  mostrarResultados.value = false
  if (searchTimer) clearTimeout(searchTimer)
  const q = searchQuery.value.trim()
  if (q.length < 3) {
    resultadosBusqueda.value = []
    return
  }
  searchTimer = setTimeout(searchNominatim, 400)
}

const searchNominatim = async () => {
  const q = searchQuery.value.trim()
  if (!q || q.length < 3) return
  buscando.value = true
  mostrarResultados.value = true
  try {
    const res = await fetch(`https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(q)}&limit=5&countrycodes=bo`)
    resultadosBusqueda.value = await res.json()
  } catch {
    resultadosBusqueda.value = []
  } finally {
    buscando.value = false
  }
}

const seleccionarResultado = (r: any) => {
  mostrarResultados.value = false
  lat.value = parseFloat(r.lat)
  lng.value = parseFloat(r.lon)
  direccion.value = r.display_name
  searchQuery.value = r.display_name

  if (map && marker) {
    map.setView([lat.value, lng.value], 17)
    marker.setLatLng([lat.value, lng.value])
  }

  emitSelect()
}

// Click en mapa → auto-completar dirección
const reverseGeocode = async (latVal: number, lngVal: number) => {
  reverseGeocoding.value = true
  try {
    const res = await fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${latVal}&lon=${lngVal}&addressdetails=1`)
    const data = await res.json()
    if (data.display_name) {
      direccion.value = data.display_name
      searchQuery.value = data.display_name
    }
  } catch {
    // fallback: mantener valores actuales
  } finally {
    reverseGeocoding.value = false
  }
  emitSelect()
}

const emitSelect = () => {
  emit('select', { direccion: direccion.value, lat: lat.value, lng: lng.value })
}

// Cerrar dropdown con delay para permitir click en resultado
const onBlur = () => {
  setTimeout(() => { mostrarResultados.value = false }, 200)
}

const onFocus = () => {
  // Solo mostrar si ya hay resultados de búsqueda previa
  if (resultadosBusqueda.value.length > 0 && searchQuery.value.trim().length >= 3) {
    mostrarResultados.value = true
  }
}

onMounted(() => {
  waitForDimensionsAndInit()
})

onUnmounted(() => {
  clearInitTimers()
  resizeObserver?.disconnect()
  resizeObserver = null
  if (searchTimer) clearTimeout(searchTimer)
  map?.remove()
  map = null
})
</script>

<template>
  <div class="space-y-3 overflow-hidden">
    <!-- Search -->
    <div class="relative">
      <div class="flex items-center gap-2">
        <input v-model="searchQuery" @input="onSearchInput" @focus="onFocus" @blur="onBlur"
          placeholder="Escribe para buscar dirección…"
          class="flex-1 border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-red-300" />
        <button v-if="searchQuery" @click.prevent="searchNominatim" :disabled="buscando"
          class="bg-gray-200 hover:bg-gray-300 px-3 py-2.5 rounded-lg text-sm cursor-pointer border-none">
          <span v-if="buscando" class="w-4 h-4 border-2 border-gray-400 border-t-transparent rounded-full animate-spin inline-block"></span>
          <span v-else>🔍</span>
        </button>
      </div>

      <!-- Search results -->
      <div v-if="mostrarResultados && resultadosBusqueda.length"
        class="absolute z-[1000] w-full bg-white border border-gray-200 rounded-lg shadow-lg mt-1 max-h-48 overflow-y-auto">
        <button v-for="r in resultadosBusqueda" :key="r.osm_id || r.place_id" @mousedown.prevent="seleccionarResultado(r)"
          class="w-full text-left px-4 py-2.5 text-sm hover:bg-gray-50 border-b border-gray-100 last:border-0 cursor-pointer bg-transparent border-none">
          <span class="font-medium">{{ r.display_name?.split(',')[0] }}</span>
          <span class="text-xs text-gray-400 block truncate">{{ r.display_name }}</span>
        </button>
      </div>
    </div>

    <!-- Map -->
    <div ref="mapContainer" class="w-full h-64 rounded-lg border border-gray-300 z-0 overflow-hidden bg-gray-100"></div>

    <!-- Selected address -->
    <div v-if="reverseGeocoding" class="bg-blue-50 border border-blue-200 rounded-lg p-3 text-sm text-blue-700 flex items-center gap-2">
      <span class="w-4 h-4 border-2 border-blue-400 border-t-transparent rounded-full animate-spin"></span>
      Obteniendo dirección…
    </div>
    <div v-else-if="direccion" class="bg-green-50 border border-green-200 rounded-lg p-3 text-sm text-green-800">
      📍 {{ direccion }}
    </div>
  </div>
</template>
