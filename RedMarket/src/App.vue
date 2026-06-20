<script setup lang="ts">
import { useCarritoStore } from './stores/carrito'
import { useSearchStore } from './stores/search'
import { useAuthStore } from './stores/auth'
import { useToastStore } from './stores/toast'
import { useRouter, useRoute } from 'vue-router'
import { ref, computed, onMounted, onUnmounted, watch } from 'vue'
import { useSessionTimeout } from './composables/useSessionTimeout'

const carrito = useCarritoStore()
const search = useSearchStore()
const auth = useAuthStore()
const toast = useToastStore()
const router = useRouter()
const route = useRoute()

const menuOpen = ref(false)
const isAdmin = computed(() => auth.user?.roles?.some((r: any) => r.name === 'Administrador'))
const canPick = computed(() => auth.user?.roles?.some((r: any) => ['Administrador', 'Encargado', 'Picking'].includes(r.name)))
const canDeliver = computed(() => auth.user?.roles?.some((r: any) => ['Administrador', 'Repartidor'].includes(r.name)))
const showSidebar = computed(() => canPick.value || canDeliver.value || isAdmin.value)
const isStaff = computed(() => auth.user?.roles?.some((r: any) => ['Administrador', 'Encargado', 'Picking', 'Repartidor'].includes(r.name)))

const { showWarning, extendSession, resetTimer, start: startTimeout, stop: stopTimeout } = useSessionTimeout()

const handleLogout = async () => {
  menuOpen.value = false
  await auth.logout()
  router.push('/')
}

const handleLogoutAll = async () => {
  menuOpen.value = false
  await auth.logout(true)
  router.push('/')
}

const cerrarMenu = () => { menuOpen.value = false }

watch(() => auth.token, (val) => {
  if (val) startTimeout()
  else stopTimeout()
})

onMounted(() => {
  document.addEventListener('click', cerrarMenu)
  if (auth.token) {
    auth.refreshUser()
    startTimeout()
  }
})

onUnmounted(() => {
  document.removeEventListener('click', cerrarMenu)
  stopTimeout()
})
</script>

<template>
  <div class="min-h-screen bg-gray-100 flex flex-col">
    <!-- Top Header -->
    <header class="bg-white border-b border-gray-200 sticky top-0 z-50">
      <div class="max-w-7xl mx-auto px-4 h-14 flex items-center justify-between">
        <div class="flex items-center gap-6">
          <RouterLink to="/" class="flex items-center gap-1 no-underline">
            <span class="text-xl font-bold text-red-700">RedMarket</span>
            <span class="text-xs text-gray-500">Potosí</span>
          </RouterLink>

          <nav class="hidden sm:flex items-center gap-4 text-sm">
            <RouterLink to="/" class="text-gray-700 hover:text-red-700 no-underline font-medium">Tienda</RouterLink>
            <input v-model="search.query" @input="router.push('/')" placeholder="Buscar productos…" class="w-48 lg:w-64 px-3 py-1.5 border border-gray-300 rounded text-sm text-gray-700 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-red-300 focus:border-red-500" />
          </nav>
        </div>

        <div class="flex items-center gap-4">
          <!-- User dropdown -->
          <div v-if="auth.user" class="relative" @click.stop>
            <button @click="menuOpen = !menuOpen"
              class="flex items-center gap-2 text-sm text-gray-700 hover:text-red-700 font-medium cursor-pointer bg-transparent border-none px-2 py-1 rounded hover:bg-gray-100">
              <span class="w-7 h-7 rounded-full bg-red-700 text-white flex items-center justify-center text-xs font-bold">{{ auth.user.name?.charAt(0)?.toUpperCase() }}</span>
              <span class="hidden sm:inline">{{ auth.user.name }}</span>
              <span class="text-xs">▾</span>
            </button>
            <div v-if="menuOpen"
              class="absolute right-0 mt-1 w-52 bg-white border border-gray-200 rounded-lg shadow-lg py-1 z-50">
              <div class="px-4 py-2 text-xs text-gray-400 border-b border-gray-100 truncate">{{ auth.user.email }}</div>
              <RouterLink to="/perfil" @click="menuOpen = false"
                class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 no-underline">👤 Mi Perfil</RouterLink>
              <RouterLink v-if="!isStaff" to="/tracking" @click="menuOpen = false"
                class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 no-underline">📦 Mis Pedidos</RouterLink>
              <hr class="border-gray-100">
              <button @click="handleLogout"
                class="w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-red-50 font-medium cursor-pointer bg-transparent border-none">🚪 Cerrar sesión</button>
              <button v-if="isStaff" @click="handleLogoutAll"
                class="w-full text-left px-4 py-2 text-xs text-gray-500 hover:bg-gray-50 cursor-pointer bg-transparent border-none">Cerrar sesión en todos los dispositivos</button>
            </div>
          </div>

          <RouterLink v-else to="/login" class="text-sm text-gray-700 hover:text-red-700 no-underline font-medium">
            Iniciar sesión
          </RouterLink>

          <!-- Cart (solo para clientes, no staff) -->
          <RouterLink v-if="!isStaff" to="/carrito"
            class="relative flex items-center gap-1 bg-gray-800 text-white px-3 py-1.5 rounded text-sm no-underline hover:bg-gray-700">
            <span>🛒</span>
            <span v-if="carrito.items.length" class="absolute -top-2 -right-2 bg-red-600 text-white text-xs w-5 h-5 rounded-full flex items-center justify-center">
              {{ carrito.items.reduce((t: number, i: any) => t + i.cantidad, 0) }}
            </span>
          </RouterLink>
        </div>
      </div>
    </header>

    <!-- Session timeout warning modal -->
    <Teleport to="body">
      <Transition name="modal">
        <div v-if="showWarning" class="fixed inset-0 z-[9998] flex items-center justify-center bg-black/40" @click.self="extendSession">
          <div class="modal-panel bg-white rounded-xl shadow-2xl max-w-sm w-full mx-4 p-6 text-center">
            <div class="text-4xl mb-3">⏰</div>
            <h3 class="text-lg font-bold text-gray-800 mb-2">¿Sigues ahí?</h3>
            <p class="text-sm text-gray-500 mb-5">Tu sesión está por expirar por inactividad.</p>
            <button @click="extendSession"
              class="w-full bg-red-700 text-white py-2.5 rounded-lg font-semibold hover:bg-red-800 cursor-pointer border-none">
              Continuar sesión
            </button>
          </div>
        </div>
      </Transition>
    </Teleport>

    <!-- Body -->
    <div class="flex-1 flex max-w-7xl mx-auto w-full px-4 py-6 gap-6" :class="{ 'flex-row-reverse': showSidebar }">
      <main class="flex-1 min-w-0">
        <RouterView />
      </main>

      <aside v-if="showSidebar" class="w-56 shrink-0">
        <div class="bg-white rounded-lg shadow p-4 space-y-1 sticky top-20">
          <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-3">Administración</p>

          <RouterLink v-if="canPick" to="/picking"
            class="flex items-center gap-2 px-3 py-2 rounded text-sm no-underline text-amber-700 hover:bg-amber-50 font-medium">
            <span>📋</span> Picking
          </RouterLink>

          <RouterLink v-if="canDeliver" to="/repartidor"
            class="flex items-center gap-2 px-3 py-2 rounded text-sm no-underline text-blue-700 hover:bg-blue-50 font-medium">
            <span>🚚</span> Repartidor
          </RouterLink>

          <RouterLink v-if="isAdmin" to="/admin"
            class="flex items-center gap-2 px-3 py-2 rounded text-sm no-underline text-red-700 hover:bg-red-50 font-medium">
            <span>⚙️</span> Admin
          </RouterLink>
        </div>
      </aside>
    </div>

    <!-- Toast container -->
    <div class="fixed top-4 right-4 z-[9999] space-y-2 w-80">
      <div v-for="t in toast.toasts" :key="t.id"
        class="px-4 py-3 rounded-lg shadow-lg text-white text-sm font-medium flex items-center gap-2 animate-slide-in cursor-pointer"
        :class="t.type === 'success' ? 'bg-green-600' : t.type === 'error' ? 'bg-red-600' : 'bg-blue-600'"
        @click="toast.remove(t.id)">
        <span>{{ t.type === 'success' ? '✅' : t.type === 'error' ? '❌' : 'ℹ️' }}</span>
        {{ t.message }}
      </div>
    </div>
  </div>
</template>

<style scoped>
@keyframes slide-in {
  from { transform: translateX(100%); opacity: 0; }
  to { transform: translateX(0); opacity: 1; }
}
.animate-slide-in { animation: slide-in 0.3s ease-out; }
</style>
