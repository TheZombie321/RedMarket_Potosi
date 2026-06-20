<?php

use App\Http\Controllers\ProductoController;
use App\Http\Controllers\CategoriaController;
use App\Http\Controllers\ProveedorController;
use App\Http\Controllers\PedidoController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\PasswordResetController;
use App\Http\Controllers\ResenaController;
use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/olvide-password', [PasswordResetController::class, 'sendResetLink']);
Route::post('/reset-password', [PasswordResetController::class, 'reset']);

Route::apiResource('productos', ProductoController::class)->only(['index', 'show']);
Route::get('/productos/{producto}/resenas', [ResenaController::class, 'index']);
Route::middleware(['auth:sanctum'])->group(function () {
    Route::apiResource('productos', ProductoController::class)->except(['index', 'show']);
    Route::apiResource('categorias', CategoriaController::class);
    Route::apiResource('proveedores', ProveedorController::class);
    Route::apiResource('pedidos', PedidoController::class);
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/logout-all', [AuthController::class, 'logoutAll']);
    Route::get('/user', [AuthController::class, 'user']);
    Route::put('/perfil', [AuthController::class, 'updatePerfil']);
    Route::apiResource('usuarios', UserController::class);
    Route::put('/pedidos/{pedido}/ubicacion', [PedidoController::class, 'ubicacion']);
    Route::post('/productos/{producto}/resenas', [ResenaController::class, 'store']);
    Route::delete('/resenas/{resena}', [ResenaController::class, 'destroy']);
    Route::get('/dashboard', [DashboardController::class, 'index']);
});
