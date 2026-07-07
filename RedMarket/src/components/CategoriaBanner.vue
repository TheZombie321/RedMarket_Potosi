<script setup lang="ts">
import { ref, onMounted, onUnmounted } from 'vue'
import { apiFetch } from '../composables/useApi'

const props = withDefaults(defineProps<{
  categoriaId: number
  titulo: string
  color?: string
}>(), {
  color: 'from-red-800 to-red-600',
})

const imagenes = ref<string[]>([])
const currentIdx = ref(0)
const enlace = ref(`/tienda?categoria=${props.categoriaId}`)
let timer: ReturnType<typeof setInterval> | null = null

onMounted(async () => {
  try {
    const resp = await apiFetch(`/productos?categoria=${props.categoriaId}&per_page=5&random=1`, { skipAuth: true })
    const items: any[] = resp.data ?? resp
    imagenes.value = items.map((p: any) => p.imagen_url).filter(Boolean)
    if (imagenes.value.length > 1) {
      timer = setInterval(() => {
        currentIdx.value = (currentIdx.value + 1) % imagenes.value.length
      }, 4000)
    }
  } catch {}
})

onUnmounted(() => {
  if (timer) clearInterval(timer)
})
</script>

<template>
  <RouterLink :to="enlace"
    class="relative block w-full overflow-hidden rounded-xl no-underline group"
    style="aspect-ratio: 3/1">
    <div class="absolute inset-0">
      <img
        v-for="(img, i) in imagenes.slice(0, 5)" :key="i"
        :src="img" :alt="titulo"
        class="absolute inset-0 w-full h-full object-cover transition-opacity duration-700"
        :class="i === currentIdx ? 'opacity-100' : 'opacity-0'"
      />
    </div>
    <div class="absolute inset-0 bg-gradient-to-r opacity-80" :class="color"></div>
    <div class="absolute inset-0 flex items-center p-6 sm:p-10">
      <div>
        <span class="text-xs uppercase tracking-wider text-white/70">Categoria destacada</span>
        <h3 class="text-xl sm:text-3xl font-bold text-white mt-1">{{ titulo }}</h3>
        <span class="inline-block mt-2 text-sm text-white/90 font-medium border-b border-white/30 pb-0.5 group-hover:border-white transition-colors">
          Ver productos →
        </span>
      </div>
    </div>
  </RouterLink>
</template>