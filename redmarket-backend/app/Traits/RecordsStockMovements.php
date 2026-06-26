<?php

namespace App\Traits;

use App\Models\Producto;
use App\Models\StockMovement;

trait RecordsStockMovements
{
    public function recordMovement(
        Producto $producto,
        string $tipo,
        int $cantidad,
        ?object $referencia = null,
        ?int $userId = null,
        ?string $motivo = null
    ): StockMovement {
        $anterior = $producto->stock_actual;

        if ($tipo === 'egreso') {
            $producto->decrement('stock_actual', $cantidad);
        } else {
            $producto->increment('stock_actual', $cantidad);
        }

        $producto->refresh();

        return StockMovement::create([
            'producto_id' => $producto->id,
            'tipo' => $tipo,
            'cantidad' => $cantidad,
            'stock_anterior' => $anterior,
            'stock_nuevo' => $producto->stock_actual,
            'referencia_type' => $referencia ? get_class($referencia) : null,
            'referencia_id' => $referencia?->id,
            'user_id' => $userId,
            'motivo' => $motivo,
        ]);
    }
}
