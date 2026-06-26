<?php

namespace App\Http\Controllers;

use App\Models\StockMovement;
use Illuminate\Http\Request;

class StockMovementController extends Controller
{
    public function index(Request $request)
    {
        if (!$request->user()->hasAnyRole(['Administrador', 'Encargado'])) {
            abort(403);
        }

        $query = StockMovement::with(['producto', 'user']);

        if ($productoId = $request->input('producto_id')) {
            $query->where('producto_id', $productoId);
        }

        if ($tipo = $request->input('tipo')) {
            $query->where('tipo', $tipo);
        }

        return $query->latest()->paginate(50);
    }
}
