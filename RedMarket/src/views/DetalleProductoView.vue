<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useCarritoStore } from '../stores/carrito'
import { useAuthStore } from '../stores/auth'
import { useToastStore } from '../stores/toast'
import { apiFetch } from '../composables/useApi'
import EstrellasDisplay from '../components/EstrellasDisplay.vue'

const route = useRoute()
const router = useRouter()
const carrito = useCarritoStore()
const auth = useAuthStore()
const toast = useToastStore()

const producto = ref<any>(null)
const cargando = ref(true)
const error = ref('')
const cantidad = ref(1)
const agregado = ref(false)

// Ratings
const resenas = ref<any[]>([])
const promedio = ref(0)
const totalResenas = ref(0)
const puntuacionInput = ref(5)
const comentarioInput = ref('')
const enviandoResena = ref(false)

const precioFinal = computed(() => {
  if (!producto.value) return 0
  const base = producto.value.en_descuento && producto.value.precio_oferta
    ? producto.value.precio_oferta
    : producto.value.precio_venta
  return (base * cantidad.value).toFixed(2)
})

const obtenerProducto = async () => {
  try {
    const resp = await apiFetch<any>(`/productos/${route.params.id}`, { skipAuth: true })
    producto.value = resp.data ?? resp
    // Cargar reseñas
    const res = await apiFetch<any>(`/productos/${route.params.id}/resenas`, { skipAuth: true })
    resenas.value = res.data || []
    promedio.value = res.promedio || 0
    totalResenas.value = res.total || 0
  } catch (e: any) {
    error.value = e.message || 'Error al cargar el producto'
  } finally {
    cargando.value = false
  }
}

const agregar = () => {
  carrito.agregarConCantidad(producto.value, cantidad.value)
  agregado.value = true
  toast.add(`${producto.value.nombre} añadido al carrito`, 'success')
  setTimeout(() => { agregado.value = false }, 2000)
}

const enviarResena = async () => {
  if (!auth.token) {
    router.push({ name: 'login', query: { redirect: route.name as string } })
    return
  }
  enviandoResena.value = true
  try {
    const res = await apiFetch<any>(`/productos/${route.params.id}/resenas`, {
      method: 'POST',
      body: JSON.stringify({ puntuacion: puntuacionInput.value, ...(comentarioInput.value.trim() ? { comentario: comentarioInput.value } : {}) }),
    })
    resenas.value = res.data ? [res.data, ...resenas.value.filter((r: any) => r.user?.id !== auth.user?.id)] : resenas.value
    promedio.value = res.promedio || promedio.value
    totalResenas.value = res.total || totalResenas.value
    comentarioInput.value = ''
    toast.add('Reseña guardada', 'success')
  } catch (e: any) {
    toast.add('Error al guardar reseña: ' + e.message, 'error')
  } finally {
    enviandoResena.value = false
  }
}

const eliminarResena = async (id: number) => {
  try {
    await apiFetch(`/resenas/${id}`, { method: 'DELETE' })
    resenas.value = resenas.value.filter((r: any) => r.id !== id)
    toast.add('Reseña eliminada', 'success')
  } catch (e: any) {
    toast.add('Error: ' + e.message, 'error')
  }
}

onMounted(obtenerProducto)
</script>

<template>
  <div v-if="cargando" class="max-w-4xl mx-auto py-8">
    <div class="flex gap-8">
      <div class="skeleton w-full md:w-96 h-64 md:h-96 flex-shrink-0"></div>
      <div class="flex-1 space-y-4">
        <div class="skeleton h-8 w-3/4"></div>
        <div class="skeleton h-4 w-1/2"></div>
        <div class="skeleton h-6 w-1/4"></div>
        <div class="skeleton h-24 w-full"></div>
        <div class="skeleton h-12 w-40"></div>
      </div>
    </div>
  </div>

  <div v-else-if="error" class="max-w-4xl mx-auto py-16 text-center">
    <p class="text-rojo-mercado-dark mb-3">{{ error }}</p>
    <button @click="obtenerProducto" class="bg-red-700 text-white px-4 py-2 rounded-lg cursor-pointer border-none hover:bg-red-800">Reintentar</button>
  </div>

  <div v-else-if="producto" class="max-w-4xl mx-auto">
    <button @click="router.back()" class="text-sm text-gray-500 hover:text-gray-700 mb-4 cursor-pointer bg-transparent border-none">⬅ Volver</button>

    <div class="bg-white rounded-lg shadow overflow-hidden">
      <div class="md:flex">
        <!-- Image -->
        <div class="md:w-1/2 bg-gray-50 p-8 flex items-center justify-center relative">
          <img :src="producto.imagen_url" :alt="producto.nombre" loading="lazy"
            class="max-h-96 max-w-full object-contain"
            :class="!producto.imagen_url && 'opacity-20'" />
          <div v-if="!producto.imagen_url" class="absolute inset-0 flex items-center justify-center pointer-events-none">
            <span class="text-6xl text-gray-300">📦</span>
          </div>
        </div>

        <!-- Details -->
        <div class="md:w-1/2 p-6 md:p-8 flex flex-col justify-between">
          <div>
            <span class="text-xs font-medium text-gray-500 uppercase tracking-wide">{{ producto.categoria?.nombre }}</span>
            <h1 class="text-2xl font-bold text-gray-800 mt-1">{{ producto.nombre }}</h1>

            <!-- Rating summary -->
            <div v-if="totalResenas > 0" class="flex items-center gap-2 mt-2">
              <EstrellasDisplay :puntuacion="Math.round(promedio)" size="sm" />
              <span class="text-sm text-gray-500">{{ promedio.toFixed(1) }} ({{ totalResenas }} reseñas)</span>
            </div>

            <div v-if="producto.en_descuento && producto.precio_oferta" class="flex items-center gap-2 mt-3">
              <span class="text-3xl font-bold text-orange-600">Bs. {{ producto.precio_oferta }}</span>
              <span class="text-lg text-gray-400 line-through">Bs. {{ producto.precio_venta }}</span>
              <span class="bg-red-100 text-red-700 text-xs font-semibold px-2 py-0.5 rounded">OFERTA</span>
            </div>
            <p v-else class="text-3xl font-bold text-gray-800 mt-3">Bs. {{ producto.precio_venta }}</p>

            <p v-if="producto.descripcion" class="text-gray-600 mt-4 leading-relaxed">{{ producto.descripcion }}</p>

            <div class="mt-4 space-y-1 text-sm text-gray-500">
              <p><span class="font-medium text-gray-700">Unidad:</span> {{ producto.unidad_medida === 'kg' ? 'Por kilogramo' : producto.unidad_medida === 'lt' ? 'Por litro' : 'Por unidad' }}</p>
              <p><span class="font-medium text-gray-700">Código:</span> {{ producto.codigo_barras }}</p>
              <p><span class="font-medium" :class="(producto.stock_actual ?? 0) > 0 ? 'text-green-600' : 'text-red-600'">{{ (producto.stock_actual ?? 0) > 0 ? 'En stock' : 'Sin stock' }}</span></p>
            </div>
          </div>

          <!-- Add to cart section -->
          <div class="mt-6 pt-6 border-t border-gray-200">
            <div class="flex items-center gap-4 mb-4">
              <label class="text-sm font-medium text-gray-700">Cantidad:</label>
              <div class="flex items-center border border-gray-300 rounded">
                <button @click="cantidad = Math.max(1, cantidad - 1)" aria-label="Reducir cantidad"
                  class="px-3 py-1.5 text-gray-500 hover:bg-gray-100 font-bold cursor-pointer border-none bg-transparent">−</button>
                <input type="number" v-model.number="cantidad" min="1" :max="producto.stock_actual || 99"
                  class="w-14 text-center border-x border-gray-300 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-red-300" />
                <button @click="cantidad = Math.min(producto.stock_actual || 99, cantidad + 1)" aria-label="Aumentar cantidad"
                  class="px-3 py-1.5 text-gray-500 hover:bg-gray-100 font-bold cursor-pointer border-none bg-transparent">+</button>
              </div>
            </div>

            <div class="flex items-center justify-between mb-4">
              <span class="text-lg font-semibold text-gray-700">Total: <span class="text-orange-600">Bs. {{ precioFinal }}</span></span>
            </div>

            <button @click="agregar"
              class="w-full bg-gray-800 text-white py-3 rounded-lg font-bold text-lg hover:bg-gray-700 transition-colors cursor-pointer border-none flex items-center justify-center gap-2">
              <span v-if="agregado">✅ ¡Añadido!</span>
              <span v-else>🛒 Añadir al Carrito</span>
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Ratings Section -->
    <div class="bg-white rounded-lg shadow mt-6 p-6">
      <h2 class="text-lg font-bold text-gray-800 mb-4">
        Reseñas
        <span v-if="totalResenas > 0" class="text-sm font-normal text-gray-500">({{ totalResenas }})</span>
      </h2>

      <!-- Write review -->
      <div v-if="auth.token" class="bg-gray-50 rounded-lg p-4 mb-6">
        <h3 class="text-sm font-semibold text-gray-700 mb-3">Escribe tu reseña</h3>
        <div class="flex items-center gap-1 mb-3">
          <button v-for="i in 5" :key="i" @click="puntuacionInput = i"
            class="text-2xl cursor-pointer bg-transparent border-none transition-colors"
            :class="i <= puntuacionInput ? 'text-yellow-400' : 'text-gray-300 hover:text-yellow-300'">★</button>
        </div>
        <textarea v-model="comentarioInput" rows="2" placeholder="Comparte tu opinión sobre este producto…"
          class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-300 mb-3"></textarea>
        <button @click="enviarResena" :disabled="enviandoResena"
          class="bg-red-700 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-red-800 disabled:opacity-50 cursor-pointer border-none">
          {{ enviandoResena ? 'Enviando...' : 'Enviar reseña' }}
        </button>
      </div>
      <div v-else class="bg-gray-50 rounded-lg p-4 mb-6 text-center text-sm text-gray-500">
        <RouterLink to="/login" class="text-red-700 font-medium no-underline">Inicia sesión</RouterLink> para dejar una reseña.
      </div>

      <!-- Review list -->
      <div v-if="resenas.length === 0" class="text-center py-8 text-gray-400 text-sm">
        Aún no hay reseñas para este producto.
      </div>
      <div v-else class="space-y-4">
        <div v-for="r in resenas" :key="r.id" class="border-b border-gray-100 pb-4 last:border-0">
          <div class="flex items-center justify-between">
            <div class="flex items-center gap-2">
              <span class="w-7 h-7 rounded-full bg-red-100 text-red-700 flex items-center justify-center text-xs font-bold">{{ r.user?.name?.charAt(0)?.toUpperCase() }}</span>
              <span class="font-medium text-sm text-gray-800">{{ r.user?.name }}</span>
              <EstrellasDisplay :puntuacion="r.puntuacion" size="sm" />
            </div>
            <div class="flex items-center gap-2">
              <span class="text-xs text-gray-400">{{ new Date(r.created_at).toLocaleDateString('es-BO') }}</span>
              <button v-if="auth.user?.id === r.user?.id" @click="eliminarResena(r.id)"
                class="text-xs text-red-400 hover:text-red-600 cursor-pointer bg-transparent border-none">✕</button>
            </div>
          </div>
          <p v-if="r.comentario" class="text-sm text-gray-600 mt-1 ml-9">{{ r.comentario }}</p>
        </div>
      </div>
    </div>
  </div>

  <div v-else class="text-center py-12 text-gray-500">Producto no encontrado.</div>
</template>
