# Task 6: Routes

## File
- Modify: `redmarket-backend/routes/api.php`

## Changes

Inside `Route::middleware(['auth:sanctum'])->group(function () { ... })`, add:
```php
Route::post('/pedidos/{pedido}/stripe/checkout', [PedidoController::class, 'createCheckoutSession']);
```

Outside the auth group (after the closing `});`), add:
```php
Route::post('/stripe/webhook', [StripeWebhookController::class, 'handle'])->withoutMiddleware(['auth:sanctum']);
```

Also add the import at the top of the file:
```php
use App\Http\Controllers\StripeWebhookController;
```
