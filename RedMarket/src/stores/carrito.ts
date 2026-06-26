import { defineStore } from 'pinia'
import { ref, computed, watch } from 'vue'

const STORAGE_KEY = 'redmarket_carrito'

function cargarDelStorage(): any[] {
  try {
    const raw = localStorage.getItem(STORAGE_KEY)
    return raw ? JSON.parse(raw) : []
  } catch {
    return []
  }
}

export const useCarritoStore = defineStore('carrito', () => {
  const items = ref<any[]>(cargarDelStorage())

  watch(items, (val) => {
    localStorage.setItem(STORAGE_KEY, JSON.stringify(val))
  }, { deep: true })

  const agregarAlCarrito = (producto: any) => {
    const existe = items.value.find(item => item.id === producto.id)
    if (existe) {
      existe.cantidad++
    } else {
      items.value.push({ ...producto, cantidad: 1 })
    }
  }

  const agregarConCantidad = (producto: any, cantidad: number) => {
    const existe = items.value.find((item: any) => item.id === producto.id)
    if (existe) {
      existe.cantidad += cantidad
    } else {
      items.value.push({ ...producto, cantidad })
    }
  }

  const eliminarItem = (id: number) => {
    items.value = items.value.filter(item => item.id !== id)
  }

  const vaciarCarrito = () => {
    items.value = []
  }

  const totalPagar = computed(() => {
    return items.value.reduce((total, item) => total + (item.precio_venta * item.cantidad), 0)
  })

  return { items, agregarAlCarrito, agregarConCantidad, eliminarItem, vaciarCarrito, totalPagar }
})
