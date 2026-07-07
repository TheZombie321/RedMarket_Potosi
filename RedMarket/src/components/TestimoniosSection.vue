<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { apiFetch } from '../composables/useApi'
import EstrellasDisplay from './EstrellasDisplay.vue'

interface Resena {
  id: number
  puntuacion: number
  comentario: string
  created_at: string
  user: { name: string }
}

const resenas = ref<Resena[]>([])

onMounted(async () => {
  try {
    const resp = await apiFetch('/resenas?per_page=4', { skipAuth: true, silent: true })
    resenas.value = resp.data ?? resp
  } catch {}
})
</script>

<template>
  <section v-if="resenas.length" class="bg-accent-sky-light rounded-xl p-6 sm:p-8 mb-8">
    <h2 class="text-lg font-semibold text-gray-800 mb-5 text-center">Lo que dicen nuestros clientes</h2>
    <div class="flex gap-4 overflow-x-auto snap-x snap-mandatory pb-2 -mx-2 px-2 sm:overflow-visible sm:grid sm:grid-cols-2 lg:grid-cols-4 sm:gap-4">
      <div v-for="r in resenas" :key="r.id"
        class="snap-start shrink-0 w-72 sm:w-auto bg-white rounded-xl p-4 shadow-sm border border-gray-100">
        <EstrellasDisplay :puntuacion="r.puntuacion" size="sm" />
        <p class="text-sm text-gray-600 mt-2 line-clamp-3">"{{ r.comentario }}"</p>
        <p class="text-xs text-gray-400 mt-2 font-medium">— {{ r.user?.name || 'Cliente' }}</p>
      </div>
    </div>
  </section>
</template>
