<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useToastStore } from '../stores/toast'
import { apiFetch } from '../composables/useApi'

const route = useRoute()
const router = useRouter()
const toast = useToastStore()

const form = ref({ token: '', email: '', password: '', password_confirmation: '' })
const cargando = ref(false)
const error = ref('')
const exito = ref(false)

onMounted(() => {
  form.value.token = (route.query.token as string) || ''
  form.value.email = (route.query.email as string) || ''
  if (route.query.token) {
    window.history.replaceState({}, document.title, window.location.pathname)
  }
})

const submit = async () => {
  cargando.value = true
  error.value = ''
  try {
    await apiFetch('/reset-password', {
      method: 'POST',
      skipAuth: true,
      body: JSON.stringify(form.value),
    })
    exito.value = true
    toast.add('Contraseña actualizada correctamente.', 'success')
    setTimeout(() => router.push({ name: 'login' }), 2000)
  } catch (e: any) {
    error.value = e.message
  } finally {
    cargando.value = false
  }
}
</script>

<template>
  <div class="min-h-[70vh] flex items-center justify-center">
    <div class="w-full max-w-sm">
      <div class="bg-white rounded-xl shadow-lg p-8">
        <div class="text-center mb-6">
          <div class="text-4xl mb-3">🔑</div>
          <h1 class="text-xl font-bold text-gray-800">Restablecer contraseña</h1>
          <p class="text-sm text-gray-500 mt-1">Ingresa tu nuevo código de acceso.</p>
        </div>

        <div v-if="error" class="bg-red-50 border border-red-200 text-red-700 px-4 py-2.5 rounded-lg text-sm mb-4">{{ error }}</div>

        <div v-if="exito" class="bg-green-50 border border-green-200 text-green-700 px-4 py-8 rounded-lg text-center">
          <div class="text-3xl mb-2">✅</div>
          <p class="font-semibold">Contraseña actualizada</p>
          <p class="text-sm mt-1">Redirigiendo al inicio de sesión...</p>
        </div>

        <form v-else @submit.prevent="submit" class="space-y-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
            <input v-model="form.email" type="email" required readonly
              class="w-full px-3 py-2.5 border border-gray-200 rounded-lg text-sm bg-gray-50 text-gray-500 cursor-not-allowed" />
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Código de recuperación</label>
            <input v-model="form.token" required placeholder="000000" maxlength="6"
              class="w-full px-3 py-2.5 border border-gray-300 rounded-lg text-sm text-center text-lg font-bold tracking-[0.5em] focus:outline-none focus:ring-2 focus:ring-red-300 focus:border-red-500" />
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Nueva contraseña</label>
            <input v-model="form.password" type="password" required minlength="8" placeholder="Mín. 8 caracteres"
              class="w-full px-3 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-red-300 focus:border-red-500" />
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Confirmar contraseña</label>
            <input v-model="form.password_confirmation" type="password" required minlength="8" placeholder="Repite la contraseña"
              class="w-full px-3 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-red-300 focus:border-red-500" />
          </div>

          <button type="submit" :disabled="cargando"
            class="w-full bg-red-700 text-white py-2.5 rounded-lg font-semibold hover:bg-red-800 disabled:opacity-60 cursor-pointer border-none flex items-center justify-center gap-2">
            <span v-if="cargando" class="w-4 h-4 border-2 border-white/30 border-t-white rounded-full animate-spin"></span>
            {{ cargando ? 'Restableciendo...' : 'Restablecer contraseña' }}
          </button>
        </form>
      </div>
    </div>
  </div>
</template>
