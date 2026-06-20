<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{

    public function show(Request $request, User $user)
    {
        if (!$request->user()->hasAnyRole(['Administrador', 'Encargado'])) {
            abort(403, 'No autorizado.');
        }
        return $user->load('roles');
    }

    public function index(Request $request)
    {
        $user = $request->user();
        if (!$user->hasAnyRole(['Administrador', 'Encargado'])) {
            abort(403, 'No autorizado.');
        }

        $staffRoles = ['Administrador', 'Encargado', 'Picking', 'Repartidor'];

        $perPage = min((int) $request->input('per_page', 20), 100);

        return User::with('roles')
            ->whereHas('roles', fn($q) => $q->whereIn('name', $staffRoles))
            ->latest()
            ->paginate($perPage);
    }

    public function store(Request $request)
    {
        $request->user()->hasAnyRole(['Administrador', 'Encargado']) || abort(403);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8',
            'telefono' => 'nullable|string|max:20',
            'direccion' => 'nullable|string|max:500',
            'role' => 'required|in:Administrador,Encargado,Picking,Repartidor',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'telefono' => $validated['telefono'] ?? null,
            'direccion' => $validated['direccion'] ?? null,
        ]);

        $user->assignRole($validated['role']);

        return response()->json($user->load('roles'), 201);
    }

    public function update(Request $request, User $user)
    {
        $request->user()->hasAnyRole(['Administrador', 'Encargado']) || abort(403);

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|unique:users,email,' . $user->id,
            'password' => 'nullable|string|min:8',
            'telefono' => 'nullable|string|max:20',
            'direccion' => 'nullable|string|max:500',
            'role' => 'sometimes|in:Administrador,Encargado,Picking,Repartidor',
        ]);

        $data = array_filter([
            'name' => $validated['name'] ?? null,
            'email' => $validated['email'] ?? null,
            'telefono' => $validated['telefono'] ?? null,
            'direccion' => $validated['direccion'] ?? null,
        ], fn($v) => $v !== null);

        if (!empty($validated['password'])) {
            $data['password'] = Hash::make($validated['password']);
        }

        if (!empty($data)) {
            $user->update($data);
        }

        if (!empty($validated['role'])) {
            $user->syncRoles([$validated['role']]);
        }

        return response()->json($user->fresh()->load('roles'));
    }

    public function destroy(Request $request, User $user)
    {
        $request->user()->hasRole('Administrador') || abort(403);

        if ($user->id === $request->user()->id) {
            abort(422, 'No puedes eliminarte a ti mismo.');
        }

        $user->delete();

        return response()->noContent();
    }
}
