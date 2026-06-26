# Task 3: Update PedidoController::store for payment_method

## File
- Modify: `redmarket-backend/app/Http/Controllers/PedidoController.php`

## Changes

### Step 1: Modify validation in `store()`

Add `'payment_method' => 'sometimes|in:cash,stripe'` to the validation rules array.

The current validation is:
```php
$validated = $request->validate([
    'items' => 'required|array|min:1',
    'items.*.producto_id' => 'required|exists:productos,id',
    'items.*.cantidad' => 'required|integer|min:1',
    'direccion_texto' => 'required|string|max:500',
    'latitud' => 'nullable|numeric',
    'longitud' => 'nullable|numeric',
]);
```

Add `'payment_method' => 'sometimes|in:cash,stripe',` after `'longitud'`.

### Step 2: Set payment_method and payment_status when creating the pedido

In the `DB::transaction` callback, before the `Pedido::create([...])` call, add:
```php
$paymentMethod = $validated['payment_method'] ?? 'cash';
```

In the `Pedido::create([...])` array, add:
```php
'payment_method' => $paymentMethod,
'payment_status' => $paymentMethod === 'stripe' ? 'pending' : 'unpaid',
```
