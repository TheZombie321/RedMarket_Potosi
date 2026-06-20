<script setup lang="ts">
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useToastStore } from '../stores/toast'
import { apiFetch } from '../composables/useApi'
import MapLocationPicker from '../components/MapLocationPicker.vue'

const router = useRouter()
const toast = useToastStore()

const form = ref({ name: '', email: '', telefono: '', password: '', password_confirmation: '' })
const cargando = ref(false)
const error = ref('')

const direccion = ref('')
const latitud = ref<number | null>(null)
const longitud = ref<number | null>(null)

const onLocationSelect = (loc: { direccion: string; lat: number; lng: number }) => {
  direccion.value = loc.direccion
  latitud.value = loc.lat
  longitud.value = loc.lng
}

const submit = async () => {
  cargando.value = true
  error.value = ''
  try {
    await apiFetch('/register', {
      method: 'POST',
      skipAuth: true,
      body: JSON.stringify({
        ...form.value,
        direccion: direccion.value || undefined,
        latitud: latitud.value || undefined,
        longitud: longitud.value || undefined,
      }),
    })
    toast.add('Cuenta creada exitosamente. Ya puedes iniciar sesión.', 'success')
    router.push({ name: 'login' })
  } catch (e: any) {
    error.value = e.message
  } finally {
    cargando.value = false
  }
}
</script>

<template>
  <div class="min-h-[70vh] flex items-center justify-center py-8">
    <div class="w-full max-w-sm">
      <div class="bg-white rounded-xl shadow-lg p-8 overflow-hidden">
        <div class="text-center mb-6">
          <div class="w-14 h-14 bg-red-700 rounded-full flex items-center justify-center mx-auto mb-3">
            <span class="text-2xl text-white font-bold">R</span>
          </div>
          <h1 class="text-xl font-bold text-gray-800">Crear cuenta</h1>
          <p class="text-sm text-gray-500 mt-1">Únete a RedMarket Potosí</p>
        </div>

        <div v-if="error" class="bg-red-50 border border-red-200 text-red-700 px-4 py-2.5 rounded-lg text-sm mb-4">
          {{ error }}
        </div>

        <form @submit.prevent="submit" class="space-y-3">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Nombre completo</label>
            <input v-model="form.name" required placeholder="Juan Pérez"
              class="w-full px-3 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-red-300 focus:border-red-500" />
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Correo electrónico</label>
            <input v-model="form.email" type="email" required placeholder="tu@correo.com"
              class="w-full px-3 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-red-300 focus:border-red-500" />
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Teléfono (opcional)</label>
            <input v-model="form.telefono" type="tel" placeholder="7XXX-XXXX"
              class="w-full px-3 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-red-300 focus:border-red-500" />
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Contraseña</label>
            <input v-model="form.password" type="password" required minlength="8" placeholder="Mín. 8 caracteres"
              class="w-full px-3 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-red-300 focus:border-red-500" />
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Confirmar contraseña</label>
            <input v-model="form.password_confirmation" type="password" required minlength="8" placeholder="Repite la contraseña"
              class="w-full px-3 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-red-300 focus:border-red-500" />
          </div>

          <div class="pt-2">
            <label class="block text-sm font-medium text-gray-700 mb-1">Dirección de entrega (opcional)</label>
            <MapLocationPicker
              :initialAddress="direccion || ''"
              :initialLat="latitud || undefined"
              :initialLng="longitud || undefined"
              @select="onLocationSelect"
            />
          </div>

          <button type="submit" :disabled="cargando"
            class="w-full bg-red-700 text-white py-2.5 rounded-lg font-semibold hover:bg-red-800 disabled:opacity-60 disabled:cursor-not-allowed cursor-pointer border-none flex items-center justify-center gap-2 mt-1">
            <span v-if="cargando" class="w-4 h-4 border-2 border-white/30 border-t-white rounded-full animate-spin"></span>
            {{ cargando ? 'Creando cuenta...' : 'Crear cuenta' }}
          </button>
        </form>

        <p class="text-center text-sm text-gray-500 mt-6">
          ¿Ya tienes cuenta?
          <RouterLink to="/login" class="text-red-700 hover:text-red-800 font-medium no-underline">Inicia sesión</RouterLink>
        </p>
      </div>
    </div>
  </div>
</template>
