import { defineStore } from 'pinia'
import { ref } from 'vue'
import { apiFetch } from '../composables/useApi'

const BASE = 'http://127.0.0.1:8000/api'

export const useAuthStore = defineStore('auth', () => {
  const user = ref<any>(() => {
    try { return JSON.parse(localStorage.getItem('user') || 'null') } catch { return null }
  })
  const token = ref(localStorage.getItem('token') || '')

  const login = async (credentials: any) => {
    const data = await apiFetch('/login', {
      method: 'POST',
      skipAuth: true,
      body: JSON.stringify(credentials),
    })
    user.value = data.user
    token.value = data.token
    localStorage.setItem('user', JSON.stringify(data.user))
    localStorage.setItem('token', data.token)
  }

  const register = async (data: { name: string; email: string; password: string; password_confirmation: string }) => {
    const result = await apiFetch('/register', {
      method: 'POST',
      skipAuth: true,
      body: JSON.stringify(data),
    })
    user.value = result.user
    token.value = result.token
    localStorage.setItem('user', JSON.stringify(result.user))
    localStorage.setItem('token', result.token)
  }

  // logout usa fetch nativo para NO disparar el handler de 401 de apiFetch
  const logout = async (revokeAll = false) => {
    const currentToken = token.value
    // Limpiar estado local PRIMERO
    user.value = null
    token.value = ''
    localStorage.removeItem('user')
    localStorage.removeItem('token')
    // Intentar revocar token en servidor (sin await, fire-and-forget)
    if (currentToken) {
      fetch(`${BASE}${revokeAll ? '/logout-all' : '/logout'}`, {
        method: 'POST',
        headers: {
          'Accept': 'application/json',
          'Authorization': `Bearer ${currentToken}`,
        },
      }).catch(() => {})
    }
  }

  const refreshUser = async () => {
    try {
      const data = await apiFetch<{ user: any }>('/user')
      user.value = data.user
      localStorage.setItem('user', JSON.stringify(data.user))
    } catch {
      // silently fail
    }
  }

  const updateUser = (data: any) => {
    user.value = data
    localStorage.setItem('user', JSON.stringify(data))
  }

  return { user, token, login, register, logout, refreshUser, updateUser }
})
