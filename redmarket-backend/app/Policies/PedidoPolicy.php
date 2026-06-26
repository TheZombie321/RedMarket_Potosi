<?php

namespace App\Policies;

use App\Models\Pedido;
use App\Models\User;

class PedidoPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Pedido $pedido): bool
    {
        return $user->id === $pedido->user_id || $user->hasAnyRole(['Administrador', 'Encargado', 'Picking', 'Repartidor']);
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, Pedido $pedido): bool
    {
        // Owner can cancel if pendiente
        if ($user->id === $pedido->user_id && $pedido->estado === 'pendiente') {
            return true;
        }
        return $user->hasAnyRole(['Administrador', 'Encargado', 'Picking', 'Repartidor']);
    }

    public function delete(User $user, Pedido $pedido): bool
    {
        return $user->hasRole('Administrador');
    }
}
