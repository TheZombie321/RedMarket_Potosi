<?php

namespace App\Http\Controllers;

use App\Models\OrdenCompra;
use App\Models\Producto;
use App\Traits\RecordsStockMovements;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrdenCompraController extends Controller
{
    use RecordsStockMovements;

    public function index(Request $request)
    {
        $user = $request->user();
        if (!$user->hasAnyRole(['Administrador', 'Encargado'])) {
            abort(403);
        }

        $query = OrdenCompra::with(['proveedor', 'user', 'items.producto']);

        if ($search = $request->input('search')) {
            $query->where('codigo', 'like', "%{$search}%");
        }

        if ($estado = $request->input('estado')) {
            $query->where('estado', $estado);
        }

        return $query->latest()->paginate(20);
    }

    public function store(Request $request)
    {
        $user = $request->user();
        if (!$user->hasAnyRole(['Administrador', 'Encargado'])) {
            abort(403);
        }

        $validated = $request->validate([
            'proveedor_id' => 'required|exists:proveedores,id',
            'notas' => 'nullable|string|max:1000',
            'items' => 'required|array|min:1',
            'items.*.producto_id' => 'required|exists:productos,id',
            'items.*.cantidad' => 'required|integer|min:1',
            'items.*.precio_unitario' => 'required|numeric|min:0',
        ]);

        return DB::transaction(function () use ($validated, $user) {
            $total = 0;
            $itemsData = [];

            foreach ($validated['items'] as $item) {
                $subtotal = $item['cantidad'] * $item['precio_unitario'];
                $total += $subtotal;
                $itemsData[] = [
                    'producto_id' => $item['producto_id'],
                    'cantidad' => $item['cantidad'],
                    'precio_unitario' => $item['precio_unitario'],
                    'subtotal' => $subtotal,
                ];
            }

            $orden = OrdenCompra::create([
                'codigo' => 'OC-' . str_pad((OrdenCompra::max('id') ?? 0) + 1, 4, '0', STR_PAD_LEFT),
                'proveedor_id' => $validated['proveedor_id'],
                'user_id' => $user->id,
                'estado' => 'pendiente',
                'notas' => $validated['notas'] ?? null,
                'total' => $total,
            ]);

            $orden->items()->createMany($itemsData);

            return $orden->load(['proveedor', 'user', 'items.producto']);
        });
    }

    public function show(Request $request, OrdenCompra $ordenCompra)
    {
        if (!$request->user()->hasAnyRole(['Administrador', 'Encargado'])) {
            abort(403);
        }
        return $ordenCompra->load(['proveedor', 'user', 'items.producto']);
    }

    public function update(Request $request, OrdenCompra $ordenCompra)
    {
        $user = $request->user();
        if (!$user->hasAnyRole(['Administrador', 'Encargado'])) {
            abort(403);
        }

        $validated = $request->validate([
            'estado' => 'required|in:pendiente,enviada,recibida,cancelada',
        ]);

        $oldEstado = $ordenCompra->estado;
        $newEstado = $validated['estado'];

        $allowed = [
            'pendiente' => ['enviada', 'cancelada'],
            'enviada' => ['recibida', 'cancelada'],
            'recibida' => [],
            'cancelada' => [],
        ];

        if (!in_array($newEstado, $allowed[$oldEstado] ?? [])) {
            abort(422, "Transición inválida de '{$oldEstado}' a '{$newEstado}'.");
        }

        return DB::transaction(function () use ($ordenCompra, $newEstado, $user) {
            // Al recibir, aumentar stock y registrar movimientos
            if ($newEstado === 'recibida' && $ordenCompra->estado !== 'recibida') {
                $ordenCompra->load('items.producto');
                foreach ($ordenCompra->items as $item) {
                    $this->recordMovement(
                        $item->producto,
                        'ingreso',
                        $item->cantidad,
                        $ordenCompra,
                        $user->id,
                        "OC {$ordenCompra->codigo} recibida"
                    );
                }
            }

            $ordenCompra->update(['estado' => $newEstado]);
            return $ordenCompra->load(['proveedor', 'user', 'items.producto']);
        });
    }

    public function pdf(Request $request, OrdenCompra $ordenCompra)
    {
        if (!$request->user()->hasAnyRole(['Administrador', 'Encargado'])) {
            abort(403);
        }

        $ordenCompra->load(['proveedor', 'user', 'items.producto']);

        $pdf = Pdf::loadView('pdfs.orden-compra', [
            'orden' => $ordenCompra,
        ]);

        return $pdf->download("{$ordenCompra->codigo}.pdf");
    }
}
