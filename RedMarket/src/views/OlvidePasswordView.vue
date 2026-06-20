<script setup lang="ts">
import { ref, computed } from 'vue'
import { useRouter } from 'vue-router'
import { useToastStore } from '../stores/toast'
import { apiFetch } from '../composables/useApi'

const router = useRouter()
const toast = useToastStore()

const email = ref('')
const cargando = ref(false)
const enviado = ref(false)
const resetUrl = ref('')
const error = ref('')
const isDev = import.meta.env.DEV

const resetToken = computed(() => {
  if (!resetUrl.value) return ''
  const parts = resetUrl.value.split('?')
  return new URLSearchParams(parts[1] || '').get('token') || ''
})

const submit = async () => {
  cargando.value = true
  error.value = ''
  try {
    const data = await apiFetch<{ message: string; reset_url: string; reset_token: string }>('/olvide-password', {
      method: 'POST',
      skipAuth: true,
      body: JSON.stringify({ email: email.value }),
    })
    resetUrl.value = data.reset_url
    enviado.value = true
    toast.add('Código generado. Revisa el resultado abajo.', 'info')
  } catch (e: any) {
    error.value = e.message
  } finally {
    cargando.value = false
  }
}

const copiarCodigo = () => {
  const params = new URLSearchParams(resetUrl.value)
  navigator.clipboard.writeText(params.get('token') || '')
  toast.add('Código copiado al portapapeles', 'success')
}
</script>

<template>
  <div class="min-h-[70vh] flex items-center justify-center">
    <div class="w-full max-w-sm">
      <div class="bg-white rounded-xl shadow-lg p-8">
        <div class="text-center mb-6">
          <div class="text-4xl mb-3">🔐</div>
          <h1 class="text-xl font-bold text-gray-800">¿Olvidaste tu contraseña?</h1>
          <p class="text-sm text-gray-500 mt-1">Ingresa tu correo y te enviaremos un código de recuperación.</p>
        </div>

        <div v-if="error" class="bg-red-50 border border-red-200 text-red-700 px-4 py-2.5 rounded-lg text-sm mb-4">{{ error }}</div>

        <form v-if="!enviado" @submit.prevent="submit" class="space-y-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Correo electrónico</label>
            <input v-model="email" type="email" required placeholder="tu@correo.com"
              class="w-full px-3 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-red-300 focus:border-red-500" />
          </div>
          <button type="submit" :disabled="cargando"
            class="w-full bg-red-700 text-white py-2.5 rounded-lg font-semibold hover:bg-red-800 disabled:opacity-60 cursor-pointer border-none flex items-center justify-center gap-2">
            <span v-if="cargando" class="w-4 h-4 border-2 border-white/30 border-t-white rounded-full animate-spin"></span>
            {{ cargando ? 'Enviando...' : 'Enviar código' }}
          </button>
        </form>

        <div v-else class="space-y-4">
          <div class="bg-green-50 border border-green-200 rounded-lg p-4 text-sm text-green-700">
            Si el correo existe, recibirás un enlace de recuperación.
          </div>

          <template v-if="isDev">
            <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
              <p class="text-xs text-yellow-700 font-semibold mb-2">🔧 Modo desarrollo — Código generado:</p>
              <div class="flex items-center gap-2">
                <code class="flex-1 text-lg font-bold text-center bg-yellow-100 rounded px-3 py-1">{{ resetToken }}</code>
                <button @click="copiarCodigo" class="text-xs bg-yellow-200 hover:bg-yellow-300 px-2 py-1 rounded cursor-pointer border-none">📋</button>
              </div>
            </div>
          </template>

          <RouterLink :to="{ name: 'reset-password', query: { token: resetToken, email } }"
            class="block w-full text-center bg-red-700 text-white py-2.5 rounded-lg font-semibold hover:bg-red-800 no-underline">
            Continuar
          </RouterLink>
        </div>

        <p class="text-center text-sm text-gray-500 mt-6">
          <RouterLink to="/login" class="text-red-700 hover:text-red-800 font-medium no-underline">Volver a inicio de sesión</RouterLink>
        </p>
      </div>
    </div>
  </div>
</template>
