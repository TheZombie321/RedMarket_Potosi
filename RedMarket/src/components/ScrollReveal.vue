<script setup lang="ts">
import { ref, onMounted } from 'vue'

const props = withDefaults(defineProps<{
  delay?: number
}>(), {
  delay: 0,
})

const el = ref<HTMLElement | null>(null)
const visible = ref(false)

onMounted(() => {
  const observer = new IntersectionObserver(
    ([entry]) => {
      if (entry?.isIntersecting) {
        visible.value = true
        observer.disconnect()
      }
    },
    { threshold: 0.1 }
  )
  if (el.value) observer.observe(el.value)
})
</script>

<template>
  <div
    ref="el"
    :class="[
      'reveal-section',
      visible ? 'reveal-visible' : '',
      delay ? `reveal-delay-${delay}` : ''
    ]"
  >
    <slot />
  </div>
</template>
