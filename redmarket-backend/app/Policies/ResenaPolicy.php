<?php

namespace App\Policies;

use App\Models\Resena;
use App\Models\User;

class ResenaPolicy
{
    public function delete(User $user, Resena $resena): bool
    {
        return $user->id === $resena->user_id || $user->hasRole('Administrador');
    }
}
