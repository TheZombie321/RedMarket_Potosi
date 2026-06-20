<script setup lang="ts">
import { ref } from 'vue'
import { useAuthStore } from '../stores/auth'
import { useRoute, useRouter } from 'vue-router'
import { useToastStore } from '../stores/toast'

const auth = useAuthStore()
const router = useRouter()
const route = useRoute()
const toast = useToastStore()

const form = ref({ email: '', password: '', remember_me: false })
const cargando = ref(false)
const error = ref('')

const submit = async () => {
  cargando.value = true
  error.value = ''
  try {
    await auth.login({
      email: form.value.email,
      password: form.value.password,
      remember_me: form.value.remember_me,
    })
    toast.add('Inicio de sesión exitoso. ¡Bienvenido!', 'success')
    const redirect = (route.query.redirect as string) || 'catalogo'
    router.push({ name: redirect })
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
        <!-- Header -->
        <div class="text-center mb-6">
          <div class="w-14 h-14 bg-red-700 rounded-full flex items-center justify-center mx-auto mb-3">
            <span class="text-2xl text-white font-bold">R</span>
          </div>
          <h1 class="text-xl font-bold text-gray-800">Iniciar sesión</h1>
          <p class="text-sm text-gray-500 mt-1">Accede a tu cuenta RedMarket</p>
        </div>

        <!-- Error -->
        <div v-if="error" class="bg-red-50 border border-red-200 text-red-700 px-4 py-2.5 rounded-lg text-sm mb-4">
          {{ error }}
        </div>

        <!-- Session expired message -->
        <div v-if="route.query.expired === '1'" class="bg-blue-50 border border-blue-200 text-blue-700 px-4 py-2.5 rounded-lg text-sm mb-4">
          Tu sesión ha expirado. Inicia sesión nuevamente.
        </div>

        <form @submit.prevent="submit" class="space-y-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Correo electrónico</label>
            <div class="relative">
              <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400">📧</span>
              <input v-model="form.email" type="email" required autocomplete="email" placeholder="tu@correo.com"
                class="w-full pl-9 pr-3 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-red-300 focus:border-red-500" />
            </div>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Contraseña</label>
            <div class="relative">
              <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400">🔒</span>
              <input v-model="form.password" type="password" required autocomplete="current-password" placeholder="••••••••"
                class="w-full pl-9 pr-3 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-red-300 focus:border-red-500" />
            </div>
          </div>

          <div class="flex items-center justify-between text-sm">
            <label class="flex items-center gap-2 text-gray-600 cursor-pointer">
              <input type="checkbox" v-model="form.remember_me" class="rounded border-gray-300 text-red-700 focus:ring-red-500" />
              Recordarme
            </label>
            <RouterLink to="/olvide-password" class="text-red-700 hover:text-red-800 font-medium no-underline">
              ¿Olvidaste tu contraseña?
            </RouterLink>
          </div>

          <button type="submit" :disabled="cargando"
            class="w-full bg-red-700 text-white py-2.5 rounded-lg font-semibold hover:bg-red-800 disabled:opacity-60 disabled:cursor-not-allowed cursor-pointer border-none flex items-center justify-center gap-2">
            <span v-if="cargando" class="w-4 h-4 border-2 border-white/30 border-t-white rounded-full animate-spin"></span>
            {{ cargando ? 'Iniciando sesión...' : 'Iniciar sesión' }}
          </button>
        </form>

        <p class="text-center text-sm text-gray-500 mt-6">
          ¿No tienes cuenta?
          <RouterLink to="/register" class="text-red-700 hover:text-red-800 font-medium no-underline">Regístrate</RouterLink>
        </p>
      </div>
    </div>
  </div>
</template>
