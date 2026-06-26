import { defineStore } from 'pinia'
import { ref } from 'vue'
import { apiFetch } from '../composables/useApi'

const POTOSI_BOUNDS = { minLat: -19.63, maxLat: -19.54, minLng: -65.78, maxLng: -65.68 }

function clamp(v: number, min: number, max: number) {
  return Math.min(Math.max(v, min), max)
}

export const useDeliveryStore = defineStore('delivery', () => {
  const lat = ref(-19.5836)
  const lng = ref(-65.7531)
  const isTracking = ref(false)
  let intervalId: any = null
  let pedidoId: number | null | undefined = null

  const startTracking = (id?: number) => {
    if (isTracking.value) return
    pedidoId = id ?? null
    isTracking.value = true

    intervalId = setInterval(() => {
      lat.value = clamp(lat.value + (Math.random() - 0.5) * 0.001, POTOSI_BOUNDS.minLat, POTOSI_BOUNDS.maxLat)
      lng.value = clamp(lng.value + (Math.random() - 0.5) * 0.001, POTOSI_BOUNDS.minLng, POTOSI_BOUNDS.maxLng)

      if (pedidoId) {
        apiFetch(`/pedidos/${pedidoId}/ubicacion`, {
          method: 'PUT',
          body: JSON.stringify({ lat: lat.value, lng: lng.value }),
        }).catch(() => {})
      }
    }, 15000)
  }

  const stopTracking = () => {
    isTracking.value = false
    clearInterval(intervalId)
    pedidoId = null
  }

  return { lat, lng, isTracking, startTracking, stopTracking }
})
