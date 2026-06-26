<?php

namespace App\Providers;

use App\Models\Pedido;
use App\Models\Producto;
use App\Models\Resena;
use App\Policies\PedidoPolicy;
use App\Policies\ProductoPolicy;
use App\Policies\ResenaPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        Pedido::class => PedidoPolicy::class,
        Producto::class => ProductoPolicy::class,
        Resena::class => ResenaPolicy::class,
    ];

    public function boot(): void
    {
        $this->registerPolicies();
    }
}
