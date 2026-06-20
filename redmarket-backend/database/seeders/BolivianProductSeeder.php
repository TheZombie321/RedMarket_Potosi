<?php

namespace Database\Seeders;

use App\Models\Categoria;
use App\Models\Producto;
use App\Models\Proveedor;
use Illuminate\Database\Seeder;

class BolivianProductSeeder extends Seeder
{
    public function run(): void
    {
        $proveedor = Proveedor::firstOrCreate(
            ['nombre' => 'Distribuidora Mayorista'],
            ['contacto' => 'Carlos López', 'direccion' => 'Av. Circunvalación #500, Potosí', 'telefono' => '26543210', 'email' => 'distribuidora@example.com']
        );

        $categorias = [
            'Abarrotes' => Categoria::firstOrCreate(['nombre' => 'Abarrotes']),
            'Bebidas' => Categoria::firstOrCreate(['nombre' => 'Bebidas']),
            'Lácteos' => Categoria::firstOrCreate(['nombre' => 'Lácteos']),
            'Limpieza' => Categoria::firstOrCreate(['nombre' => 'Limpieza']),
        ];

        $productos = [
            ['nombre' => 'Arroz Grano de Oro 1kg', 'categoria' => 'Abarrotes', 'precio_compra' => 6.50, 'precio_venta' => 8.50, 'stock_actual' => 100, 'codigo_barras' => 'AR001', 'pasillo' => '1', 'nivel' => 'A', 'unidad_medida' => 'kg'],
            ['nombre' => 'Aceite Vegetal Fino 1L', 'categoria' => 'Abarrotes', 'precio_compra' => 9.00, 'precio_venta' => 12.00, 'stock_actual' => 60, 'codigo_barras' => 'AC001', 'pasillo' => '1', 'nivel' => 'B', 'unidad_medida' => 'lt'],
            ['nombre' => 'Azúcar Caña Real 1kg', 'categoria' => 'Abarrotes', 'precio_compra' => 5.50, 'precio_venta' => 7.50, 'stock_actual' => 80, 'codigo_barras' => 'AZ001', 'pasillo' => '1', 'nivel' => 'A', 'unidad_medida' => 'kg'],
            ['nombre' => 'Fideos Don Victorio 500g', 'categoria' => 'Abarrotes', 'precio_compra' => 4.00, 'precio_venta' => 5.50, 'stock_actual' => 120, 'codigo_barras' => 'FD001', 'pasillo' => '2', 'nivel' => 'A', 'unidad_medida' => 'un'],
            ['nombre' => 'Harina Selecta 1kg', 'categoria' => 'Abarrotes', 'precio_compra' => 5.00, 'precio_venta' => 7.00, 'stock_actual' => 70, 'codigo_barras' => 'HA001', 'pasillo' => '2', 'nivel' => 'B', 'unidad_medida' => 'kg'],

            ['nombre' => 'Coca-Cola 2L', 'categoria' => 'Bebidas', 'precio_compra' => 8.00, 'precio_venta' => 11.00, 'stock_actual' => 90, 'codigo_barras' => 'CC001', 'pasillo' => '3', 'nivel' => 'A', 'unidad_medida' => 'lt'],
            ['nombre' => 'Pepsi 2L', 'categoria' => 'Bebidas', 'precio_compra' => 7.50, 'precio_venta' => 10.00, 'stock_actual' => 75, 'codigo_barras' => 'PE001', 'pasillo' => '3', 'nivel' => 'A', 'unidad_medida' => 'lt'],
            ['nombre' => 'Agua Vital 2L', 'categoria' => 'Bebidas', 'precio_compra' => 4.00, 'precio_venta' => 5.50, 'stock_actual' => 150, 'codigo_barras' => 'AG001', 'pasillo' => '3', 'nivel' => 'B', 'unidad_medida' => 'lt'],
            ['nombre' => 'Jugo Deli Naranja 1L', 'categoria' => 'Bebidas', 'precio_compra' => 6.00, 'precio_venta' => 8.50, 'stock_actual' => 45, 'codigo_barras' => 'JU001', 'pasillo' => '3', 'nivel' => 'B', 'unidad_medida' => 'lt'],

            ['nombre' => 'Leche PIL Entera 1L', 'categoria' => 'Lácteos', 'precio_compra' => 7.00, 'precio_venta' => 9.50, 'stock_actual' => 60, 'codigo_barras' => 'LC001', 'pasillo' => '4', 'nivel' => 'A', 'unidad_medida' => 'lt'],
            ['nombre' => 'Yogurt PIL Natural 900ml', 'categoria' => 'Lácteos', 'precio_compra' => 8.00, 'precio_venta' => 11.00, 'stock_actual' => 40, 'codigo_barras' => 'YG001', 'pasillo' => '4', 'nivel' => 'A', 'unidad_medida' => 'lt'],
            ['nombre' => 'Queso Criollo 500g', 'categoria' => 'Lácteos', 'precio_compra' => 15.00, 'precio_venta' => 20.00, 'stock_actual' => 30, 'codigo_barras' => 'QS001', 'pasillo' => '4', 'nivel' => 'B', 'unidad_medida' => 'un'],
            ['nombre' => 'Mantequilla Finita 250g', 'categoria' => 'Lácteos', 'precio_compra' => 9.00, 'precio_venta' => 12.50, 'stock_actual' => 35, 'codigo_barras' => 'MT001', 'pasillo' => '4', 'nivel' => 'B', 'unidad_medida' => 'un'],

            ['nombre' => 'Detergente Ace 1kg', 'categoria' => 'Limpieza', 'precio_compra' => 10.00, 'precio_venta' => 14.00, 'stock_actual' => 50, 'codigo_barras' => 'DT001', 'pasillo' => '5', 'nivel' => 'A', 'unidad_medida' => 'un'],
            ['nombre' => 'Cloro Los Andes 1L', 'categoria' => 'Limpieza', 'precio_compra' => 5.00, 'precio_venta' => 7.00, 'stock_actual' => 65, 'codigo_barras' => 'CL001', 'pasillo' => '5', 'nivel' => 'A', 'unidad_medida' => 'lt'],
            ['nombre' => 'Jabón Líquido Soprole 500ml', 'categoria' => 'Limpieza', 'precio_compra' => 8.00, 'precio_venta' => 11.00, 'stock_actual' => 40, 'codigo_barras' => 'JB001', 'pasillo' => '5', 'nivel' => 'B', 'unidad_medida' => 'lt'],
            ['nombre' => 'Lava Vajilla Limon 500ml', 'categoria' => 'Limpieza', 'precio_compra' => 6.50, 'precio_venta' => 9.00, 'stock_actual' => 55, 'codigo_barras' => 'LV001', 'pasillo' => '5', 'nivel' => 'B', 'unidad_medida' => 'lt'],

            ['nombre' => 'Galletas Casino Familiares 400g', 'categoria' => 'Abarrotes', 'precio_compra' => 7.00, 'precio_venta' => 9.50, 'stock_actual' => 85, 'codigo_barras' => 'GL001', 'pasillo' => '2', 'nivel' => 'A', 'unidad_medida' => 'un'],
            ['nombre' => 'Café Soluble Nescafé 200g', 'categoria' => 'Abarrotes', 'precio_compra' => 18.00, 'precio_venta' => 24.00, 'stock_actual' => 25, 'codigo_barras' => 'CF001', 'pasillo' => '2', 'nivel' => 'C', 'unidad_medida' => 'un'],
        ];

        foreach ($productos as $data) {
            $producto = Producto::firstOrCreate(
                ['codigo_barras' => $data['codigo_barras']],
                [
                    'nombre' => $data['nombre'],
                    'categoria_id' => $categorias[$data['categoria']]->id,
                    'precio_compra' => $data['precio_compra'],
                    'precio_venta' => $data['precio_venta'],
                    'stock_actual' => $data['stock_actual'],
                    'pasillo' => $data['pasillo'],
                    'nivel' => $data['nivel'],
                    'unidad_medida' => $data['unidad_medida'],
                    'imagen_url' => null,
                ]
            );

            if (!$producto->proveedores()->where('proveedor_id', $proveedor->id)->exists()) {
                $producto->proveedores()->attach($proveedor->id, ['precio_compra' => $data['precio_compra'], 'es_principal' => true]);
            }
        }

        $this->command->info('Productos bolivianos creados exitosamente.');
    }
}
