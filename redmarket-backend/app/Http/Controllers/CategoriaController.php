<?php

namespace App\Http\Controllers;

use App\Models\Categoria;
use Illuminate\Http\Request;

class CategoriaController extends Controller
{
    public function index()
    {
        return Categoria::with('subcategorias')->whereNull('parent_id')->get();
    }

    public function store(Request $request)
    {
        $user = $request->user();
        if (!$user->hasAnyRole(['Administrador', 'Encargado'])) {
            abort(403, 'No autorizado.');
        }

        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'parent_id' => 'nullable|exists:categorias,id',
        ]);

        return Categoria::create($validated);
    }

    public function show(Categoria $categoria)
    {
        return $categoria->load('subcategorias');
    }

    public function update(Request $request, Categoria $categoria)
    {
        $user = $request->user();
        if (!$user->hasAnyRole(['Administrador', 'Encargado'])) {
            abort(403, 'No autorizado.');
        }

        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'parent_id' => 'nullable|exists:categorias,id',
        ]);

        $categoria->update($validated);
        return $categoria;
    }

    public function destroy(Request $request, Categoria $categoria)
    {
        $user = $request->user();
        if (!$user->hasAnyRole(['Administrador', 'Encargado'])) {
            abort(403, 'No autorizado.');
        }

        $categoria->delete();
        return response()->noContent();
    }
}
