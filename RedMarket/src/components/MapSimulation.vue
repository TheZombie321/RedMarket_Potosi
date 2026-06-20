<template>
  <div class="map-simulation">
    <h3>Simulación de Ruta en Tiempo Real</h3>
    <div class="map-box">
      <div class="delivery-icon" :style="{ top: `${(lat + 17.79) * 5000}px`, left: `${(lng + 63.19) * 5000}px` }">
        🚚
      </div>
    </div>
    <p>Latitud: {{ lat.toFixed(4) }}</p>
    <p>Longitud: {{ lng.toFixed(4) }}</p>
  </div>
</template>

<script setup lang="ts">
import { useDeliveryStore } from '../stores/deliveryStore'
import { storeToRefs } from 'pinia'
import { onMounted, onUnmounted } from 'vue'

const store = useDeliveryStore()
const { lat, lng } = storeToRefs(store)

onMounted(() => {
  store.startTracking(0)
})

onUnmounted(() => {
  store.stopTracking()
})
</script>

<style scoped>
.map-box {
  width: 300px;
  height: 300px;
  border: 2px solid var(--color-ink, #374151);
  position: relative;
  background: var(--color-potosí-stone, #f3f4f6);
  margin: 10px auto;
}
.delivery-icon {
  position: absolute;
  font-size: 24px;
  transition: all 2s linear;
}
</style>
