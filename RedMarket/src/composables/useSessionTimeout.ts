import { ref, onMounted, onUnmounted } from 'vue'
import { useAuthStore } from '../stores/auth'
import { useRouter } from 'vue-router'
import { useToastStore } from '../stores/toast'

const INACTIVITY_TIMEOUT = 60 * 60 * 1000 // 60 min
const WARNING_BEFORE = 2 * 60 * 1000 // 2 min antes

export function useSessionTimeout() {
  const auth = useAuthStore()
  const router = useRouter()
  const toast = useToastStore()

  const showWarning = ref(false)
  let timer: ReturnType<typeof setTimeout> | null = null
  let warningTimer: ReturnType<typeof setTimeout> | null = null

  const clearAllTimers = () => {
    if (timer) { clearTimeout(timer); timer = null }
    if (warningTimer) { clearTimeout(warningTimer); warningTimer = null }
  }

  const resetTimer = () => {
    if (!auth.token) return
    clearAllTimers()
    showWarning.value = false

    warningTimer = setTimeout(() => {
      showWarning.value = true
    }, INACTIVITY_TIMEOUT - WARNING_BEFORE)

    timer = setTimeout(() => {
      doLogout()
    }, INACTIVITY_TIMEOUT)
  }

  const doLogout = () => {
    clearAllTimers()
    showWarning.value = false
    auth.logout()
    router.push({ name: 'login', query: { expired: '1' } })
    toast.add('Tu sesión ha expirado por inactividad.', 'info', 5000)
  }

  const extendSession = () => {
    resetTimer()
    toast.add('Sesión extendida.', 'success', 2000)
  }

  // Usar capture para capturar clicks en elementos hijos (Leaflet, etc.)
  const events = ['mousemove', 'keydown', 'click', 'scroll', 'touchstart', 'mousedown']

  const handleEvent = () => { resetTimer() }

  const start = () => {
    if (!auth.token) return
    events.forEach(e => window.addEventListener(e, handleEvent, { passive: true }))
    resetTimer()
  }

  const stop = () => {
    events.forEach(e => window.removeEventListener(e, handleEvent))
    clearAllTimers()
    showWarning.value = false
  }

  return { showWarning, extendSession, resetTimer, start, stop }
}
