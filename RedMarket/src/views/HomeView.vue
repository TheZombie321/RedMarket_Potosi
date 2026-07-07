<script setup lang="ts">
import { ref, onMounted } from 'vue'
import HeroCarousel from '../components/HeroCarousel.vue'
import CategoryGrid from '../components/CategoryGrid.vue'
import OfertasSection from '../components/OfertasSection.vue'
import CategoriaBanner from '../components/CategoriaBanner.vue'
import TestimoniosSection from '../components/TestimoniosSection.vue'
import ScrollReveal from '../components/ScrollReveal.vue'
import { apiFetch } from '../composables/useApi'

interface Categoria {
  id: number
  nombre: string
}

const categorias = ref<Categoria[]>([])
const totalProductos = ref(0)
const animateCount = ref(false)

onMounted(async () => {
  try {
    const resp = await apiFetch('/categorias', { skipAuth: true })
    categorias.value = (resp.data ?? resp).slice(0, 3)
  } catch {}

  try {
    const resp = await apiFetch('/productos?per_page=1', { skipAuth: true, silent: true })
    totalProductos.value = resp.total ?? 0
  } catch {}
})
</script>

<template>
  <div>
    <ScrollReveal>
      <HeroCarousel />
    </ScrollReveal>

    <ScrollReveal v-if="categorias.length >= 1" :delay="100">
      <CategoriaBanner
        :categoria-id="categorias[0]!.id"
        :titulo="categorias[0]!.nombre"
        color="from-accent-amber/80 to-red-700/50"
      />
    </ScrollReveal>

    <ScrollReveal :delay="200">
      <CategoryGrid />
    </ScrollReveal>

    <ScrollReveal v-if="categorias.length >= 2" :delay="100">
      <div class="my-6">
        <CategoriaBanner
          :categoria-id="categorias[1]!.id"
          :titulo="categorias[1]!.nombre"
          color="from-accent-sky/80 to-blue-700/50"
        />
      </div>
    </ScrollReveal>

    <ScrollReveal :delay="300">
      <OfertasSection />
    </ScrollReveal>

    <ScrollReveal :delay="200">
      <TestimoniosSection />
    </ScrollReveal>

    <ScrollReveal v-if="categorias.length >= 3" :delay="100">
      <div class="mb-6">
        <CategoriaBanner
          :categoria-id="categorias[2]!.id"
          :titulo="categorias[2]!.nombre"
          color="from-accent-emerald/80 to-green-700/50"
        />
      </div>
    </ScrollReveal>

    <!-- Info section -->
    <ScrollReveal :delay="300">
      <section class="bg-white rounded-xl shadow-sm border border-gray-100 p-8 mb-8">
        <h2 class="text-2xl font-bold text-gray-800 mb-6 text-center">¿Por qué RedMarket Potosí?</h2>
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
          <div class="text-center p-4">
            <div class="w-12 h-12 mx-auto mb-3 rounded-xl bg-accent-amber-light flex items-center justify-center">
              <svg class="w-6 h-6 text-accent-amber" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
              </svg>
            </div>
            <h3 class="font-semibold text-gray-800 mb-1">Entrega a domicilio</h3>
            <p class="text-sm text-gray-500">Recibí tus productos en la puerta de tu casa sin costo adicional.</p>
          </div>
          <div class="text-center p-4">
            <div class="w-12 h-12 mx-auto mb-3 rounded-xl bg-accent-emerald-light flex items-center justify-center">
              <svg class="w-6 h-6 text-accent-emerald" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
              </svg>
            </div>
            <h3 class="font-semibold text-gray-800 mb-1">Pago seguro</h3>
            <p class="text-sm text-gray-500">Pagá con tarjeta o en efectivo contra entrega. Tus datos protegidos.</p>
          </div>
          <div class="text-center p-4">
            <div class="w-12 h-12 mx-auto mb-3 rounded-xl bg-accent-rose-light flex items-center justify-center">
              <svg class="w-6 h-6 text-accent-rose" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16" />
              </svg>
            </div>
            <h3 class="font-semibold text-gray-800 mb-1">Productos frescos</h3>
            <p class="text-sm text-gray-500">Trabajamos con marcas bolivianas de calidad directo a tu mesa.</p>
          </div>
        </div>
      </section>
    </ScrollReveal>

    <!-- CTA -->
    <ScrollReveal :delay="400">
      <div class="text-center mb-8">
        <RouterLink to="/tienda"
          class="inline-block bg-rojo-mercado text-white px-8 py-3 rounded-lg font-semibold text-lg no-underline hover:bg-rojo-mercado-dark transition-colors shadow-md hover:shadow-lg">
          Ver todos los productos →
        </RouterLink>
      </div>
    </ScrollReveal>
  </div>
</template>
