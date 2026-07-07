<?php

namespace App\Http\Controllers;

use App\Models\Producto;
use App\Models\Resena;
use Illuminate\Http\Request;

class ResenaController extends Controller
{
    public function listAll(Request $request)
    {
        $perPage = min((int) $request->input('per_page', 10), 50);

        return Resena::with('user:id,name')
            ->latest()
            ->paginate($perPage)
            ->through(fn($r) => [
                'id' => $r->id,
                'puntuacion' => $r->puntuacion,
                'comentario' => $r->comentario,
                'producto_nombre' => $r->producto?->nombre,
                'user' => ['id' => $r->user->id, 'name' => $r->user->name],
                'created_at' => $r->created_at,
            ]);
    }

    public function index(Producto $producto)
    {
        $resenas = $producto->resenas()
            ->with('user:id,name')
            ->latest()
            ->get()
            ->map(fn($r) => [
                'id' => $r->id,
                'puntuacion' => $r->puntuacion,
                'comentario' => $r->comentario,
                'user' => ['id' => $r->user->id, 'name' => $r->user->name],
                'created_at' => $r->created_at,
            ]);

        $stats = $producto->resenas()
            ->selectRaw('AVG(puntuacion) as promedio, COUNT(*) as total')
            ->first();

        return response()->json([
            'data' => $resenas,
            'promedio' => (float) ($stats->promedio ?? 0),
            'total' => (int) ($stats->total ?? 0),
        ]);
    }

    public function store(Request $request, Producto $producto)
    {
        $validated = $request->validate([
            'puntuacion' => 'required|integer|min:1|max:5',
            'comentario' => 'nullable|string|max:500',
        ]);

        $resena = Resena::updateOrCreate(
            [
                'producto_id' => $producto->id,
                'user_id' => $request->user()->id,
            ],
            [
                'puntuacion' => $validated['puntuacion'],
                'comentario' => $validated['comentario'] ?? null,
            ]
        );

        $stats = $producto->resenas()
            ->selectRaw('AVG(puntuacion) as promedio, COUNT(*) as total')
            ->first();

        return response()->json([
            'data' => $resena->load('user:id,name'),
            'promedio' => (float) ($stats->promedio ?? 0),
            'total' => (int) ($stats->total ?? 0),
        ]);
    }

    public function destroy(Request $request, Resena $resena)
    {
        if ($resena->user_id !== $request->user()->id && !$request->user()->hasRole('Administrador')) {
            abort(403, 'No autorizado.');
        }

        $resena->delete();

        return response()->noContent();
    }
}
