# Stripe Payment Integration Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Add real payment processing via Stripe Checkout while preserving existing cash-on-delivery flow.

**Architecture:** Two-step flow for card payments: (1) order created with `payment_status=pending`, (2) separate endpoint creates Stripe Checkout Session and returns URL for redirect. Webhooks handle async payment confirmation/failure. Cash payments remain unchanged.

**Tech Stack:** Laravel 13, PHP 8.3, Vue 3, Stripe PHP SDK ^16, Stripe Checkout (hosted page)

## Global Constraints

- Backend directory: `redmarket-backend/`
- Frontend directory: `RedMarket/`
- PHP binary: `/mnt/c/laragon/bin/php/php-8.3.30-Win32-vs16-x64/php.exe`
- Artisan prefix: always use full PHP path above
- All model edits must specify `protected $table = 'table_name'`
- Never expose pasillo, nivel, precio_compra to customers
- Frontend uses Composition API with `<script setup lang="ts">`
- Tailwind CSS v4 via `@tailwindcss/vite` plugin
- No commits without explicit user request

---

## File Structure

```
redmarket-backend/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── PedidoController.php        (MODIFY: store + new checkout method)
│   │   │   └── StripeWebhookController.php (CREATE)
│   ├── Models/
│   │   └── Pedido.php                      (MODIFY: fillable + casts)
├── config/
│   ├── app.php                             (MODIFY: add frontend_url)
│   └── stripe.php                          (CREATE)
├── database/
│   └── migrations/
│       └── 2026_06_22_000001_add_payment_fields_to_pedidos_table.php (CREATE)
├── routes/
│   └── api.php                             (MODIFY: +2 routes)
├── .env                                    (MODIFY: +STRIPE_* keys)
└── composer.json                           (MODIFY: +stripe/stripe-php)

RedMarket/
├── src/
│   ├── views/
│   │   ├── CarritoView.vue                 (MODIFY: +payment method selector)
│   │   └── TrackingView.vue                (MODIFY: +payment badges)
├── .env                                    (MODIFY: +VITE_STRIPE_PUBLISHABLE_KEY)
└── package.json                            (MODIFY: no change needed)
```

---

### Task 1: Migration + Model changes

**Files:**
- Create: `redmarket-backend/database/migrations/2026_06_22_000001_add_payment_fields_to_pedidos_table.php`
- Modify: `redmarket-backend/app/Models/Pedido.php`

**Interfaces:**
- Consumes: existing `pedidos` table
- Produces: `Pedido` model with `payment_method`, `payment_status`, `stripe_session_id` fields

- [ ] **Step 1: Create migration**

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pedidos', function (Blueprint $table) {
            $table->string('payment_method', 10)->default('cash');
            $table->string('payment_status', 10)->default('unpaid');
            $table->string('stripe_session_id')->nullable()->unique()->after('payment_status');
        });
    }

    public function down(): void
    {
        Schema::table('pedidos', function (Blueprint $table) {
            $table->dropColumn(['payment_method', 'payment_status', 'stripe_session_id']);
        });
    }
};
```

- [ ] **Step 2: Add fields to Pedido model**

In `redmarket-backend/app/Models/Pedido.php`, add to `$fillable`:
```php
'payment_method', 'payment_status', 'stripe_session_id',
```

In the `$casts` array:
```php
'payment_method' => 'string',
'payment_status' => 'string',
```

- [ ] **Step 3: Run migration**

```bash
/mnt/c/laragon/bin/php/php-8.3.30-Win32-vs16-x64/php.exe artisan migrate
```

Expected output: `Migrating: 2026_06_22_000001_add_payment_fields_to_pedidos_table`

---

### Task 2: Stripe SDK + Configuration

**Files:**
- Modify: `redmarket-backend/composer.json`
- Modify: `redmarket-backend/.env`
- Create: `redmarket-backend/config/stripe.php`
- Modify: `redmarket-backend/config/app.php`
- Modify: `RedMarket/.env`

**Interfaces:**
- Produces: `config('stripe.secret')`, `config('stripe.webhook_secret')`, `config('app.frontend_url')`, `import.meta.env.VITE_STRIPE_PUBLISHABLE_KEY`

- [ ] **Step 1: Install Stripe PHP SDK**

```bash
cd /mnt/e/CARRERA\ INFORMATICA/Taller\ de\ Software/RedMarket/redmarket-backend
/mnt/c/laragon/bin/php/php-8.3.30-Win32-vs16-x64/php.exe composer.phar require stripe/stripe-php:^16.0
```

- [ ] **Step 2: Add Stripe env vars to backend `.env`**

Append to `redmarket-backend/.env`:
```
STRIPE_KEY=pk_test_51...
STRIPE_SECRET=sk_test_51...
STRIPE_WEBHOOK_SECRET=whsec_...
FRONTEND_URL=https://unique-reflection-production-59c9.up.railway.app
```

- [ ] **Step 3: Create `config/stripe.php`**

```php
<?php

return [
    'key' => env('STRIPE_KEY'),
    'secret' => env('STRIPE_SECRET'),
    'webhook_secret' => env('STRIPE_WEBHOOK_SECRET'),
];
```

- [ ] **Step 4: Add `frontend_url` to `config/app.php`**

In the `'timezone' => 'UTC'` area (or after `'url'`), add:
```php
'frontend_url' => env('FRONTEND_URL', 'http://localhost:5173'),
```

- [ ] **Step 5: Add Stripe key to frontend `.env`**

In `RedMarket/.env`:
```
VITE_STRIPE_PUBLISHABLE_KEY=pk_test_51...
```

---

### Task 3: Update PedidoController::store for payment_method

**Files:**
- Modify: `redmarket-backend/app/Http/Controllers/PedidoController.php`

**Interfaces:**
- Consumes: existing `store()` method, Task 1 model changes
- Produces: modified `store()` that accepts `payment_method` and sets `payment_status`

- [ ] **Step 1: Modify validation in `store()`**

Add `'payment_method' => 'sometimes|in:cash,stripe'` to the validation rules:

```php
$validated = $request->validate([
    'items' => 'required|array|min:1',
    'items.*.producto_id' => 'required|exists:productos,id',
    'items.*.cantidad' => 'required|integer|min:1',
    'direccion_texto' => 'required|string|max:500',
    'latitud' => 'nullable|numeric',
    'longitud' => 'nullable|numeric',
    'payment_method' => 'sometimes|in:cash,stripe',
]);
```

- [ ] **Step 2: Set payment_method and payment_status when creating the pedido**

In the `DB::transaction` callback, after `$pedido = Pedido::create([...])`, change the creation array to include:

```php
$paymentMethod = $validated['payment_method'] ?? 'cash';

$pedido = Pedido::create([
    'user_id' => $request->user()->id,
    'estado' => 'pendiente',
    'total_productos' => $totalProductos,
    'delivery_fee' => $deliveryFee,
    'total_final' => $totalFinal,
    'direccion_texto' => $validated['direccion_texto'],
    'latitud' => $validated['latitud'] ?? 0,
    'longitud' => $validated['longitud'] ?? 0,
    'payment_method' => $paymentMethod,
    'payment_status' => $paymentMethod === 'stripe' ? 'pending' : 'unpaid',
]);
```

---

### Task 4: Checkout session endpoint in PedidoController

**Files:**
- Modify: `redmarket-backend/app/Http/Controllers/PedidoController.php`

**Interfaces:**
- Consumes: Task 1 (model fields), Task 2 (stripe config)
- Produces: `POST /api/pedidos/{pedido}/stripe/checkout` returns `{ url: string }`

- [ ] **Step 1: Add `createCheckoutSession()` method**

At the end of `PedidoController.php`, before the closing `}`, add:

```php
<?php
// At the top with other imports
use Stripe\Stripe;
use Stripe\Checkout\Session;

// Inside the class:
public function createCheckoutSession(Request $request, Pedido $pedido)
{
    $this->authorize('view', $pedido);

    if ($pedido->payment_method !== 'stripe') {
        abort(422, 'Este pedido no usa pago con tarjeta.');
    }

    if ($pedido->payment_status === 'paid') {
        abort(422, 'Este pedido ya fue pagado.');
    }

    // Idempotent: return existing session URL if already created
    if ($pedido->stripe_session_id) {
        try {
            Stripe::setApiKey(config('stripe.secret'));
            $existing = Session::retrieve($pedido->stripe_session_id);
            if ($existing->url) {
                return response()->json(['url' => $existing->url]);
            }
        } catch (\Exception $e) {
            // Session expired or invalid, create a new one
        }
    }

    Stripe::setApiKey(config('stripe.secret'));

    try {
        $checkout = Session::create([
            'mode' => 'payment',
            'line_items' => [[
                'price_data' => [
                    'currency' => 'bob',
                    'product_data' => [
                        'name' => "Pedido {$pedido->codigo}",
                    ],
                    'unit_amount' => (int) round($pedido->total_final * 100),
                ],
                'quantity' => 1,
            ]],
            'metadata' => [
                'pedido_id' => $pedido->id,
            ],
            'success_url' => config('app.frontend_url') . '/tracking?payment=success&pedido=' . $pedido->id,
            'cancel_url' => config('app.frontend_url') . '/carrito?payment=cancelled',
        ]);

        $pedido->update([
            'stripe_session_id' => $checkout->id,
        ]);

        return response()->json(['url' => $checkout->url]);
    } catch (\Exception $e) {
        \Log::error('Stripe session creation failed: ' . $e->getMessage());
        abort(422, 'Error al crear la sesión de pago. Intenta de nuevo.');
    }
}
```

---

### Task 5: StripeWebhookController

**Files:**
- Create: `redmarket-backend/app/Http/Controllers/StripeWebhookController.php`

**Interfaces:**
- Consumes: Task 1 (payment_status field), Task 2 (stripe config)
- Produces: `POST /api/stripe/webhook` — processes async payment events

- [ ] **Step 1: Create StripeWebhookController**

`redmarket-backend/app/Http/Controllers/StripeWebhookController.php`:

```php
<?php

namespace App\Http\Controllers;

use App\Models\Pedido;
use Illuminate\Http\Request;
use Stripe\Stripe;
use Stripe\Webhook;
use Illuminate\Support\Facades\DB;

class StripeWebhookController extends Controller
{
    public function handle(Request $request)
    {
        Stripe::setApiKey(config('stripe.secret'));

        $payload = $request->getContent();
        $sigHeader = $request->header('Stripe-Signature');

        try {
            $event = Webhook::constructEvent(
                $payload,
                $sigHeader,
                config('stripe.webhook_secret')
            );
        } catch (\Exception $e) {
            \Log::error('Stripe webhook signature verification failed: ' . $e->getMessage());
            return response()->json(['error' => 'Invalid signature'], 400);
        }

        $session = $event->data->object;

        if ($event->type === 'checkout.session.completed') {
            $this->handleCompleted($session);
        } elseif (in_array($event->type, [
            'checkout.session.expired',
            'checkout.session.async_payment_failed',
        ])) {
            $this->handleFailed($session);
        }

        return response()->json(['status' => 'ok']);
    }

    protected function handleCompleted($session)
    {
        $pedidoId = $session->metadata->pedido_id ?? null;
        if (!$pedidoId) return;

        $pedido = Pedido::find($pedidoId);
        if (!$pedido || $pedido->payment_status === 'paid') return;

        $pedido->update([
            'payment_status' => 'paid',
            'stripe_session_id' => $session->id,
        ]);
    }

    protected function handleFailed($session)
    {
        $pedidoId = $session->metadata->pedido_id ?? null;
        if (!$pedidoId) return;

        DB::transaction(function () use ($pedidoId) {
            $pedido = Pedido::find($pedidoId);
            if (!$pedido || $pedido->payment_status === 'paid') return;

            $pedido->load('items.producto');
            foreach ($pedido->items as $item) {
                $item->producto->increment('stock_actual', $item->cantidad);
            }

            $pedido->update([
                'payment_status' => 'failed',
                'estado' => 'cancelado',
            ]);
        });
    }
}
```

---

### Task 6: Routes

**Files:**
- Modify: `redmarket-backend/routes/api.php`

**Interfaces:**
- Consumes: Task 4 (createCheckoutSession), Task 5 (StripeWebhookController)

- [ ] **Step 1: Add routes to `api.php`**

Inside `Route::middleware(['auth:sanctum'])->group(function () { ... })`, add:
```php
Route::post('/pedidos/{pedido}/stripe/checkout', [PedidoController::class, 'createCheckoutSession']);
```

Outside the auth group (before or after), add the webhook route:
```php
Route::post('/stripe/webhook', [StripeWebhookController::class, 'handle'])->withoutMiddleware(['auth:sanctum']);
```

---

### Task 7: CarritoView.vue — payment method selector

**Files:**
- Modify: `RedMarket/src/views/CarritoView.vue`

**Interfaces:**
- Consumes: Task 4 (checkout endpoint URL)
- Produces: redirect to Stripe Checkout on card payment

- [ ] **Step 1: Add payment method state and selector UI**

Add to `<script setup>`:
```ts
const paymentMethod = ref<'cash' | 'stripe'>('cash')
```

In the template, before the address section or after it, add payment method radio:

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

- [ ] **Step 2: Modify `procesarPago` for stripe flow**

Change the `procesarPago` function to handle both methods:

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

- [ ] **Step 3: Update total section to show payment method note**

In the total section, after the existing text, add a conditional note:

```html
<p v-if="paymentMethod === 'stripe'" class="text-xs text-blue-600 mt-1">
  💳 Serás redirigido a Stripe para completar el pago.
</p>
```

---

### Task 8: TrackingView.vue — payment badges

**Files:**
- Modify: `RedMarket/src/views/TrackingView.vue`

- [ ] **Step 1: Add payment info display in order cards**

Inside each order card (in the "En Proceso" section), after the existing `direccion_texto` line, add:

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

Also add in the `historial` section, after `estadoBadge(p.estado)` span:
```html
<span v-if="p.payment_status === 'paid'" class="text-xs font-medium px-2 py-0.5 rounded-full bg-green-100 text-green-800 ml-1">💳 Pagado</span>
```

- [ ] **Step 2: Handle success query param on mount**

Add to `<script setup>` in `onMounted`:
```ts
onMounted(() => {
  const route = useRoute()
  if (route.query.payment === 'success') {
    toast.add('✅ Pago realizado con éxito. Tu pedido está en proceso.', 'success')
    // Clean URL
    window.history.replaceState({}, document.title, window.location.pathname)
  }
  fetchPedidos()
  interval = setInterval(fetchPedidos, 30000)
})
```

Also add the import at the top:
```ts
import { useRoute } from 'vue-router'
```

---

## Self-Check

**Spec coverage:**
| Spec requirement | Task |
|---|---|
| Migration: payment_method, payment_status, stripe_session_id | Task 1 |
| Pedido model: fillable + casts | Task 1 |
| Install stripe/stripe-php | Task 2 |
| Backend .env + config/stripe.php | Task 2 |
| store() accepts payment_method | Task 3 |
| createCheckoutSession() endpoint | Task 4 |
| StripeWebhookController: completed/failed/expired | Task 5 |
| Webhook signature validation | Task 5 |
| Stock restoration on payment failure | Task 5 (handleFailed) |
| Routes: checkout + webhook | Task 6 |
| CarritoView: payment method selector | Task 7 |
| CarritoView: stripe redirect flow | Task 7 |
| TrackingView: payment badges | Task 8 |
| TrackingView: success query param toast | Task 8 |
| frontend_url config | Task 2 |

**All spec requirements covered ✅**
