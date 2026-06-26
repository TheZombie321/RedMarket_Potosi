<?php

namespace App\Http\Controllers;

use App\Models\Shift;
use Illuminate\Http\Request;

class ShiftController extends Controller
{
    public function iniciar(Request $request)
    {
        $user = $request->user();

        if ($user->hasAnyRole(['Cliente'])) {
            abort(403, 'No autorizado.');
        }

        $activo = Shift::where('user_id', $user->id)->where('estado', 'activo')->first();
        if ($activo) {
            abort(422, 'Ya tienes un turno activo. Finalízalo antes de iniciar otro.');
        }

        $shift = Shift::create([
            'user_id' => $user->id,
            'inicio' => now(),
            'estado' => 'activo',
        ]);

        return $shift;
    }

    public function finalizar(Request $request)
    {
        $user = $request->user();
        $shift = Shift::where('user_id', $user->id)->where('estado', 'activo')->firstOrFail();

        $shift->update([
            'fin' => now(),
            'estado' => 'finalizado',
        ]);

        return $shift;
    }

    public function actual(Request $request)
    {
        $shift = Shift::where('user_id', $request->user()->id)
            ->where('estado', 'activo')
            ->first();

        return $shift;
    }

    public function index(Request $request)
    {
        $user = $request->user();
        if (!$user->hasAnyRole(['Administrador', 'Encargado'])) {
            abort(403, 'No autorizado.');
        }

        $query = Shift::with('user');

        if ($request->has('activos')) {
            $query->where('estado', 'activo');
        }

        if ($request->has('desde')) {
            $query->where('inicio', '>=', $request->desde);
        }

        if ($request->has('hasta')) {
            $query->where('inicio', '<=', $request->hasta);
        }

        return $query->latest('inicio')->paginate(20);
    }
}
