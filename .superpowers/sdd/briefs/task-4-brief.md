# Task 4: Checkout session endpoint in PedidoController

## File
- Modify: `redmarket-backend/app/Http/Controllers/PedidoController.php`

## Changes

Add `createCheckoutSession()` method at the end of the class (before the closing `}`):

Add imports at the top:
```php
use Stripe\Stripe;
use Stripe\Checkout\Session;
```

Method:
```php
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
