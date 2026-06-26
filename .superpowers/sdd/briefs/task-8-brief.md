# Task 8: TrackingView.vue — payment badges

## File
- Modify: `RedMarket/src/views/TrackingView.vue`

## Changes

### Step 1: Add payment info in "En Proceso" order cards

Inside each order card in the "En Proceso" section, after the `direccion_texto` line (around line 251), add:
```html
<p><span class="font-medium text-ink">Pago:</span>
  <span class="inline-flex items-center gap-1 text-xs px-2 py-0.5 rounded-full font-medium"
    :class="{
      'bg-green-100 text-green-800': p.payment_status === 'paid',
      'bg-yellow-100 text-yellow-800': p.payment_status === 'unpaid',
      'bg-blue-100 text-blue-800': p.payment_status === 'pending',
      'bg-red-100 text-red-800': p.payment_status === 'failed',
    }">
    {{ p.payment_method === 'stripe' ? '💳' : '💰' }}
    {{ p.payment_status === 'paid' ? 'Pagado' : p.payment_status === 'pending' ? 'Pendiente de pago' : p.payment_status === 'failed' ? 'Pago fallido' : 'Pendiente' }}
  </span>
</p>
```

### Step 2: Add payment badge in historial section

In the historial section, after the `estadoBadge(p.estado)` span (~line 288), add:
```html
<span v-if="p.payment_status === 'paid'" class="text-xs font-medium px-2 py-0.5 rounded-full bg-green-100 text-green-800 ml-1">💳 Pagado</span>
```

### Step 3: Handle success query param on mount

In `<script setup>`, add `useRoute` to the vue-router import:
```ts
import { useRouter } from 'vue-router'
```
→ Change to:
```ts
import { useRouter, useRoute } from 'vue-router'
```

In `onMounted`, before `fetchPedidos()`, add:
```ts
const route = useRoute()
if (route.query.payment === 'success') {
  toast.add('✅ Pago realizado con éxito. Tu pedido está en proceso.', 'success')
  // Clean URL
  window.history.replaceState({}, document.title, window.location.pathname)
}
```
