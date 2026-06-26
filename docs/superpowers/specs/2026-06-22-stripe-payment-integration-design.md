# Stripe Payment Integration — Design Spec

> **Status:** Draft  
> **Date:** 2026-06-22  
> **Project:** RedMarket Potosí — Virtual Supermarket

## Goal

Add real payment processing to RedMarket via Stripe, while preserving the existing cash-on-delivery flow. Customers choose between **Efectivo** (current free flow) or **Tarjeta** (Stripe Checkout) when placing an order.

---

## Architecture

```
                    ┌──────────────────────┐
                    │    Stripe Checkout   │
                    │   (hosted page)      │
                    └──────┬───────────────┘
                           │ redirect (success/cancel)
                           ▼
┌─────────────┐   POST    ┌──────────────────────┐   webhook   ┌──────────────┐
│ CarritoView │ ────────▶ │  Laravel Backend     │ ◀───────────│ Stripe API   │
│ (Vue SPA)   │           │  /api/pedidos/*      │             │              │
└─────────────┘           │  /api/stripe/*       │             └──────────────┘
                          └──────────────────────┘
```

- **Efectivo:** order created immediately → `pendiente` (unchanged).
- **Tarjeta:** order created first (`payment_status=pending`), Stripe Checkout session generated, user redirected. Webhook flips `payment_status=paid` or `failed`.

---

## Data Model

### Migration: `add_payment_fields_to_pedidos_table`

```php
Schema::table('pedidos', function (Blueprint $table) {
    $table->string('payment_method', 10)->default('cash');     // cash | stripe
    $table->string('payment_status', 10)->default('unpaid');   // unpaid | pending | paid | failed
    $table->string('stripe_session_id')->nullable()->unique();
});
```

### Pedido model — new casts + fillable

```php
protected $fillable = [
    // … existing fields …
    'payment_method', 'payment_status', 'stripe_session_id',
];

protected $casts = [
    // … existing casts …
    'payment_method' => 'string',
    'payment_status' => 'string',
];
```

---

## Backend — New Endpoints

### `POST /api/pedidos` — modified

Accept new optional field `payment_method`:

```json
{
  "items": [...],
  "direccion_texto": "...",
  "payment_method": "stripe" | "cash"
}
```

- `payment_method` defaults to `'cash'`
- If `cash`: `payment_status = 'unpaid'`, order returned normally
- If `stripe`: `payment_status = 'pending'`, order created, returns order **without** checkout URL

### `POST /api/pedidos/{pedido}/stripe/checkout`

Creates a Stripe Checkout Session for an existing order. Idempotent: if pedido already has a `stripe_session_id`, returns existing session URL instead of creating a new one.

```php
$checkout = Session::create([
    'mode' => 'payment',
    'line_items' => [[
        'price_data' => [
            'currency' => 'bob',
            'product_data' => ['name' => "Pedido {$pedido->codigo}"],
            'unit_amount' => (int) ($pedido->total_final * 100),
        ],
        'quantity' => 1,
    ]],
    'metadata' => ['pedido_id' => $pedido->id],
    'success_url' => config('app.frontend_url') . '/tracking?payment=success&pedido=' . $pedido->id,
    'cancel_url'  => config('app.frontend_url') . '/carrito?payment=cancelled',
]);
```

### `POST /api/stripe/webhook` — no auth

- Validates Stripe signature via `\Stripe\Webhook::constructEvent()`
- Listens for: `checkout.session.completed`, `checkout.session.expired`, `checkout.session.async_payment_failed`

**On `completed`:** Update `payment_status = 'paid'`, store `stripe_session_id` on the pedido.

**On `expired` / `failed`:** Update `payment_status = 'failed'`, restore stock (same logic as cancellation), cancel the order.

### `GET /api/stripe/success` — Página de retorno

Opcional: podría redirigir o servir como landing page simple.

---

## Frontend — Changes

### CarritoView.vue

- Add radio buttons: **Efectivo** / **Tarjeta** (Stripe) at checkout section
- When "Pagar y Confirmar Pedido" is clicked with `stripe`:
  1. POST `/pedidos` with `payment_method: 'stripe'` → receives `pedido.id`
  2. POST `/pedidos/{pedido.id}/stripe/checkout` → receives `url`
  3. `window.location.href = url` (redirects to Stripe Checkout)
- When `cash`: existing flow unchanged

### TrackingView.vue

- Show payment badge: `paid` (green), `unpaid` (yellow), `pending` (blue), `failed` (red)
- Show payment method icon (💰 cash / 💳 card)
- After Stripe redirect, check URL params for `payment=success` → show toast

### .env

```env
VITE_STRIPE_PUBLISHABLE_KEY=pk_test_...
VITE_FRONTEND_URL=https://unique-reflection-production-59c9.up.railway.app
```

---

## Stripe Configuration

### Backend

```env
STRIPE_KEY=pk_test_...
STRIPE_SECRET=sk_test_...
STRIPE_WEBHOOK_SECRET=whsec_...
```

### composer.json

```json
"require": {
    "stripe/stripe-php": "^16"
}
```

### routes/api.php

```php
Route::post('/pedidos/{pedido}/stripe/checkout', [PedidoController::class, 'createCheckoutSession']);
Route::post('/stripe/webhook', [StripeWebhookController::class, 'handle'])->withoutMiddleware(['auth:sanctum']);
```

---

## Webhook Security

- Endpoint is public (no `auth:sanctum`)
- Protected by Stripe signature verification
- Idempotency: `stripe_session_id` unique index prevents double-processing
- Rate limit: webhooks are infrequent so no extra rate limiting needed

---

## Error Handling

| Scenario | Behavior |
|---|---|
| Stripe session creation fails | Return 422, pedido created with `payment_status=failed`, stock restored |
| User cancels on Stripe page | Redirect to `/carrito`, pedido stays `pending`, stock already decremented (auto-cancel via webhook after 24h or manual cancel) |
| Webhook signature invalid | Return 400, Stripe retries |
| Duplicate webhook event | `stripe_session_id` unique constraint → ignore second event |

---

## Order Lifecycle with Payments

### Efectivo (unchanged)

```
pendiente → en_preparacion → listo_despacho → en_camino → entregado
payment_status: unpaid (stays unpaid throughout)
```

### Tarjeta

```
[POST] payment_status=pending, estado=pendiente
[Stripe] → usuario paga
[Webhook] → payment_status=paid
estado: pendiente → en_preparacion → listo_despacho → en_camino → entregado

If webhook fails:
[Webhook expired/failed] → payment_status=failed, estado=cancelado, stock restored
```

---

## Out of Scope (Phase 1)

- Refunds / Stripe Dashboard integration
- Recurring subscriptions
- Multiple payment methods per order
- Saved cards / customers (no `stripe_customer_id` yet)
- Real-time payment status via WebSockets (polling is sufficient)

---

## Future Considerations

- Stripe Customer creation for repeat buyers
- Metadata for per-item breakdown in Stripe dashboard
