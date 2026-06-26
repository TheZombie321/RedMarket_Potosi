<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { useCarritoStore } from '../stores/carrito'
import { useAuthStore } from '../stores/auth'
import { useToastStore } from '../stores/toast'
import { useRouter } from 'vue-router'
import { apiFetch } from '../composables/useApi'

const carrito = useCarritoStore()
const auth = useAuthStore()
const toast = useToastStore()
const router = useRouter()
const procesando = ref(false)
const error = ref('')
const paymentMethod = ref<'cash' | 'stripe'>('cash')

const direccionOverride = ref('')
const usarDireccionGuardada = ref(true)

onMounted(() => {
  if (!auth.token) {
    router.push({ name: 'login', query: { redirect: 'carrito' } })
  }
})

const incrementar = (item: any) => {
  const i = carrito.items.find((x: any) => x.id === item.id)
  if (i) i.cantidad++
}

const decrementar = (item: any) => {
  const i = carrito.items.find((x: any) => x.id === item.id)
  if (i && i.cantidad > 1) i.cantidad--
}

const eliminar = (item: any) => {
  carrito.eliminarItem(item.id)
}

const direccionFinal = computed(() => {
  if (usarDireccionGuardada.value) return auth.user?.direccion || ''
  return direccionOverride.value
})

const totalFormateado = computed(() => carrito.totalPagar.toFixed(2))
const totalConDelivery = computed(() => (carrito.totalPagar + 5).toFixed(2))

const getSubtotal = (item: any) => (item.precio_venta * item.cantidad).toFixed(2)

const procesarPago = async () => {
  if (carrito.items.length === 0) return
  const dir = direccionFinal.value
  if (!dir.trim()) {
    error.value = 'Configura tu dirección en tu perfil antes de hacer un pedido.'
    return
  }

  procesando.value = true
  error.value = ''
  try {
    const data = await apiFetch<any>('/pedidos', {
      method: 'POST',
      body: JSON.stringify({
        items: carrito.items.map(i => ({ producto_id: i.id, cantidad: i.cantidad })),
        direccion_texto: dir,
        latitud: auth.user?.latitud || undefined,
        longitud: auth.user?.longitud || undefined,
        payment_method: paymentMethod.value,
      }),
    })

    if (paymentMethod.value === 'cash') {
      toast.add(`Pedido ${data.codigo || '#' + data.id} creado. Total: Bs. ${data.total_final}`, 'success')
      carrito.vaciarCarrito()
      router.push('/tracking')
    } else {
      const session = await apiFetch<{ url: string }>(`/pedidos/${data.id}/stripe/checkout`, {
        method: 'POST',
      })
      sessionStorage.setItem('pending_order_id', data.id.toString())
      window.location.href = session.url
    }
  } catch (e: any) {
    error.value = e.message
  } finally {
    procesando.value = false
  }
}
</script>

<template>
  <div class="max-w-3xl mx-auto bg-white rounded-lg shadow p-6">
    <h1 class="text-2xl font-bold text-gray-800 mb-4">Mi Carrito</h1>

    <div v-if="carrito.items.length === 0" class="text-center py-12 text-gray-500">
      <p class="mb-4">No tienes productos en tu carrito.</p>
      <RouterLink to="/" class="bg-gray-800 text-white px-4 py-2 rounded text-sm no-underline hover:bg-gray-700">Ir al Catálogo</RouterLink>
    </div>

    <div v-else>
      <div v-if="error" class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4 text-sm">{{ error }}</div>

      <!-- Items -->
      <div v-for="item in carrito.items" :key="item.id"
        class="flex flex-wrap items-center gap-3 border-b border-gray-200 py-3">
        <img :src="item.imagen_url" :alt="item.nombre" loading="lazy" class="w-14 h-14 object-contain" />
        <div class="flex-1 min-w-[120px]">
          <h4 class="font-semibold text-sm truncate">{{ item.nombre }}</h4>
          <p class="text-xs text-gray-500">Bs. {{ item.precio_venta }} c/u</p>
        </div>

        <div class="flex items-center border border-gray-300 rounded">
          <button @click="decrementar(item)" aria-label="Reducir cantidad"
            class="w-9 h-9 flex items-center justify-center text-gray-500 hover:bg-gray-100 font-bold cursor-pointer border-none bg-transparent text-sm">−</button>
          <span class="w-10 text-center py-1 text-sm font-medium border-x border-gray-300">{{ item.cantidad }}</span>
          <button @click="incrementar(item)" aria-label="Aumentar cantidad"
            class="w-9 h-9 flex items-center justify-center text-gray-500 hover:bg-gray-100 font-bold cursor-pointer border-none bg-transparent text-sm">+</button>
        </div>

        <p class="font-bold text-orange-600 sm:w-20 text-right text-sm">Bs. {{ getSubtotal(item) }}</p>
        <button @click="eliminar(item)"
          class="text-red-400 hover:text-red-600 text-lg cursor-pointer bg-transparent border-none" title="Eliminar">✕</button>
      </div>

      <!-- Payment method -->
      <div class="mt-4 pt-4 border-t border-gray-200">
        <h3 class="font-semibold text-gray-800 mb-3">Metodo de pago</h3>
        <div class="space-y-2">
          <label class="flex items-center gap-2 text-sm cursor-pointer">
            <input type="radio" value="cash" v-model="paymentMethod" class="accent-red-700" />
            <span>Efectivo — Paga al recibir</span>
          </label>
          <label class="flex items-center gap-2 text-sm cursor-pointer">
            <input type="radio" value="stripe" v-model="paymentMethod" class="accent-red-700" />
            <span>Tarjeta — Paga ahora con Stripe</span>
          </label>
        </div>
      </div>

      <!-- Address section -->
      <div class="mt-6 pt-4 border-t border-gray-200">
        <h3 class="font-semibold text-gray-800 mb-3">📍 Dirección de entrega</h3>

        <div v-if="auth.user?.direccion" class="space-y-3">
          <label class="flex items-center gap-2 text-sm cursor-pointer">
            <input type="radio" :value="true" v-model="usarDireccionGuardada" class="accent-red-700" />
            <span class="text-gray-700">{{ auth.user.direccion }}</span>
          </label>
          <label class="flex items-center gap-2 text-sm cursor-pointer">
            <input type="radio" :value="false" v-model="usarDireccionGuardada" class="accent-red-700" />
            <span class="text-gray-700">Usar otra dirección</span>
          </label>
          <div v-if="!usarDireccionGuardada">
            <input v-model="direccionOverride" type="text" placeholder="Ingresa la dirección para este pedido"
              class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-red-300" />
          </div>
        </div>

        <div v-else class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 text-sm">
          <p class="text-yellow-700 mb-2">No tienes dirección registrada.</p>
          <RouterLink to="/perfil" class="text-red-700 hover:underline font-medium">Agregar dirección en mi perfil →</RouterLink>
        </div>
      </div>

      <!-- Total & Pay -->
      <div v-if="direccionFinal" class="flex items-center justify-between mt-6 pt-4 border-t-2 border-gray-800">
        <div>
          <p class="text-sm text-gray-500">Productos: Bs. {{ totalFormateado }}</p>
          <p class="text-sm text-gray-500">Delivery: Bs. 5.00</p>
          <h3 class="text-xl font-bold mt-1">Total: Bs. {{ totalConDelivery }}</h3>
          <p v-if="paymentMethod === 'stripe'" class="text-xs text-blue-600 mt-1">
            Seras redirigido a Stripe para completar el pago.
          </p>
        </div>
        <button @click="procesarPago" :disabled="procesando"
          class="bg-green-600 text-white px-6 py-2.5 rounded-lg font-semibold hover:bg-green-700 disabled:opacity-50 cursor-pointer border-none flex items-center gap-2">
          <span v-if="procesando" class="w-4 h-4 border-2 border-white/30 border-t-white rounded-full animate-spin"></span>
          {{ procesando ? 'Procesando...' : 'Pagar y Confirmar Pedido' }}
        </button>
      </div>
    </div>
  </div>
</template>
