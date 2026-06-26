<script setup lang="ts">
import HeroCarousel from '../components/HeroCarousel.vue'
import CategoryGrid from '../components/CategoryGrid.vue'
import OfertasSection from '../components/OfertasSection.vue'
</script>

<template>
  <div>
    <HeroCarousel />
    <CategoryGrid />
    <OfertasSection />

    <!-- Info section -->
    <section class="mt-10 bg-white rounded-xl shadow-sm border border-gray-100 p-8">
      <h2 class="text-2xl font-bold text-gray-800 mb-6 text-center">¿Por qué RedMarket Potosí?</h2>
      <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
        <div class="text-center p-4">
          <div class="text-4xl mb-3">🚚</div>
          <h3 class="font-semibold text-gray-800 mb-1">Entrega a domicilio</h3>
          <p class="text-sm text-gray-500">Recibí tus productos en la puerta de tu casa sin costo adicional.</p>
        </div>
        <div class="text-center p-4">
          <div class="text-4xl mb-3">💳</div>
          <h3 class="font-semibold text-gray-800 mb-1">Pago seguro</h3>
          <p class="text-sm text-gray-500">Pagá con tarjeta o en efectivo contra entrega. Tus datos protegidos.</p>
        </div>
        <div class="text-center p-4">
          <div class="text-4xl mb-3">🥦</div>
          <h3 class="font-semibold text-gray-800 mb-1">Productos frescos</h3>
          <p class="text-sm text-gray-500">Trabajamos con marcas bolivianas de calidad directo a tu mesa.</p>
        </div>
      </div>
    </section>

    <!-- CTA -->
    <div class="mt-8 text-center">
      <RouterLink to="/tienda"
        class="inline-block bg-red-700 text-white px-8 py-3 rounded-lg font-semibold text-lg no-underline hover:bg-red-800 transition-colors">
        Ver todos los productos →
      </RouterLink>
    </div>
  </div>
</template>
