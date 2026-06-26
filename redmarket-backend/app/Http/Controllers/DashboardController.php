<?php

namespace App\Http\Controllers;

use App\Models\Pedido;
use App\Models\Producto;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        if (!$user->hasRole('Administrador')) {
            abort(403);
        }

        $hoy = now()->startOfDay();
        $inicioMes = now()->startOfMonth();

        // Pedidos hoy
        $pedidosHoy = Pedido::whereDate('created_at', $hoy)->count();

        // Ingresos hoy (solo entregados)
        $ingresosHoy = Pedido::whereDate('created_at', $hoy)
            ->where('estado', 'entregado')
            ->sum('total_final');

        // Ingresos del mes
        $ingresosMes = Pedido::where('created_at', '>=', $inicioMes)
            ->where('estado', 'entregado')
            ->sum('total_final');

        // Ingresos por método de pago (mes actual)
        $ingresosPorMetodo = Pedido::where('created_at', '>=', $inicioMes)
            ->where('estado', 'entregado')
            ->groupBy('payment_method')
            ->select('payment_method', DB::raw('SUM(total_final) as total'))
            ->pluck('total', 'payment_method');

        // Pedidos últimos 7 días
        $pedidos7dias = Pedido::where('created_at', '>=', now()->subDays(7)->startOfDay())
            ->select(DB::raw('DATE(created_at) as fecha'), DB::raw('count(*) as total'))
            ->groupBy('fecha')
            ->orderBy('fecha')
            ->get();

        // Top 5 productos más vendidos
        $topProductos = DB::table('pedido_items')
            ->join('productos', 'pedido_items.producto_id', '=', 'productos.id')
            ->select('productos.id', 'productos.nombre', DB::raw('SUM(pedido_items.cantidad) as total_vendido'))
            ->groupBy('productos.id', 'productos.nombre')
            ->orderByDesc('total_vendido')
            ->limit(5)
            ->get();

        // Pedidos por estado
        $pedidosPorEstado = Pedido::select('estado', DB::raw('count(*) as total'))
            ->groupBy('estado')
            ->pluck('total', 'estado');

        // Productos con stock bajo
        $stockBajo = Producto::whereColumn('stock_actual', '<=', 'stock_minimo')
            ->whereNull('deleted_at')
            ->orderBy('stock_actual')
            ->limit(10)
            ->get(['id', 'nombre', 'stock_actual', 'stock_minimo', 'unidad_medida']);

        // Rendimiento por empleado (mes actual)
        $rendimientoStaff = User::whereHas('roles', fn($q) => $q->whereIn('name', ['Picking', 'Repartidor']))
            ->get()->map(fn($staff) => [
                'id' => $staff->id,
                'name' => $staff->name,
                'role' => $staff->roles->first()?->name,
                'pedidos_preparados' => Pedido::where('picking_user_id', $staff->id)
                    ->where('created_at', '>=', $inicioMes)->count(),
                'pedidos_entregados' => Pedido::where('repartidor_id', $staff->id)
                    ->where('created_at', '>=', $inicioMes)
                    ->where('estado', 'entregado')->count(),
                'total_ingresos_generados' => (float) Pedido::where('repartidor_id', $staff->id)
                    ->where('created_at', '>=', $inicioMes)
                    ->where('estado', 'entregado')->sum('total_final'),
            ]);

        // Totals
        $totalClientes = User::whereHas('roles', fn($q) => $q->where('name', 'Cliente'))->count();
        $totalProductos = Producto::whereNull('deleted_at')->count();
        $totalStaff = User::whereHas('roles', fn($q) => $q->whereIn('name', ['Administrador', 'Encargado', 'Picking', 'Repartidor']))->count();

        return response()->json([
            'pedidos_hoy' => $pedidosHoy,
            'ingresos_hoy' => (float) $ingresosHoy,
            'ingresos_mes' => (float) $ingresosMes,
            'ingresos_por_metodo' => [
                'cash' => (float) ($ingresosPorMetodo['cash'] ?? 0),
                'stripe' => (float) ($ingresosPorMetodo['stripe'] ?? 0),
            ],
            'pedidos_7dias' => $pedidos7dias,
            'top_productos' => $topProductos,
            'pedidos_por_estado' => $pedidosPorEstado,
            'stock_bajo' => $stockBajo,
            'total_clientes' => $totalClientes,
            'total_productos' => $totalProductos,
            'total_staff' => $totalStaff,
            'rendimiento_staff' => $rendimientoStaff,
        ]);
    }
}
