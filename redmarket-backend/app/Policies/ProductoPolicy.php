<?php

namespace App\Policies;

use App\Models\Producto;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class ProductoPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(?User $user): bool
    {
        return true;
    }

    public function view(?User $user, Producto $producto): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return $user->hasAnyRole(['Administrador', 'Encargado']);
    }

    public function update(User $user, Producto $producto): bool
    {
        return $user->hasAnyRole(['Administrador', 'Encargado']);
    }

    public function delete(User $user, Producto $producto): bool
    {
        return $user->hasRole('Administrador');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Producto $producto): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Producto $producto): bool
    {
        return false;
    }
}
