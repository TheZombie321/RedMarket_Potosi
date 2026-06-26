<?php

namespace App\Http\Controllers;

use App\Models\Pedido;
use App\Models\Producto;
use App\Traits\RecordsStockMovements;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Stripe\Stripe;
use Stripe\Checkout\Session;

class PedidoController extends Controller
{
    use RecordsStockMovements;
    public function index(Request $request)
    {
        $user = $request->user();

        // Admin/Encargado: ven todo
        if ($user->hasAnyRole(['Administrador', 'Encargado'])) {
            $query = Pedido::with(['items.producto', 'user', 'preparador', 'repartidor']);
            if ($request->has('estado')) {
                $query->where('estado', $request->estado);
            }
            if ($search = $request->input('search')) {
                $query->whereHas('user', fn($q) => $q->where('name', 'like', "%{$search}%"));
            }
            $perPage = min((int) $request->input('per_page', 20), 100);
            return $query->latest()->paginate($perPage);
        }

        // Picking: disponibles + mis pedidos
        if ($user->hasRole('Picking')) {
            $disponibles = Pedido::with(['items.producto', 'user'])
                ->where('estado', 'pendiente')
                ->latest()->get();

            $misPedidos = Pedido::with(['items.producto', 'user'])
                ->where('picking_user_id', $user->id)
                ->latest()->get();

            return ['disponibles' => $disponibles, 'mis_pedidos' => $misPedidos];
        }

        // Repartidor: disponibles + mis pedidos
        if ($user->hasRole('Repartidor')) {
            $disponibles = Pedido::with(['items.producto', 'user'])
                ->where('estado', 'listo_despacho')
                ->latest()->get();

            $misPedidos = Pedido::with(['items.producto', 'user'])
                ->where('repartidor_id', $user->id)
                ->latest()->get();

            return ['disponibles' => $disponibles, 'mis_pedidos' => $misPedidos];
        }

        // Cliente: solo suyos
        $query = Pedido::with(['items.producto', 'user'])->where('user_id', $user->id);
        if ($request->has('estado')) {
            $query->where('estado', $request->estado);
        }
        $perPage = min((int) $request->input('per_page', 20), 100);
        return $query->latest()->paginate($perPage);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'items' => 'required|array|min:1',
            'items.*.producto_id' => 'required|exists:productos,id',
            'items.*.cantidad' => 'required|integer|min:1',
            'direccion_texto' => 'required|string|max:500',
            'latitud' => 'nullable|numeric',
            'longitud' => 'nullable|numeric',
            'payment_method' => 'sometimes|in:cash,stripe',
        ]);

        return DB::transaction(function () use ($request, $validated) {
            $totalProductos = 0;
            $pedidoItems = [];

            foreach ($validated['items'] as $item) {
                $producto = Producto::where('id', $item['producto_id'])->lockForUpdate()->firstOrFail();

                if ($producto->stock_actual < $item['cantidad']) {
                    abort(422, "Stock insuficiente para {$producto->nombre}. Disponible: {$producto->stock_actual}");
                }

                $subtotal = $producto->precio_venta * $item['cantidad'];
                $totalProductos += $subtotal;

                $pedidoItems[] = [
                    'producto_id' => $producto->id,
                    'cantidad' => $item['cantidad'],
                    'precio_unitario' => $producto->precio_venta,
                ];

                $this->recordMovement(
                    $producto, 'egreso', $item['cantidad'],
                    referencia: null, userId: $request->user()->id,
                    motivo: "Pedido - venta"
                );
            }

            $deliveryFee = 5.00;
            $totalFinal = $totalProductos + $deliveryFee;
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

            $pedido->items()->createMany($pedidoItems);

            return $pedido->load(['items.producto', 'user', 'preparador', 'repartidor']);
        });
    }

    public function show(Request $request, Pedido $pedido)
    {
        $this->authorize('view', $pedido);
        return $pedido->load(['items.producto', 'user', 'preparador', 'repartidor']);
    }

    public function update(Request $request, Pedido $pedido)
    {
        $this->authorize('update', $pedido);

        $validated = $request->validate([
            'estado' => 'required|in:pendiente,en_preparacion,listo_despacho,en_camino,entregado,cancelado',
        ]);

        $user = $request->user();
        $nuevoEstado = $validated['estado'];
        $oldEstado = $pedido->estado;

        $allowedTransitions = [
            'pendiente' => ['en_preparacion', 'cancelado'],
            'en_preparacion' => ['listo_despacho', 'cancelado'],
            'listo_despacho' => ['en_camino'],
            'en_camino' => ['entregado'],
            'entregado' => [],
            'cancelado' => [],
        ];

        if ($user->hasAnyRole(['Picking'])) {
            $allowed = ['pendiente' => ['en_preparacion'], 'en_preparacion' => ['listo_despacho']];
            $allowedTransitions = $allowed + ['listo_despacho' => [], 'en_camino' => [], 'entregado' => [], 'cancelado' => []];
        }

        if ($user->hasAnyRole(['Repartidor'])) {
            $allowed = ['listo_despacho' => ['en_camino'], 'en_camino' => ['entregado']];
            $allowedTransitions = $allowed + ['pendiente' => [], 'en_preparacion' => [], 'cancelado' => []];
        }

        // Cliente: solo puede cancelar si está pendiente
        if ($user->id === $pedido->user_id && !$user->hasAnyRole(['Administrador', 'Encargado', 'Picking', 'Repartidor'])) {
            $allowedTransitions = ['pendiente' => ['cancelado']];
        }

        if (!in_array($nuevoEstado, $allowedTransitions[$oldEstado] ?? [])) {
            abort(422, "Transición inválida de '{$oldEstado}' a '{$nuevoEstado}'.");
        }

        $dataToUpdate = ['estado' => $nuevoEstado];

        // Track who prepared/delivered
        if ($nuevoEstado === 'en_preparacion' && !$pedido->picking_user_id) {
            $dataToUpdate['picking_user_id'] = $user->id;
        }
        if ($nuevoEstado === 'en_camino' && !$pedido->repartidor_id) {
            $dataToUpdate['repartidor_id'] = $user->id;
        }

        // Restore stock when cancelled
        if ($nuevoEstado === 'cancelado' && $oldEstado !== 'cancelado') {
            DB::transaction(function () use ($pedido, $dataToUpdate, $user) {
                $pedido->load('items.producto');
                foreach ($pedido->items as $item) {
                    $this->recordMovement(
                        $item->producto, 'ingreso', $item->cantidad,
                        $pedido, $user->id,
                        "Pedido {$pedido->codigo} cancelado"
                    );
                }
                $pedido->update($dataToUpdate);
            });
        } else {
            $pedido->update($dataToUpdate);
        }

        return $pedido->load(['items.producto', 'user', 'preparador', 'repartidor']);
    }

    public function ubicacion(Request $request, Pedido $pedido)
    {
        $user = $request->user();

        if (!$user->hasAnyRole(['Administrador', 'Repartidor'])) {
            abort(403, 'No autorizado.');
        }

        if ($pedido->estado !== 'en_camino') {
            abort(422, 'El pedido no está en camino.');
        }

        if ($pedido->repartidor_id !== $user->id && !$user->hasRole('Administrador')) {
            abort(403, 'No eres el repartidor asignado.');
        }

        $validated = $request->validate([
            'lat' => 'required|numeric',
            'lng' => 'required|numeric',
        ]);

        $pedido->update([
            'ubicacion_actual' => ['lat' => $validated['lat'], 'lng' => $validated['lng']],
        ]);

        return $pedido->load(['items.producto', 'user', 'preparador', 'repartidor']);
    }

    public function createCheckoutSession(Request $request, Pedido $pedido)
    {
        $this->authorize('view', $pedido);

        if ($pedido->payment_method !== 'stripe') {
            abort(422, 'Este pedido no usa pago con tarjeta.');
        }

        if ($pedido->payment_status === 'paid') {
            abort(422, 'Este pedido ya fue pagado.');
        }

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
            abort(422, 'Error Stripe: ' . $e->getMessage());
        }
    }
}
