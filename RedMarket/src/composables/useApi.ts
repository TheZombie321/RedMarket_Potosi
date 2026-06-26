import { useAuthStore } from '../stores/auth'
import { useToastStore } from '../stores/toast'

interface ApiOptions extends RequestInit {
  skipAuth?: boolean
  timeout?: number
  silent?: boolean
}

const BASE = import.meta.env.VITE_API_BASE_URL || 'http://127.0.0.1:8000/api';

let isLoggingOut = false

export async function apiFetch<T = any>(endpoint: string, options: ApiOptions = {}): Promise<T> {
  const auth = useAuthStore()
  const toast = useToastStore()

  const headers: Record<string, string> = {
    'Accept': 'application/json',
    ...(options.headers as Record<string, string> || {}),
  }

  if (!options.skipAuth && auth.token) {
    headers['Authorization'] = `Bearer ${auth.token}`
  }

  // No set Content-Type for FormData (browser sets it with boundary)
  if (!(options.body instanceof FormData)) {
    headers['Content-Type'] = headers['Content-Type'] || 'application/json'
  }

  const timeoutSignal = options.timeout ? AbortSignal.timeout(options.timeout) : undefined
  const combinedSignal = options.signal && timeoutSignal
    ? AbortSignal.any([options.signal, timeoutSignal])
    : (timeoutSignal || (options.signal as AbortSignal))

  const res = await fetch(`${BASE}${endpoint}`, {
    ...options,
    signal: combinedSignal,
    headers,
  })

  // 401 — token expirado o inválido
  // skipAuth: request is unauthenticated (login/register) → don't touch session
  // silent: caller handles 401 itself (refreshUser) → don't touch session
  if (res.status === 401 && auth.token && !isLoggingOut && !options.silent && !options.skipAuth) {
    isLoggingOut = true
    auth.user = null
    auth.token = ''
    localStorage.removeItem('user')
    localStorage.removeItem('token')
    toast.add('Tu sesión ha expirado. Inicia sesión nuevamente.', 'error', 5000)
    window.location.href = '/login?expired=1'
    setTimeout(() => { isLoggingOut = false }, 2000)
    throw new Error('Tu sesión ha expirado. Inicia sesión nuevamente.')
  }

  // 429 — rate limit
  if (res.status === 429) {
    const data = await res.json().catch(() => ({}))
    toast.add(data.message || 'Demasiadas solicitudes. Intenta más tarde.', 'error', 5000)
    throw new Error(data.message || 'Rate limited')
  }

  // 422 — validación
  if (res.status === 422) {
    const data = await res.json().catch(() => ({}))
    const msg = data.message || Object.values(data.errors || {}).flat().join(', ') || 'Error de validación'
    throw new Error(msg)
  }

  const data = await res.json().catch(() => null)

  if (!res.ok) {
    throw new Error(data?.message || `Error ${res.status}`)
  }

  return data as T
}
