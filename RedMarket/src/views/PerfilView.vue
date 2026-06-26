<script setup lang="ts">
import { ref, onMounted, computed } from 'vue'
import { apiFetch } from '../composables/useApi'
import { useAuthStore } from '../stores/auth'
import MapLocationPicker from '../components/MapLocationPicker.vue'

const auth = useAuthStore()
const editando = ref(false)
const name = ref('')
const telefono = ref('')
const direccion = ref('')
const latitud = ref<number | null>(null)
const longitud = ref<number | null>(null)
const guardando = ref(false)
const mensaje = ref('')

// Order history
const pedidos = ref<any[]>([])
const cargandoPedidos = ref(true)
const errorPedidos = ref('')

const toggleEditar = () => {
  if (!editando.value) {
    name.value = auth.user?.name || ''
    telefono.value = auth.user?.telefono || ''
    direccion.value = auth.user?.direccion || ''
    latitud.value = auth.user?.latitud || null
    longitud.value = auth.user?.longitud || null
  }
  editando.value = !editando.value
  mensaje.value = ''
}

const onLocationSelect = (loc: { direccion: string; lat: number; lng: number }) => {
  direccion.value = loc.direccion
  latitud.value = loc.lat
  longitud.value = loc.lng
}

const guardar = async () => {
  guardando.value = true
  mensaje.value = ''
  try {
    const data = await apiFetch('/perfil', {
      method: 'PUT',
      body: JSON.stringify({
        name: name.value,
        telefono: telefono.value,
        direccion: direccion.value,
        latitud: latitud.value || undefined,
        longitud: longitud.value || undefined,
      }),
    })
    auth.updateUser(data.user)
    mensaje.value = '✅ Perfil actualizado'
    editando.value = false
  } catch (e: any) {
    mensaje.value = '❌ ' + e.message
  } finally {
    guardando.value = false
  }
}

const fetchPedidos = async () => {
  if (!auth.token) { cargandoPedidos.value = false; return }
  try {
    const resp = await apiFetch<any>('/pedidos')
    pedidos.value = resp.data ?? resp
  } catch (e: any) {
    if (!e.message?.includes('expirado')) {
      errorPedidos.value = e.message || 'Error al cargar pedidos'
    }
  } finally { cargandoPedidos.value = false }
}

const estadoColor = (e: string) => {
  const colors: Record<string, string> = {
    pendiente: 'bg-yellow-100 text-yellow-800',
    en_preparacion: 'bg-orange-100 text-orange-800',
    listo_despacho: 'bg-blue-100 text-blue-800',
    en_camino: 'bg-blue-100 text-blue-800',
    entregado: 'bg-green-100 text-green-800',
    cancelado: 'bg-red-100 text-red-800',
  }
  return colors[e] || 'bg-gray-100 text-ink'
}

onMounted(fetchPedidos)
</script>

<template>
  <div class="max-w-2xl mx-auto space-y-6">
    <!-- Profile card -->
    <div class="bg-blanco-mercado rounded-lg shadow p-6 overflow-hidden">
      <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold text-ink">Mi Perfil</h1>
        <button @click="toggleEditar" class="text-sm text-red-700 hover:underline font-medium cursor-pointer bg-transparent border-none">
          {{ editando ? 'Cancelar' : 'Editar' }}
        </button>
      </div>

      <div v-if="mensaje" class="mb-4 text-sm font-medium" :class="mensaje.startsWith('✅') ? 'text-green-700' : 'text-red-700'">
        {{ mensaje }}
      </div>

      <div v-if="!editando" class="space-y-4">
        <div>
          <label class="text-sm font-medium text-ink-muted">Nombre</label>
          <p class="text-ink font-semibold">{{ auth.user?.name || '—' }}</p>
        </div>
        <div>
          <label class="text-sm font-medium text-ink-muted">Email</label>
          <p class="text-ink">{{ auth.user?.email || '—' }}</p>
        </div>
        <div>
          <label class="text-sm font-medium text-ink-muted">Teléfono</label>
          <p class="text-ink">{{ auth.user?.telefono || '—' }}</p>
        </div>
        <div>
          <label class="text-sm font-medium text-ink-muted">Dirección</label>
          <p class="text-ink">{{ auth.user?.direccion || '—' }}</p>
        </div>
      </div>

      <form v-else @submit.prevent="guardar" class="space-y-4 overflow-hidden">
        <div>
          <label class="block text-sm font-medium text-ink mb-1">Nombre</label>
          <input type="text" v-model="name" required
            class="w-full border border-platform-edge rounded px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-rojo-mercado" />
        </div>
        <div>
          <label class="block text-sm font-medium text-ink mb-1">Teléfono</label>
          <input type="text" v-model="telefono" placeholder="Ej: 76543210"
            class="w-full border border-platform-edge rounded px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-rojo-mercado" />
        </div>
        <div>
          <label class="block text-sm font-medium text-ink mb-1">Dirección de entrega</label>
          <MapLocationPicker
            :initialAddress="direccion"
            :initialLat="latitud || undefined"
            :initialLng="longitud || undefined"
            @select="onLocationSelect"
          />
        </div>
        <button type="submit" :disabled="guardando"
          class="w-full bg-red-700 text-white py-2 rounded font-semibold hover:bg-red-800 disabled:opacity-50">
          {{ guardando ? 'Guardando...' : 'Guardar Cambios' }}
        </button>
      </form>
    </div>

    <!-- Order history -->
    <div class="bg-blanco-mercado rounded-lg shadow p-6">
      <h3 class="text-lg font-bold text-ink mb-4">Mis Pedidos</h3>

      <div v-if="cargandoPedidos" class="space-y-3 py-4">
        <div class="skeleton h-12 w-full"></div>
        <div class="skeleton h-12 w-full"></div>
        <div class="skeleton h-12 w-3/4"></div>
      </div>

      <div v-else-if="errorPedidos" class="text-center py-4">
        <p class="text-rojo-mercado-dark text-sm mb-2">{{ errorPedidos }}</p>
        <button @click="fetchPedidos" class="text-xs bg-red-700 text-white px-3 py-1.5 rounded cursor-pointer border-none hover:bg-red-800">Reintentar</button>
      </div>

      <div v-else-if="pedidos.length === 0" class="text-center py-4 text-ink-dim text-sm">
        No tienes pedidos aún.
      </div>

      <div v-else class="space-y-3">
        <div v-for="p in pedidos" :key="p.id"
          class="border border-platform-edge rounded-lg p-4 hover:border-platform-edge transition-colors">
          <div class="flex items-center justify-between mb-2">
            <span class="font-bold text-rojo-mercado-dark">{{ p.codigo || '#' + p.id }}</span>
            <span class="text-xs px-2 py-0.5 rounded-full font-medium" :class="estadoColor(p.estado)">
              {{ p.estado?.replace(/_/g, ' ') }}
            </span>
          </div>
          <p class="text-sm text-ink-muted">{{ p.items?.length }} producto(s) — Total: <span class="font-semibold">Bs. {{ p.total_final }}</span></p>
          <p class="text-xs text-ink-dim">Creado: {{ new Date(p.created_at).toLocaleDateString('es-BO') }}</p>
        </div>
      </div>
    </div>
  </div>
</template>
