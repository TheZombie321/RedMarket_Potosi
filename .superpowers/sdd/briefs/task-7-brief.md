# Task 7: CarritoView.vue — payment method selector

## File
- Modify: `RedMarket/src/views/CarritoView.vue`

## Changes

### Step 1: Add payment method state

In `<script setup>`, add after `const error = ref('')`:
```ts
const paymentMethod = ref<'cash' | 'stripe'>('cash')
```

### Step 2: Add payment method selector UI in template

Before the address section (before `<!-- Address section -->` div), add:
```html
<div class="mt-4 pt-4 border-t border-gray-200">
  <h3 class="font-semibold text-gray-800 mb-3">💳 Método de pago</h3>
  <div class="space-y-2">
    <label class="flex items-center gap-2 text-sm cursor-pointer">
      <input type="radio" value="cash" v-model="paymentMethod" class="accent-red-700" />
      <span>💰 Efectivo — Paga al recibir</span>
    </label>
    <label class="flex items-center gap-2 text-sm cursor-pointer">
      <input type="radio" value="stripe" v-model="paymentMethod" class="accent-red-700" />
      <span>💳 Tarjeta — Paga ahora con Stripe</span>
    </label>
  </div>
</div>
```

### Step 3: Modify `procesarPago` for stripe flow

Replace the entire `procesarPago` function body. After creating the pedido, if payment is `stripe`, create a checkout session and redirect to Stripe URL. If `cash`, show toast and redirect to tracking as before:

```ts
const procesarPago = async () => {
  if (carrito.items.length === 0) return
  const dir = direccionFinal()
  if (!dir.trim()) {
    error.value = 'Configura tu dirección en tu perfil antes de hacer un pedido.'
    return
  }

  procesando.value = true
  error.value = ''
  try {
    const data = await apiFetch<any>('/pedidos', {
      method: 'POST',
      body: JSON.stringify({
        items: carrito.items.map(i => ({ producto_id: i.id, cantidad: i.cantidad })),
        direccion_texto: dir,
        latitud: auth.user?.latitud || undefined,
        longitud: auth.user?.longitud || undefined,
        payment_method: paymentMethod.value,
      }),
    })

    if (paymentMethod.value === 'cash') {
      toast.add(`Pedido ${data.codigo || '#' + data.id} creado. Total: Bs. ${data.total_final}`, 'success')
      carrito.vaciarCarrito()
      router.push('/tracking')
    } else {
      // Stripe: get checkout URL and redirect
      const session = await apiFetch<{ url: string }>(`/pedidos/${data.id}/stripe/checkout`, {
        method: 'POST',
      })
      carrito.vaciarCarrito()
      window.location.href = session.url
    }
  } catch (e: any) {
    error.value = e.message
  } finally {
    procesando.value = false
  }
}
```

### Step 4: Add payment method note in total section

In the total section, after the Total line, add:
```html
<p v-if="paymentMethod === 'stripe'" class="text-xs text-blue-600 mt-1">
  💳 Serás redirigido a Stripe para completar el pago.
</p>
```
