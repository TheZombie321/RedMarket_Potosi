<?php

namespace App\Http\Controllers;

use App\Models\Proveedor;
use Illuminate\Http\Request;

class ProveedorController extends Controller
{
    public function index()
    {
        return Proveedor::all();
    }

    public function store(Request $request)
    {
        $user = $request->user();
        if (!$user->hasAnyRole(['Administrador', 'Encargado'])) {
            abort(403, 'No autorizado.');
        }

        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'contacto' => 'nullable|string',
            'telefono' => 'nullable|string',
            'email' => 'nullable|email',
            'direccion' => 'nullable|string',
        ]);

        return Proveedor::create($validated);
    }

    public function show(Proveedor $proveedor)
    {
        return $proveedor;
    }

    public function update(Request $request, Proveedor $proveedor)
    {
        $user = $request->user();
        if (!$user->hasAnyRole(['Administrador', 'Encargado'])) {
            abort(403, 'No autorizado.');
        }

        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'contacto' => 'nullable|string',
            'telefono' => 'nullable|string',
            'email' => 'nullable|email',
            'direccion' => 'nullable|string',
        ]);

        $proveedor->update($validated);
        return $proveedor;
    }

    public function destroy(Request $request, Proveedor $proveedor)
    {
        $user = $request->user();
        if (!$user->hasAnyRole(['Administrador', 'Encargado'])) {
            abort(403, 'No autorizado.');
        }

        $proveedor->delete();
        return response()->noContent();
    }
}
