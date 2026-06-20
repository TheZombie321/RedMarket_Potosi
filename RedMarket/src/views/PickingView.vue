<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { apiFetch } from '../composables/useApi'
import { useAuthStore } from '../stores/auth'
import { useToastStore } from '../stores/toast'
import { estadoBadge } from '../utils/estados'

const auth = useAuthStore()
const toast = useToastStore()
const tab = ref<'disponibles' | 'mis_pedidos'>('disponibles')
const disponibles = ref<any[]>([])
const misPedidos = ref<any[]>([])
const pedidoSeleccionado = ref<any>(null)
const cargando = ref(true)
const error = ref('')

// Modal producto
const modalProducto = ref<any>(null)
const mostrarModal = ref(false)

// Scanner
const videoRef = ref<HTMLVideoElement | null>(null)
const escaneando = ref(false)
const stream = ref<MediaStream | null>(null)
const qrInput = ref('')
const qrError = ref('')
const scanCantidad = ref(1)

const todosCompletos = computed(() => {
  if (!pedidoSeleccionado.value) return false
  return pedidoSeleccionado.value.items.every((i: any) => (i._recolectados || 0) >= i.cantidad)
})

const misActivos = computed(() =>
  misPedidos.value.filter((p: any) => p.estado === 'en_preparacion')
)

const misHistorial = computed(() =>
  misPedidos.value.filter((p: any) => !['pendiente', 'en_preparacion'].includes(p.estado))
)

const fetchPedidos = async () => {
  if (fetchAbort) fetchAbort.abort()
  fetchAbort = new AbortController()
  cargando.value = true
  try {
    const data = await apiFetch('/pedidos', { signal: fetchAbort.signal } as any)
    if (data.disponibles !== undefined) {
      disponibles.value = data.disponibles ?? []
      misPedidos.value = data.mis_pedidos ?? []
    } else if (Array.isArray(data)) {
      disponibles.value = data.filter((p: any) => p.estado === 'pendiente')
      misPedidos.value = data.filter((p: any) => p.picking_user_id === auth.user?.id)
    } else if (data.data && Array.isArray(data.data)) {
      disponibles.value = data.data.filter((p: any) => p.estado === 'pendiente')
      misPedidos.value = data.data.filter((p: any) => p.picking_user_id === auth.user?.id)
    }
  } catch (e: any) { if (e.name !== 'AbortError') error.value = e.message || 'Error al cargar pedidos' } finally { cargando.value = false }
}

const tomarPedido = async (pedido: any) => {
  try {
    const data = await apiFetch(`/pedidos/${pedido.id}`, {
      method: 'PUT',
      body: JSON.stringify({ estado: 'en_preparacion' }),
    })
    data.items.forEach((i: any) => { i._recolectados = 0 })
    pedidoSeleccionado.value = data
    fetchPedidos()
  } catch (e: any) { toast.add('Error: ' + e.message, 'error') }
}

const continuarPreparacion = (p: any) => {
  p.items.forEach((i: any) => { if (i._recolectados === undefined) i._recolectados = 0 })
  pedidoSeleccionado.value = p
}

const abrirModal = (item: any) => {
  modalProducto.value = item
  mostrarModal.value = true
}

const escanearItem = (item: any) => {
  if (!item._recolectados) item._recolectados = 0
  item._recolectados = Math.min(item.cantidad, item._recolectados + (scanCantidad.value || 1))
  scanCantidad.value = 1
}

const finalizarPedido = async () => {
  if (!todosCompletos.value) return
  try {
    const data = await apiFetch(`/pedidos/${pedidoSeleccionado.value.id}`, {
      method: 'PUT',
      body: JSON.stringify({ estado: 'listo_despacho' }),
    })
    toast.add(`${data.codigo || '#' + data.id} listo para despacho`, 'success')
    detenerCamara()
    pedidoSeleccionado.value = null
    fetchPedidos()
  } catch (e: any) { toast.add('Error: ' + e.message, 'error') }
}

// Camera
const iniciarCamara = async () => {
  escaneando.value = true
  qrError.value = ''
  try {
    stream.value = await navigator.mediaDevices.getUserMedia({ video: { facingMode: 'environment' } })
    if (videoRef.value) videoRef.value.srcObject = stream.value
  } catch {
    qrError.value = 'No se pudo acceder a la cámara. Usa el campo manual.'
  }
}

const detenerCamara = () => {
  if (stream.value) { stream.value.getTracks().forEach(t => t.stop()); stream.value = null }
  escaneando.value = false
}

const escanearManual = () => {
  const id = parseInt(qrInput.value, 10)
  qrInput.value = ''
  if (isNaN(id) || !pedidoSeleccionado.value) return
  const item = pedidoSeleccionado.value.items.find((i: any) => i.id === id || i.producto?.id === id)
  if (item) escanearItem(item)
  else toast.add('Producto no encontrado', 'error')
}

let refreshInterval: ReturnType<typeof setInterval> | null = null
let fetchAbort: AbortController | null = null

onMounted(() => {
  fetchPedidos()
  refreshInterval = setInterval(fetchPedidos, 30000)
})

onUnmounted(() => {
  detenerCamara()
  if (refreshInterval) clearInterval(refreshInterval)
})
</script>

<template>
  <div>
    <h1 class="text-2xl font-bold text-ink mb-4">Picking — Preparación de Pedidos</h1>

    <!-- Tabs -->
    <div v-if="!pedidoSeleccionado" class="flex gap-2 mb-6 flex-wrap">
      <button @click="tab = 'disponibles'"
        class="px-4 py-2 rounded text-sm font-semibold cursor-pointer border-none"
        :class="tab === 'disponibles' ? 'bg-amber-600 text-white' : 'bg-gray-200 text-ink hover:bg-gray-300'">
        📋 Disponibles ({{ disponibles.length }})
      </button>
      <button @click="tab = 'mis_pedidos'"
        class="px-4 py-2 rounded text-sm font-semibold cursor-pointer border-none"
        :class="tab === 'mis_pedidos' ? 'bg-amber-600 text-white' : 'bg-gray-200 text-ink hover:bg-gray-300'">
        📦 Mis Pedidos ({{ misPedidos.length }})
      </button>
    </div>

    <div v-else-if="error && !pedidoSeleccionado" class="text-center py-12">
      <p class="text-rojo-mercado-dark mb-3">{{ error }}</p>
      <button @click="fetchPedidos" class="bg-red-700 text-white px-4 py-2 rounded-lg cursor-pointer border-none hover:bg-red-800">Reintentar</button>
    </div>

    <div v-if="cargando && !pedidoSeleccionado" class="py-8 space-y-4">
      <div class="skeleton h-6 w-48 mx-auto"></div>
      <div class="grid grid-cols-3 gap-4">
        <div class="skeleton h-36"></div>
        <div class="skeleton h-36"></div>
        <div class="skeleton h-36"></div>
      </div>
    </div>

    <!-- DISPONIBLES -->
    <div v-else-if="!pedidoSeleccionado && tab === 'disponibles'">
      <div v-if="disponibles.length === 0" class="text-center py-8 text-green-600 font-semibold">✅ No hay pedidos pendientes.</div>
      <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        <div v-for="p in disponibles" :key="p.id"
          class="bg-orange-50 rounded-lg border border-orange-200 p-4 hover:shadow-sm transition-shadow">
          <div class="flex justify-between items-center mb-2 border-b border-orange-200 pb-2">
            <h4 class="font-bold text-lg text-rojo-mercado-dark">{{ p.codigo || '#' + p.id }}</h4>
            <span class="bg-orange-500 text-white text-xs px-2 py-0.5 rounded-full">{{ p.estado.replace(/_/g, ' ') }}</span>
          </div>
          <p class="text-sm text-ink-muted"><span class="font-semibold">Cliente:</span> {{ p.user?.name || p.user_id }}</p>
          <p class="text-sm text-ink-muted"><span class="font-semibold">Items:</span> {{ p.items?.length }} tipos</p>
          <p class="text-sm text-ink-muted"><span class="font-semibold">Total:</span> Bs. {{ p.total_final }}</p>
          <button @click="tomarPedido(p)"
            class="mt-3 w-full bg-gray-800 text-white py-2 rounded text-sm font-semibold hover:bg-gray-700 cursor-pointer border-none">
            Preparar Pedido
          </button>
        </div>
      </div>
    </div>

    <!-- MIS PEDIDOS -->
    <div v-else-if="!pedidoSeleccionado && tab === 'mis_pedidos'">
      <div v-if="misPedidos.length === 0" class="text-center py-8 text-ink-muted">Aún no has tomado ningún pedido.</div>

      <!-- Activos -->
      <div v-if="misActivos.length" class="mb-8">
        <h3 class="text-md font-semibold text-ink mb-3 flex items-center gap-2">
          <span class="w-2.5 h-2.5 rounded-full bg-orange-500 inline-block"></span>
          En preparación ({{ misActivos.length }})
        </h3>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
          <div v-for="p in misActivos" :key="p.id"
            class="bg-orange-50 rounded-lg border border-orange-200 p-4 hover:shadow-sm transition-shadow">
            <div class="flex justify-between items-center mb-2 border-b border-orange-200 pb-2">
              <h4 class="font-bold text-lg text-rojo-mercado-dark">{{ p.codigo || '#' + p.id }}</h4>
              <span class="text-xs px-2 py-0.5 rounded-full font-medium" :class="estadoBadge(p.estado)">{{ p.estado.replace(/_/g, ' ') }}</span>
            </div>
            <p class="text-sm text-ink-muted"><span class="font-semibold">Cliente:</span> {{ p.user?.name || p.user_id }}</p>
            <p class="text-sm text-ink-muted"><span class="font-semibold">Items:</span> {{ p.items?.length }} tipos</p>
            <button @click="continuarPreparacion(p)"
              class="mt-3 w-full bg-blue-600 text-white py-2 rounded text-sm font-semibold hover:bg-blue-700 cursor-pointer border-none">
              Continuar Preparación
            </button>
          </div>
        </div>
      </div>

      <!-- Historial -->
      <div v-if="misHistorial.length">
        <h3 class="text-md font-semibold text-ink mb-3 flex items-center gap-2">
          <span class="w-2.5 h-2.5 rounded-full bg-gray-400 inline-block"></span>
          Completados ({{ misHistorial.length }})
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

    <!-- Detalle del pedido en preparación (same as before) -->
    <div v-else-if="pedidoSeleccionado">
      <button @click="() => { detenerCamara(); pedidoSeleccionado = null }"
        class="text-sm text-ink-muted hover:text-ink mb-3 cursor-pointer bg-transparent border-none">⬅ Volver</button>

      <div class="bg-blue-50 border border-blue-200 p-4 rounded mb-4">
        <h3 class="font-bold text-lg">Preparando <span class="text-rojo-mercado-dark">{{ pedidoSeleccionado.codigo || '#' + pedidoSeleccionado.id }}</span></h3>
        <p class="text-sm text-ink-muted"><span class="font-semibold">Cliente:</span> {{ pedidoSeleccionado.user?.name }}</p>
        <p class="text-sm text-ink-muted"><span class="font-semibold">Dirección:</span> {{ pedidoSeleccionado.direccion_texto }}</p>
      </div>

      <!-- Scanner -->
      <div class="bg-gray-50 border rounded-lg p-4 mb-4">
        <div class="flex items-center gap-3 mb-3">
          <h4 class="font-semibold text-ink">Escanear Producto</h4>
          <button v-if="!escaneando" @click="iniciarCamara"
            class="text-xs bg-blue-600 text-white px-3 py-1 rounded hover:bg-blue-700 cursor-pointer border-none">📷 Abrir Cámara</button>
          <button v-else @click="detenerCamara"
            class="text-xs bg-red-600 text-white px-3 py-1 rounded hover:bg-red-700 cursor-pointer border-none">❌ Cerrar Cámara</button>
        </div>

        <div v-if="escaneando" class="mb-3">
          <video ref="videoRef" autoplay playsinline class="w-full max-w-sm rounded border border-platform-edge"></video>
          <p class="text-xs text-ink-dim mt-1">Apunta al código del producto</p>
        </div>
        <div v-if="qrError" class="text-rojo-mercado text-sm mb-2">{{ qrError }}</div>

            <div class="flex items-center gap-2 mb-3">
              <label class="text-sm text-ink-muted">Cantidad:</label>
              <div class="flex items-center border border-platform-edge rounded">
                <button @click="scanCantidad = Math.max(1, scanCantidad - 1)" aria-label="Reducir cantidad" class="px-2 py-1 text-ink-muted hover:bg-gray-100 cursor-pointer border-none bg-transparent text-sm">−</button>
                <input type="number" v-model.number="scanCantidad" min="1" max="99"
                  class="w-12 text-center border-x border-platform-edge py-1 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" />
            <button @click="scanCantidad++" aria-label="Aumentar cantidad" class="px-2 py-1 text-ink-muted hover:bg-gray-100 cursor-pointer border-none bg-transparent text-sm">+</button>
          </div>
          <span class="text-xs text-ink-dim">uds. por escaneo</span>
        </div>

        <div class="flex gap-2">
          <label for="qr-input" class="sr-only">ID del producto</label>
          <input id="qr-input" v-model="qrInput" @keyup.enter="escanearManual" placeholder="ID del producto"
            class="flex-1 border border-platform-edge rounded px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" />
          <button @click="escanearManual"
            class="bg-gray-700 text-white px-4 py-2 rounded text-sm font-medium hover:bg-gray-600 cursor-pointer border-none">Escanear</button>
        </div>
      </div>

      <!-- Items -->
      <div class="space-y-2">
        <div v-for="item in pedidoSeleccionado.items" :key="item.id"
          class="flex items-center gap-3 bg-white border rounded-lg p-3 cursor-pointer hover:bg-gray-50 transition-colors"
          :class="(item._recolectados || 0) >= item.cantidad ? 'border-green-500 bg-green-50' : 'border-platform-edge'"
          @click="abrirModal(item)">

          <img :src="item.producto?.imagen_url" :alt="item.producto?.nombre || 'Producto'" loading="lazy" class="w-12 h-12 object-contain border rounded" />

          <div class="flex-1">
            <div class="flex items-center gap-2">
              <span class="font-bold text-rojo-mercado">{{ item.cantidad }}x</span>
              <span class="text-sm font-medium" :class="(item._recolectados || 0) >= item.cantidad ? 'line-through text-ink-dim' : ''">{{ item.producto?.nombre }}</span>
            </div>
            <div class="flex items-center gap-2 mt-1">
              <span class="text-xs text-ink-dim">ID: #{{ item.id }}</span>
              <div class="flex-1 max-w-32 h-1.5 bg-gray-200 rounded-full overflow-hidden">
                <div class="h-full bg-green-500 rounded-full transition-all" :style="{ width: Math.min(100, ((item._recolectados || 0) / item.cantidad) * 100) + '%' }"></div>
              </div>
              <span class="text-xs font-medium" :class="(item._recolectados || 0) >= item.cantidad ? 'text-green-600' : 'text-ink-muted'">
                {{ item._recolectados || 0 }}/{{ item.cantidad }}
              </span>
            </div>
          </div>

          <button @click.stop="escanearItem(item)"
            class="w-10 h-10 rounded-full flex items-center justify-center text-white text-sm transition-colors cursor-pointer border-none"
            :class="(item._recolectados || 0) >= item.cantidad ? 'bg-green-500' : 'bg-gray-400 hover:bg-gray-500'">
            {{ (item._recolectados || 0) >= item.cantidad ? '✔' : '📷' }}
          </button>
        </div>
      </div>

      <div class="mt-4 text-sm text-ink-muted">
        Progreso: {{ pedidoSeleccionado.items.filter((i: any) => (i._recolectados || 0) >= i.cantidad).length }} / {{ pedidoSeleccionado.items.length }} productos completos
      </div>

      <button @click="finalizarPedido" :disabled="!todosCompletos"
        class="mt-4 w-full bg-green-600 text-white py-3 rounded-lg font-bold text-lg hover:bg-green-700 disabled:opacity-50 disabled:cursor-not-allowed cursor-pointer border-none">
        {{ todosCompletos ? 'Marcar como Listo para Despacho' : 'Escanea todos los productos primero' }}
      </button>
    </div>

    <!-- Modal producto -->
    <Teleport to="body">
      <Transition name="modal">
        <div v-if="mostrarModal && modalProducto" class="fixed inset-0 z-modal flex items-center justify-center bg-black/40" role="dialog" aria-modal="true" aria-label="Detalle del producto"
          @click.self="mostrarModal = false" @keydown.escape="mostrarModal = false">
          <div class="modal-panel bg-white rounded-xl shadow-xl max-w-md w-full mx-4 overflow-hidden">
          <div class="p-5">
            <div class="flex items-start justify-between mb-4">
              <h3 class="text-lg font-bold text-ink">{{ modalProducto.producto?.nombre }}</h3>
              <button @click="mostrarModal = false" aria-label="Cerrar modal" class="text-ink-dim hover:text-ink-muted text-xl cursor-pointer bg-transparent border-none">✕</button>
            </div>

            <img :src="modalProducto.producto?.imagen_url" :alt="modalProducto.producto?.nombre || 'Producto'" loading="lazy" class="w-full h-48 object-contain mb-4 border rounded" />

            <div class="space-y-2 text-sm">
              <p><span class="font-semibold text-ink-muted">Código:</span> <span class="font-mono">{{ modalProducto.producto?.codigo_barras }}</span></p>
              <p><span class="font-semibold text-ink-muted">Cantidad a preparar:</span> {{ modalProducto.cantidad }}</p>
              <p><span class="font-semibold text-ink-muted">Recolectados:</span> {{ modalProducto._recolectados || 0 }}</p>
              <hr class="my-2">
              <p class="font-semibold text-ink-muted">Ubicación en almacén:</p>
              <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-3">
                <p><span class="font-medium">Pasillo:</span> <span class="font-bold text-ink">{{ modalProducto.producto?.pasillo || '—' }}</span></p>
                <p><span class="font-medium">Nivel:</span> <span class="font-bold text-ink">{{ modalProducto.producto?.nivel || '—' }}</span></p>
                <p><span class="font-medium">Stock:</span> <span class="font-bold text-ink">{{ modalProducto.producto?.stock_actual || '—' }} {{ modalProducto.producto?.unidad_medida }}</span></p>
              </div>
            </div>

            <div class="mt-5 flex gap-2">
              <button @click="escanearItem(modalProducto); mostrarModal = false"
                class="flex-1 bg-blue-600 text-white py-2 rounded text-sm font-medium hover:bg-blue-700 cursor-pointer border-none">
                {{ (modalProducto._recolectados || 0) >= modalProducto.cantidad ? '✔ Completado' : 'Marcar 1 recolectado' }}
              </button>
              <button @click="mostrarModal = false"
                class="flex-1 bg-gray-200 text-ink py-2 rounded text-sm font-medium hover:bg-gray-300 cursor-pointer border-none">
                Cerrar
              </button>
            </div>
          </div>
        </div>
      </div>
    </Transition>
    </Teleport>
  </div>
</template>