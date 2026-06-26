<?php

namespace Database\Seeders;

use App\Models\Categoria;
use App\Models\Producto;
use App\Models\Proveedor;
use Illuminate\Database\Seeder;

class BolivianProductSeeder extends Seeder
{
    protected array $imageMap = [
        'ABR001' => 'prod_ABR001.jpg', 'ABR002' => 'prod_ABR002.jpg', 'ABR003' => 'prod_ABR003.jpg',
        'ABR004' => 'prod_ABR004.jpg', 'ABR005' => 'prod_ABR005.jpg', 'ABR006' => 'prod_ABR006.jpg',
        'ABR007' => 'prod_ABR007.jpg', 'ABR008' => 'prod_ABR008.jpg', 'ABR009' => 'prod_ABR009.jpg',
        'ABR010' => 'prod_ABR010.jpg', 'ABR011' => 'prod_ABR011.jpg', 'ABR012' => 'prod_ABR012.jpg',
        'ABR013' => 'prod_ABR013.jpg', 'ABR014' => 'prod_ABR014.jpg', 'ABR015' => 'prod_ABR015.jpg',
        'BEB001' => 'prod_BEB001.jpg', 'BEB002' => 'prod_BEB002.jpg', 'BEB003' => 'prod_BEB003.jpg',
        'BEB004' => 'prod_BEB004.jpg', 'BEB005' => 'prod_BEB005.jpg', 'BEB006' => 'prod_BEB006.jpg',
        'BEB007' => 'prod_BEB007.jpg', 'BEB008' => 'prod_BEB008.jpg', 'BEB009' => 'prod_BEB009.jpg',
        'BEB010' => 'prod_BEB010.jpg', 'BEB011' => 'prod_BEB011.jpg', 'BEB012' => 'prod_BEB012.jpg',
        'BEB013' => 'prod_BEB013.jpg', 'BEB014' => 'prod_BEB014.jpg', 'BEB015' => 'prod_BEB015.jpg',
        'LAC001' => 'prod_LAC001.jpg', 'LAC002' => 'prod_LAC002.jpg', 'LAC003' => 'prod_LAC003.jpg',
        'LAC004' => 'prod_LAC004.jpg', 'LAC005' => 'prod_LAC005.jpg', 'LAC006' => 'prod_LAC006.jpg',
        'LAC007' => 'prod_LAC007.jpg', 'LAC008' => 'prod_LAC008.jpg', 'LAC009' => 'prod_LAC009.jpg',
        'LAC010' => 'prod_LAC010.jpg', 'LAC011' => 'prod_LAC011.jpg', 'LAC012' => 'prod_LAC012.jpg',
        'LAC013' => 'prod_LAC013.jpg', 'LAC014' => 'prod_LAC014.jpg', 'LAC015' => 'prod_LAC015.jpg',
        'LIM001' => 'prod_LIM001.jpg', 'LIM002' => 'prod_LIM002.jpg', 'LIM003' => 'prod_LIM003.jpg',
        'LIM004' => 'prod_LIM004.jpg', 'LIM005' => 'prod_LIM005.jpg', 'LIM006' => 'prod_LIM006.jpg',
        'LIM007' => 'prod_LIM007.jpg', 'LIM008' => 'prod_LIM008.jpg', 'LIM009' => 'prod_LIM009.jpg',
        'LIM010' => 'prod_LIM010.jpg', 'LIM011' => 'prod_LIM011.jpg', 'LIM012' => 'prod_LIM012.jpg',
        'LIM013' => 'prod_LIM013.jpg', 'LIM014' => 'prod_LIM014.jpg', 'LIM015' => 'prod_LIM015.jpg',
        'PAN001' => 'prod_PAN001.jpg', 'PAN002' => 'prod_PAN002.jpg', 'PAN003' => 'prod_PAN003.jpg',
        'PAN004' => 'prod_PAN004.jpg', 'PAN005' => 'prod_PAN005.jpg', 'PAN006' => 'prod_PAN006.jpg',
        'PAN007' => 'prod_PAN007.jpg', 'PAN008' => 'prod_PAN008.jpg', 'PAN009' => 'prod_PAN009.jpg',
        'PAN010' => 'prod_PAN010.jpg', 'PAN011' => 'prod_PAN011.jpg', 'PAN012' => 'prod_PAN012.jpg',
        'PAN013' => 'prod_PAN013.jpg', 'PAN014' => 'prod_PAN014.jpg', 'PAN015' => 'prod_PAN015.jpg',
        'CAR001' => 'prod_CAR001.jpg', 'CAR002' => 'prod_CAR002.jpg', 'CAR003' => 'prod_CAR003.jpg',
        'CAR004' => 'prod_CAR004.jpg', 'CAR005' => 'prod_CAR005.jpg', 'CAR006' => 'prod_CAR006.jpg',
        'CAR007' => 'prod_CAR007.jpg', 'CAR008' => 'prod_CAR008.jpg', 'CAR009' => 'prod_CAR009.jpg',
        'CAR010' => 'prod_CAR010.jpg', 'CAR011' => 'prod_CAR011.jpg', 'CAR012' => 'prod_CAR012.jpg',
        'CAR013' => 'prod_CAR013.jpg', 'CAR014' => 'prod_CAR014.jpg', 'CAR015' => 'prod_CAR015.jpg',
        'CAR016' => 'prod_CAR016.jpg', 'CAR017' => 'prod_CAR017.jpg', 'CAR018' => 'prod_CAR018.jpg',
        'CAR019' => 'prod_CAR019.jpg', 'CAR020' => 'prod_CAR020.jpg',
        'FRS001' => 'prod_FRS001.jpg', 'FRS002' => 'prod_FRS002.jpg', 'FRS003' => 'prod_FRS003.jpg',
        'FRS004' => 'prod_FRS004.jpg', 'FRS005' => 'prod_FRS005.jpg', 'FRS006' => 'prod_FRS006.jpg',
        'FRS007' => 'prod_FRS007.jpg', 'FRS008' => 'prod_FRS008.jpg', 'FRS009' => 'prod_FRS009.jpg',
        'FRS010' => 'prod_FRS010.jpg', 'FRS011' => 'prod_FRS011.jpg', 'FRS012' => 'prod_FRS012.jpg',
        'FRS013' => 'prod_FRS013.jpg', 'FRS014' => 'prod_FRS014.jpg', 'FRS015' => 'prod_FRS015.jpg',
        'FRS016' => 'prod_FRS016.jpg', 'FRS017' => 'prod_FRS017.jpg', 'FRS018' => 'prod_FRS018.jpg',
        'FRS019' => 'prod_FRS019.jpg', 'FRS020' => 'prod_FRS020.jpg',
        'SNK001' => 'prod_SNK001.jpg', 'SNK002' => 'prod_SNK002.jpg', 'SNK003' => 'prod_SNK003.jpg',
        'SNK004' => 'prod_SNK004.jpg', 'SNK005' => 'prod_SNK005.jpg', 'SNK006' => 'prod_SNK006.jpg',
        'SNK007' => 'prod_SNK007.jpg', 'SNK008' => 'prod_SNK008.jpg', 'SNK009' => 'prod_SNK009.jpg',
        'SNK010' => 'prod_SNK010.jpg', 'SNK011' => 'prod_SNK011.jpg', 'SNK012' => 'prod_SNK012.jpg',
        'SNK013' => 'prod_SNK013.jpg', 'SNK014' => 'prod_SNK014.jpg', 'SNK015' => 'prod_SNK015.jpg',
        'SNK016' => 'prod_SNK016.jpg', 'SNK017' => 'prod_SNK017.jpg', 'SNK018' => 'prod_SNK018.jpg',
        'SNK019' => 'prod_SNK019.jpg', 'SNK020' => 'prod_SNK020.jpg',
    ];

    public function run(): void
    {
        $proveedores = $this->createProveedores();
        $categorias = $this->createCategorias();
        $productos = $this->getProductosData();

        foreach ($productos as $data) {
            $catKey = $data['categoria'];
            $catId = $categorias[$catKey]->id;
            $imagen = $this->imageFor($data['codigo_barras']);

            $producto = Producto::firstOrCreate(
                ['codigo_barras' => $data['codigo_barras']],
                [
                    'nombre' => $data['nombre'],
                    'descripcion' => $data['descripcion'] ?? "{$data['nombre']} - producto de calidad en RedMarket Potosi.",
                    'categoria_id' => $catId,
                    'precio_compra' => $data['precio_compra'],
                    'precio_venta' => $data['precio_venta'],
                    'precio_oferta' => $data['precio_oferta'] ?? null,
                    'en_descuento' => $data['en_descuento'] ?? false,
                    'stock_actual' => $data['stock_actual'],
                    'stock_minimo' => $data['stock_minimo'] ?? 10,
                    'pasillo' => $data['pasillo'],
                    'nivel' => $data['nivel'],
                    'unidad_medida' => $data['unidad_medida'],
                    'imagen_url' => $imagen,
                    'es_perecedero' => $data['es_perecedero'] ?? false,
                ]
            );

            if ($producto->imagen_url !== $imagen) {
                $producto->update(['imagen_url' => $imagen]);
            }

            $provKey = $data['proveedor'];
            $provId = $proveedores[$provKey]->id;
            if (!$producto->proveedores()->where('proveedor_id', $provId)->exists()) {
                $producto->proveedores()->attach($provId, [
                    'precio_compra' => $data['precio_compra'],
                    'es_principal' => true,
                ]);
            }
        }

        $this->command->info('135 productos bolivianos creados en 8 categorias.');
    }

    protected function imageFor(string $codigo): string
    {
        return $this->imageMap[$codigo] ?? 'prod_ABR001.jpg';
    }

    protected function createProveedores(): array
    {
        return [
            'distribuidora' => Proveedor::firstOrCreate(
                ['nombre' => 'Distribuidora Mayorista'],
                ['contacto' => 'Carlos Lopez', 'direccion' => 'Av. Circunvalacion #500, Potosi', 'telefono' => '26543210', 'email' => 'distribuidora@example.com']
            ),
            'pil' => Proveedor::firstOrCreate(
                ['nombre' => 'PIL Andina S.A.'],
                ['contacto' => 'Maria Fernandez', 'direccion' => 'Calle Bolivar #123, La Paz', 'telefono' => '22223344', 'email' => 'ventas@pil.bo']
            ),
            'cbn' => Proveedor::firstOrCreate(
                ['nombre' => 'Cerveceria Boliviana Nacional'],
                ['contacto' => 'Pedro Quispe', 'direccion' => 'Av. 6 de Agosto #789, La Paz', 'telefono' => '24445566', 'email' => 'pedidos@cbn.bo']
            ),
            'fino' => Proveedor::firstOrCreate(
                ['nombre' => 'Industrias Fino S.A.'],
                ['contacto' => 'Ana Gutierrez', 'direccion' => 'Zona Industrial #456, Cochabamba', 'telefono' => '46667788', 'email' => 'ventas@fino.bo']
            ),
            'dillman' => Proveedor::firstOrCreate(
                ['nombre' => 'Dillman S.A.'],
                ['contacto' => 'Roberto Vargas', 'direccion' => 'Av. Blanco Galindo km 5, Cochabamba', 'telefono' => '48889900', 'email' => 'contacto@dillman.bo']
            ),
            'embol' => Proveedor::firstOrCreate(
                ['nombre' => 'Embotelladora Boliviana (Embol)'],
                ['contacto' => 'Laura Mendez', 'direccion' => 'Av. Ecologica #200, Santa Cruz', 'telefono' => '35556677', 'email' => 'pedidos@embol.bo']
            ),
            'italiana' => Proveedor::firstOrCreate(
                ['nombre' => 'La Italiana S.A.'],
                ['contacto' => 'Giuseppe Rossi', 'direccion' => 'Calle Comercio #789, Potosi', 'telefono' => '26223344', 'email' => 'info@laitaliana.bo']
            ),
        ];
    }

    protected function createCategorias(): array
    {
        $nombres = [
            'abarrotes' => 'Abarrotes',
            'bebidas' => 'Bebidas',
            'lacteos' => 'Lacteos',
            'limpieza' => 'Limpieza',
            'panaderia' => 'Panaderia',
            'carnes' => 'Carnes y Embutidos',
            'frutas' => 'Frutas y Verduras',
            'snacks' => 'Snacks y Golosinas',
        ];
        $map = [];
        foreach ($nombres as $key => $name) {
            $map[$key] = Categoria::firstOrCreate(['nombre' => $name]);
        }
        return $map;
    }

    protected function getProductosData(): array
    {
        return [
            // -- ABARROTES (15) --
            ['nombre' => 'Arroz Grano de Oro 1kg', 'categoria' => 'abarrotes', 'precio_compra' => 6.50, 'precio_venta' => 8.50, 'stock_actual' => 120, 'codigo_barras' => 'ABR001', 'pasillo' => '1', 'nivel' => 'A', 'unidad_medida' => 'kg', 'proveedor' => 'italiana'],
            ['nombre' => 'Aceite Vegetal Fino 1L', 'categoria' => 'abarrotes', 'precio_compra' => 9.00, 'precio_venta' => 12.00, 'stock_actual' => 80, 'codigo_barras' => 'ABR002', 'pasillo' => '1', 'nivel' => 'A', 'unidad_medida' => 'lt', 'proveedor' => 'fino'],
            ['nombre' => 'Azucar Cana Real 1kg', 'categoria' => 'abarrotes', 'precio_compra' => 5.50, 'precio_venta' => 7.50, 'stock_actual' => 100, 'codigo_barras' => 'ABR003', 'pasillo' => '1', 'nivel' => 'B', 'unidad_medida' => 'kg', 'proveedor' => 'distribuidora'],
            ['nombre' => 'Fideos Don Victorio Spaghetti 500g', 'categoria' => 'abarrotes', 'precio_compra' => 4.00, 'precio_venta' => 5.50, 'stock_actual' => 140, 'codigo_barras' => 'ABR004', 'pasillo' => '2', 'nivel' => 'A', 'unidad_medida' => 'un', 'proveedor' => 'italiana'],
            ['nombre' => 'Harina Selecta 1kg', 'categoria' => 'abarrotes', 'precio_compra' => 5.00, 'precio_venta' => 7.00, 'stock_actual' => 90, 'codigo_barras' => 'ABR005', 'pasillo' => '2', 'nivel' => 'B', 'unidad_medida' => 'kg', 'proveedor' => 'distribuidora', 'en_descuento' => true, 'precio_oferta' => 5.90],
            ['nombre' => 'Sal Yodada Saltena 500g', 'categoria' => 'abarrotes', 'precio_compra' => 2.00, 'precio_venta' => 3.00, 'stock_actual' => 200, 'codigo_barras' => 'ABR006', 'pasillo' => '2', 'nivel' => 'C', 'unidad_medida' => 'un', 'proveedor' => 'distribuidora'],
            ['nombre' => 'Galletas Casino Familiares 400g', 'categoria' => 'abarrotes', 'precio_compra' => 7.00, 'precio_venta' => 9.50, 'stock_actual' => 85, 'codigo_barras' => 'ABR007', 'pasillo' => '2', 'nivel' => 'A', 'unidad_medida' => 'un', 'proveedor' => 'fino', 'en_descuento' => true, 'precio_oferta' => 7.90],
            ['nombre' => 'Cafe Soluble Nescafe 200g', 'categoria' => 'abarrotes', 'precio_compra' => 18.00, 'precio_venta' => 24.00, 'stock_actual' => 35, 'codigo_barras' => 'ABR008', 'pasillo' => '2', 'nivel' => 'C', 'unidad_medida' => 'un', 'proveedor' => 'distribuidora'],
            ['nombre' => 'Leche Condensada PIL 395g', 'categoria' => 'abarrotes', 'precio_compra' => 9.00, 'precio_venta' => 12.50, 'stock_actual' => 50, 'codigo_barras' => 'ABR009', 'pasillo' => '1', 'nivel' => 'B', 'unidad_medida' => 'un', 'proveedor' => 'pil'],
            ['nombre' => 'Mermelada de Fresa La Italiana 300g', 'categoria' => 'abarrotes', 'precio_compra' => 8.00, 'precio_venta' => 11.00, 'stock_actual' => 45, 'codigo_barras' => 'ABR010', 'pasillo' => '2', 'nivel' => 'B', 'unidad_medida' => 'un', 'proveedor' => 'italiana', 'en_descuento' => true, 'precio_oferta' => 9.50],
            ['nombre' => 'Atun en Lata Real 170g', 'categoria' => 'abarrotes', 'precio_compra' => 6.00, 'precio_venta' => 8.50, 'stock_actual' => 70, 'codigo_barras' => 'ABR011', 'pasillo' => '1', 'nivel' => 'A', 'unidad_medida' => 'un', 'proveedor' => 'distribuidora'],
            ['nombre' => 'Sardina en Lata Real 120g', 'categoria' => 'abarrotes', 'precio_compra' => 4.00, 'precio_venta' => 5.50, 'stock_actual' => 65, 'codigo_barras' => 'ABR012', 'pasillo' => '1', 'nivel' => 'B', 'unidad_medida' => 'un', 'proveedor' => 'distribuidora'],
            ['nombre' => 'Mayonesa Fino 250g', 'categoria' => 'abarrotes', 'precio_compra' => 5.00, 'precio_venta' => 7.00, 'stock_actual' => 55, 'codigo_barras' => 'ABR013', 'pasillo' => '2', 'nivel' => 'A', 'unidad_medida' => 'un', 'proveedor' => 'fino'],
            ['nombre' => 'Mostaza Fino 200g', 'categoria' => 'abarrotes', 'precio_compra' => 4.00, 'precio_venta' => 5.50, 'stock_actual' => 60, 'codigo_barras' => 'ABR014', 'pasillo' => '2', 'nivel' => 'B', 'unidad_medida' => 'un', 'proveedor' => 'fino'],
            ['nombre' => 'Vinagre de Vino Los Andes 500ml', 'categoria' => 'abarrotes', 'precio_compra' => 3.00, 'precio_venta' => 4.50, 'stock_actual' => 75, 'codigo_barras' => 'ABR015', 'pasillo' => '2', 'nivel' => 'C', 'unidad_medida' => 'lt', 'proveedor' => 'distribuidora'],

            // -- BEBIDAS (15) --
            ['nombre' => 'Coca-Cola 2L', 'categoria' => 'bebidas', 'precio_compra' => 8.00, 'precio_venta' => 11.00, 'stock_actual' => 100, 'codigo_barras' => 'BEB001', 'pasillo' => '3', 'nivel' => 'A', 'unidad_medida' => 'lt', 'proveedor' => 'embol'],
            ['nombre' => 'Pepsi 2L', 'categoria' => 'bebidas', 'precio_compra' => 7.50, 'precio_venta' => 10.00, 'stock_actual' => 85, 'codigo_barras' => 'BEB002', 'pasillo' => '3', 'nivel' => 'A', 'unidad_medida' => 'lt', 'proveedor' => 'embol'],
            ['nombre' => 'Agua Vital 2L', 'categoria' => 'bebidas', 'precio_compra' => 4.00, 'precio_venta' => 5.50, 'stock_actual' => 160, 'codigo_barras' => 'BEB003', 'pasillo' => '3', 'nivel' => 'B', 'unidad_medida' => 'lt', 'proveedor' => 'embol', 'en_descuento' => true, 'precio_oferta' => 4.50],
            ['nombre' => 'Jugo Deli Naranja 1L', 'categoria' => 'bebidas', 'precio_compra' => 6.00, 'precio_venta' => 8.50, 'stock_actual' => 55, 'codigo_barras' => 'BEB004', 'pasillo' => '3', 'nivel' => 'B', 'unidad_medida' => 'lt', 'proveedor' => 'cbn'],
            ['nombre' => 'Cerveza Paceña 355ml', 'categoria' => 'bebidas', 'precio_compra' => 5.00, 'precio_venta' => 7.00, 'stock_actual' => 120, 'codigo_barras' => 'BEB005', 'pasillo' => '3', 'nivel' => 'C', 'unidad_medida' => 'un', 'proveedor' => 'cbn'],
            ['nombre' => 'Cerveza Huari 355ml', 'categoria' => 'bebidas', 'precio_compra' => 5.50, 'precio_venta' => 7.50, 'stock_actual' => 110, 'codigo_barras' => 'BEB006', 'pasillo' => '3', 'nivel' => 'C', 'unidad_medida' => 'un', 'proveedor' => 'cbn'],
            ['nombre' => 'Sprite 2L', 'categoria' => 'bebidas', 'precio_compra' => 7.00, 'precio_venta' => 9.50, 'stock_actual' => 90, 'codigo_barras' => 'BEB007', 'pasillo' => '3', 'nivel' => 'A', 'unidad_medida' => 'lt', 'proveedor' => 'embol'],
            ['nombre' => 'Fanta Naranja 2L', 'categoria' => 'bebidas', 'precio_compra' => 7.00, 'precio_venta' => 9.50, 'stock_actual' => 80, 'codigo_barras' => 'BEB008', 'pasillo' => '3', 'nivel' => 'A', 'unidad_medida' => 'lt', 'proveedor' => 'embol'],
            ['nombre' => 'Agua Con Gas Vital 1.5L', 'categoria' => 'bebidas', 'precio_compra' => 5.00, 'precio_venta' => 7.00, 'stock_actual' => 70, 'codigo_barras' => 'BEB009', 'pasillo' => '3', 'nivel' => 'B', 'unidad_medida' => 'lt', 'proveedor' => 'embol'],
            ['nombre' => 'Jugo Deli Multifruta 1L', 'categoria' => 'bebidas', 'precio_compra' => 6.50, 'precio_venta' => 9.00, 'stock_actual' => 50, 'codigo_barras' => 'BEB010', 'pasillo' => '3', 'nivel' => 'B', 'unidad_medida' => 'lt', 'proveedor' => 'cbn', 'en_descuento' => true, 'precio_oferta' => 7.50],
            ['nombre' => 'Energizante Boost 500ml', 'categoria' => 'bebidas', 'precio_compra' => 5.00, 'precio_venta' => 7.00, 'stock_actual' => 65, 'codigo_barras' => 'BEB011', 'pasillo' => '3', 'nivel' => 'C', 'unidad_medida' => 'un', 'proveedor' => 'cbn'],
            ['nombre' => 'Nectar de Durazno Pulp 1L', 'categoria' => 'bebidas', 'precio_compra' => 7.00, 'precio_venta' => 9.50, 'stock_actual' => 45, 'codigo_barras' => 'BEB012', 'pasillo' => '3', 'nivel' => 'B', 'unidad_medida' => 'lt', 'proveedor' => 'cbn'],
            ['nombre' => 'Agua de Mesa Vital 500ml (pack 6)', 'categoria' => 'bebidas', 'precio_compra' => 8.00, 'precio_venta' => 11.00, 'stock_actual' => 60, 'codigo_barras' => 'BEB013', 'pasillo' => '3', 'nivel' => 'B', 'unidad_medida' => 'un', 'proveedor' => 'embol'],
            ['nombre' => 'Malta Victoria 355ml', 'categoria' => 'bebidas', 'precio_compra' => 3.50, 'precio_venta' => 5.00, 'stock_actual' => 95, 'codigo_barras' => 'BEB014', 'pasillo' => '3', 'nivel' => 'C', 'unidad_medida' => 'un', 'proveedor' => 'cbn', 'en_descuento' => true, 'precio_oferta' => 4.00],
            ['nombre' => 'Gaseosa Latan 2L', 'categoria' => 'bebidas', 'precio_compra' => 5.00, 'precio_venta' => 7.00, 'stock_actual' => 75, 'codigo_barras' => 'BEB015', 'pasillo' => '3', 'nivel' => 'A', 'unidad_medida' => 'lt', 'proveedor' => 'embol'],

            // -- LACTEOS (15) --
            ['nombre' => 'Leche Entera PIL 1L', 'categoria' => 'lacteos', 'precio_compra' => 7.00, 'precio_venta' => 9.50, 'stock_actual' => 80, 'codigo_barras' => 'LAC001', 'pasillo' => '4', 'nivel' => 'A', 'unidad_medida' => 'lt', 'proveedor' => 'pil', 'es_perecedero' => true],
            ['nombre' => 'Leche Descremada PIL 1L', 'categoria' => 'lacteos', 'precio_compra' => 7.00, 'precio_venta' => 9.50, 'stock_actual' => 60, 'codigo_barras' => 'LAC002', 'pasillo' => '4', 'nivel' => 'A', 'unidad_medida' => 'lt', 'proveedor' => 'pil', 'es_perecedero' => true],
            ['nombre' => 'Yogurt Natural PIL 900ml', 'categoria' => 'lacteos', 'precio_compra' => 8.00, 'precio_venta' => 11.00, 'stock_actual' => 50, 'codigo_barras' => 'LAC003', 'pasillo' => '4', 'nivel' => 'A', 'unidad_medida' => 'lt', 'proveedor' => 'pil', 'es_perecedero' => true],
            ['nombre' => 'Yogurt Frutilla PIL 900ml', 'categoria' => 'lacteos', 'precio_compra' => 8.50, 'precio_venta' => 11.50, 'stock_actual' => 45, 'codigo_barras' => 'LAC004', 'pasillo' => '4', 'nivel' => 'A', 'unidad_medida' => 'lt', 'proveedor' => 'pil', 'es_perecedero' => true, 'en_descuento' => true, 'precio_oferta' => 9.90],
            ['nombre' => 'Queso Criollo Premium 500g', 'categoria' => 'lacteos', 'precio_compra' => 15.00, 'precio_venta' => 20.00, 'stock_actual' => 30, 'codigo_barras' => 'LAC005', 'pasillo' => '4', 'nivel' => 'B', 'unidad_medida' => 'un', 'proveedor' => 'pil', 'es_perecedero' => true],
            ['nombre' => 'Queso Mozzarella PIL 250g', 'categoria' => 'lacteos', 'precio_compra' => 10.00, 'precio_venta' => 14.00, 'stock_actual' => 35, 'codigo_barras' => 'LAC006', 'pasillo' => '4', 'nivel' => 'B', 'unidad_medida' => 'un', 'proveedor' => 'pil', 'es_perecedero' => true],
            ['nombre' => 'Mantequilla Finita 250g', 'categoria' => 'lacteos', 'precio_compra' => 9.00, 'precio_venta' => 12.50, 'stock_actual' => 40, 'codigo_barras' => 'LAC007', 'pasillo' => '4', 'nivel' => 'B', 'unidad_medida' => 'un', 'proveedor' => 'pil', 'es_perecedero' => true, 'en_descuento' => true, 'precio_oferta' => 10.90],
            ['nombre' => 'Margarina Dorada 250g', 'categoria' => 'lacteos', 'precio_compra' => 6.00, 'precio_venta' => 8.50, 'stock_actual' => 55, 'codigo_barras' => 'LAC008', 'pasillo' => '4', 'nivel' => 'B', 'unidad_medida' => 'un', 'proveedor' => 'pil'],
            ['nombre' => 'Crema de Leche PIL 200ml', 'categoria' => 'lacteos', 'precio_compra' => 7.00, 'precio_venta' => 9.50, 'stock_actual' => 40, 'codigo_barras' => 'LAC009', 'pasillo' => '4', 'nivel' => 'A', 'unidad_medida' => 'ml', 'proveedor' => 'pil', 'es_perecedero' => true],
            ['nombre' => 'Huevos Colorados (docena)', 'categoria' => 'lacteos', 'precio_compra' => 8.00, 'precio_venta' => 11.00, 'stock_actual' => 60, 'codigo_barras' => 'LAC010', 'pasillo' => '4', 'nivel' => 'C', 'unidad_medida' => 'un', 'proveedor' => 'distribuidora', 'es_perecedero' => true],
            ['nombre' => 'Leche Evaporada PIL 400g', 'categoria' => 'lacteos', 'precio_compra' => 6.00, 'precio_venta' => 8.50, 'stock_actual' => 70, 'codigo_barras' => 'LAC011', 'pasillo' => '4', 'nivel' => 'A', 'unidad_medida' => 'un', 'proveedor' => 'pil'],
            ['nombre' => 'Queso Crema PIL 200g', 'categoria' => 'lacteos', 'precio_compra' => 7.00, 'precio_venta' => 9.50, 'stock_actual' => 35, 'codigo_barras' => 'LAC012', 'pasillo' => '4', 'nivel' => 'B', 'unidad_medida' => 'un', 'proveedor' => 'pil', 'es_perecedero' => true],
            ['nombre' => 'Leche Saborizada PIL Chocolate 1L', 'categoria' => 'lacteos', 'precio_compra' => 8.00, 'precio_venta' => 11.00, 'stock_actual' => 50, 'codigo_barras' => 'LAC013', 'pasillo' => '4', 'nivel' => 'A', 'unidad_medida' => 'lt', 'proveedor' => 'pil', 'es_perecedero' => true],
            ['nombre' => 'Yogurt Bebible PIL Durazno 200ml', 'categoria' => 'lacteos', 'precio_compra' => 3.00, 'precio_venta' => 4.50, 'stock_actual' => 80, 'codigo_barras' => 'LAC014', 'pasillo' => '4', 'nivel' => 'A', 'unidad_medida' => 'un', 'proveedor' => 'pil', 'es_perecedero' => true, 'en_descuento' => true, 'precio_oferta' => 3.50],
            ['nombre' => 'Dulce de Leche PIL 400g', 'categoria' => 'lacteos', 'precio_compra' => 10.00, 'precio_venta' => 14.00, 'stock_actual' => 25, 'codigo_barras' => 'LAC015', 'pasillo' => '4', 'nivel' => 'B', 'unidad_medida' => 'un', 'proveedor' => 'pil'],

            // -- LIMPIEZA (15) --
            ['nombre' => 'Detergente Ace 1kg', 'categoria' => 'limpieza', 'precio_compra' => 10.00, 'precio_venta' => 14.00, 'stock_actual' => 60, 'codigo_barras' => 'LIM001', 'pasillo' => '5', 'nivel' => 'A', 'unidad_medida' => 'un', 'proveedor' => 'dillman'],
            ['nombre' => 'Detergente Opal 1kg', 'categoria' => 'limpieza', 'precio_compra' => 8.00, 'precio_venta' => 11.00, 'stock_actual' => 70, 'codigo_barras' => 'LIM002', 'pasillo' => '5', 'nivel' => 'A', 'unidad_medida' => 'un', 'proveedor' => 'dillman'],
            ['nombre' => 'Cloro Los Andes 1L', 'categoria' => 'limpieza', 'precio_compra' => 5.00, 'precio_venta' => 7.00, 'stock_actual' => 85, 'codigo_barras' => 'LIM003', 'pasillo' => '5', 'nivel' => 'B', 'unidad_medida' => 'lt', 'proveedor' => 'dillman', 'en_descuento' => true, 'precio_oferta' => 5.50],
            ['nombre' => 'Jabon Liquido Soprole 500ml', 'categoria' => 'limpieza', 'precio_compra' => 8.00, 'precio_venta' => 11.00, 'stock_actual' => 50, 'codigo_barras' => 'LIM004', 'pasillo' => '5', 'nivel' => 'B', 'unidad_medida' => 'lt', 'proveedor' => 'dillman'],
            ['nombre' => 'Lava Vajilla Limon 500ml', 'categoria' => 'limpieza', 'precio_compra' => 6.50, 'precio_venta' => 9.00, 'stock_actual' => 65, 'codigo_barras' => 'LIM005', 'pasillo' => '5', 'nivel' => 'B', 'unidad_medida' => 'lt', 'proveedor' => 'dillman', 'en_descuento' => true, 'precio_oferta' => 7.50],
            ['nombre' => 'Desinfectante Pinol 1L', 'categoria' => 'limpieza', 'precio_compra' => 7.00, 'precio_venta' => 9.50, 'stock_actual' => 55, 'codigo_barras' => 'LIM006', 'pasillo' => '5', 'nivel' => 'B', 'unidad_medida' => 'lt', 'proveedor' => 'dillman'],
            ['nombre' => 'Jabon de Tocador Rexona 125g', 'categoria' => 'limpieza', 'precio_compra' => 3.00, 'precio_venta' => 4.50, 'stock_actual' => 100, 'codigo_barras' => 'LIM007', 'pasillo' => '5', 'nivel' => 'C', 'unidad_medida' => 'un', 'proveedor' => 'distribuidora'],
            ['nombre' => 'Papel Higienico Elite (rollo x50m)', 'categoria' => 'limpieza', 'precio_compra' => 2.50, 'precio_venta' => 3.50, 'stock_actual' => 150, 'codigo_barras' => 'LIM008', 'pasillo' => '5', 'nivel' => 'C', 'unidad_medida' => 'un', 'proveedor' => 'dillman'],
            ['nombre' => 'Papel Higienico Elite pack 12', 'categoria' => 'limpieza', 'precio_compra' => 28.00, 'precio_venta' => 38.00, 'stock_actual' => 30, 'codigo_barras' => 'LIM009', 'pasillo' => '5', 'nivel' => 'C', 'unidad_medida' => 'un', 'proveedor' => 'dillman', 'en_descuento' => true, 'precio_oferta' => 32.00],
            ['nombre' => 'Limpiador Multiusos Poett 500ml', 'categoria' => 'limpieza', 'precio_compra' => 5.00, 'precio_venta' => 7.00, 'stock_actual' => 60, 'codigo_barras' => 'LIM010', 'pasillo' => '5', 'nivel' => 'B', 'unidad_medida' => 'lt', 'proveedor' => 'dillman'],
            ['nombre' => 'Lustramuebles Pronto 250ml', 'categoria' => 'limpieza', 'precio_compra' => 6.00, 'precio_venta' => 8.50, 'stock_actual' => 40, 'codigo_barras' => 'LIM011', 'pasillo' => '5', 'nivel' => 'B', 'unidad_medida' => 'un', 'proveedor' => 'dillman'],
            ['nombre' => 'Ambientador Sapolio Aerosol 360ml', 'categoria' => 'limpieza', 'precio_compra' => 7.00, 'precio_venta' => 9.50, 'stock_actual' => 45, 'codigo_barras' => 'LIM012', 'pasillo' => '5', 'nivel' => 'C', 'unidad_medida' => 'un', 'proveedor' => 'dillman'],
            ['nombre' => 'Esponja Scotch-Brite 3M', 'categoria' => 'limpieza', 'precio_compra' => 2.00, 'precio_venta' => 3.00, 'stock_actual' => 120, 'codigo_barras' => 'LIM013', 'pasillo' => '5', 'nivel' => 'C', 'unidad_medida' => 'un', 'proveedor' => 'distribuidora'],
            ['nombre' => 'Bolsa de Basura Resistente pack 20', 'categoria' => 'limpieza', 'precio_compra' => 5.00, 'precio_venta' => 7.00, 'stock_actual' => 80, 'codigo_barras' => 'LIM014', 'pasillo' => '5', 'nivel' => 'C', 'unidad_medida' => 'un', 'proveedor' => 'distribuidora'],
            ['nombre' => 'Cera para Pisos Los Andes 500ml', 'categoria' => 'limpieza', 'precio_compra' => 8.00, 'precio_venta' => 11.00, 'stock_actual' => 35, 'codigo_barras' => 'LIM015', 'pasillo' => '5', 'nivel' => 'B', 'unidad_medida' => 'lt', 'proveedor' => 'dillman'],

            // -- PANADERIA (15) --
            ['nombre' => 'Pan de Molde Bimbo 500g', 'categoria' => 'panaderia', 'precio_compra' => 7.00, 'precio_venta' => 9.50, 'stock_actual' => 50, 'codigo_barras' => 'PAN001', 'pasillo' => '6', 'nivel' => 'A', 'unidad_medida' => 'un', 'proveedor' => 'distribuidora'],
            ['nombre' => 'Pan Integral Bimbo 500g', 'categoria' => 'panaderia', 'precio_compra' => 8.00, 'precio_venta' => 11.00, 'stock_actual' => 40, 'codigo_barras' => 'PAN002', 'pasillo' => '6', 'nivel' => 'A', 'unidad_medida' => 'un', 'proveedor' => 'distribuidora'],
            ['nombre' => 'Galletas Maria Casino 250g', 'categoria' => 'panaderia', 'precio_compra' => 4.00, 'precio_venta' => 5.50, 'stock_actual' => 90, 'codigo_barras' => 'PAN003', 'pasillo' => '6', 'nivel' => 'B', 'unidad_medida' => 'un', 'proveedor' => 'fino', 'en_descuento' => true, 'precio_oferta' => 4.50],
            ['nombre' => 'Galletas Soda Casino 250g', 'categoria' => 'panaderia', 'precio_compra' => 4.00, 'precio_venta' => 5.50, 'stock_actual' => 85, 'codigo_barras' => 'PAN004', 'pasillo' => '6', 'nivel' => 'B', 'unidad_medida' => 'un', 'proveedor' => 'fino'],
            ['nombre' => 'Bizcocho Salteno 200g', 'categoria' => 'panaderia', 'precio_compra' => 5.00, 'precio_venta' => 7.00, 'stock_actual' => 45, 'codigo_barras' => 'PAN005', 'pasillo' => '6', 'nivel' => 'A', 'unidad_medida' => 'un', 'proveedor' => 'distribuidora'],
            ['nombre' => 'Tortillas de Harima 6un', 'categoria' => 'panaderia', 'precio_compra' => 6.00, 'precio_venta' => 8.50, 'stock_actual' => 60, 'codigo_barras' => 'PAN006', 'pasillo' => '6', 'nivel' => 'A', 'unidad_medida' => 'un', 'proveedor' => 'distribuidora'],
            ['nombre' => 'Paneton D Avila 250g', 'categoria' => 'panaderia', 'precio_compra' => 6.00, 'precio_venta' => 8.50, 'stock_actual' => 30, 'codigo_barras' => 'PAN007', 'pasillo' => '6', 'nivel' => 'B', 'unidad_medida' => 'un', 'proveedor' => 'distribuidora'],
            ['nombre' => 'Pan de Muerto 200g', 'categoria' => 'panaderia', 'precio_compra' => 3.00, 'precio_venta' => 4.50, 'stock_actual' => 40, 'codigo_barras' => 'PAN008', 'pasillo' => '6', 'nivel' => 'A', 'unidad_medida' => 'un', 'proveedor' => 'distribuidora'],
            ['nombre' => 'Galletas Rellenas Casino Vainilla 300g', 'categoria' => 'panaderia', 'precio_compra' => 5.00, 'precio_venta' => 7.00, 'stock_actual' => 70, 'codigo_barras' => 'PAN009', 'pasillo' => '6', 'nivel' => 'B', 'unidad_medida' => 'un', 'proveedor' => 'fino', 'en_descuento' => true, 'precio_oferta' => 5.90],
            ['nombre' => 'Galletas Chokochips Casino 300g', 'categoria' => 'panaderia', 'precio_compra' => 6.00, 'precio_venta' => 8.50, 'stock_actual' => 55, 'codigo_barras' => 'PAN010', 'pasillo' => '6', 'nivel' => 'B', 'unidad_medida' => 'un', 'proveedor' => 'fino'],
            ['nombre' => 'Pan Blandito Bimbo 380g', 'categoria' => 'panaderia', 'precio_compra' => 8.00, 'precio_venta' => 11.00, 'stock_actual' => 35, 'codigo_barras' => 'PAN011', 'pasillo' => '6', 'nivel' => 'A', 'unidad_medida' => 'un', 'proveedor' => 'distribuidora'],
            ['nombre' => 'Croissant Bimbo 200g', 'categoria' => 'panaderia', 'precio_compra' => 7.00, 'precio_venta' => 9.50, 'stock_actual' => 25, 'codigo_barras' => 'PAN012', 'pasillo' => '6', 'nivel' => 'A', 'unidad_medida' => 'un', 'proveedor' => 'distribuidora'],
            ['nombre' => 'Muffin Vainilla 100g', 'categoria' => 'panaderia', 'precio_compra' => 3.50, 'precio_venta' => 5.00, 'stock_actual' => 50, 'codigo_barras' => 'PAN013', 'pasillo' => '6', 'nivel' => 'A', 'unidad_medida' => 'un', 'proveedor' => 'distribuidora'],
            ['nombre' => 'Pan de Hamburguesa Bimbo 4un', 'categoria' => 'panaderia', 'precio_compra' => 6.00, 'precio_venta' => 8.50, 'stock_actual' => 40, 'codigo_barras' => 'PAN014', 'pasillo' => '6', 'nivel' => 'A', 'unidad_medida' => 'un', 'proveedor' => 'distribuidora'],
            ['nombre' => 'Rosquillas Caseras 200g', 'categoria' => 'panaderia', 'precio_compra' => 4.00, 'precio_venta' => 5.50, 'stock_actual' => 35, 'codigo_barras' => 'PAN015', 'pasillo' => '6', 'nivel' => 'A', 'unidad_medida' => 'un', 'proveedor' => 'distribuidora'],

            // -- CARNES Y EMBUTIDOS (20) --
            ['nombre' => 'Carne de Res Molida 1kg', 'categoria' => 'carnes', 'precio_compra' => 22.00, 'precio_venta' => 30.00, 'stock_actual' => 40, 'codigo_barras' => 'CAR001', 'pasillo' => '7', 'nivel' => 'A', 'unidad_medida' => 'kg', 'proveedor' => 'distribuidora', 'es_perecedero' => true, 'en_descuento' => true, 'precio_oferta' => 26.00],
            ['nombre' => 'Carne de Res en Trozo 1kg', 'categoria' => 'carnes', 'precio_compra' => 25.00, 'precio_venta' => 34.00, 'stock_actual' => 30, 'codigo_barras' => 'CAR002', 'pasillo' => '7', 'nivel' => 'A', 'unidad_medida' => 'kg', 'proveedor' => 'distribuidora', 'es_perecedero' => true],
            ['nombre' => 'Pollo Entero 1kg', 'categoria' => 'carnes', 'precio_compra' => 14.00, 'precio_venta' => 19.00, 'stock_actual' => 50, 'codigo_barras' => 'CAR003', 'pasillo' => '7', 'nivel' => 'A', 'unidad_medida' => 'kg', 'proveedor' => 'distribuidora', 'es_perecedero' => true],
            ['nombre' => 'Pechuga de Pollo 1kg', 'categoria' => 'carnes', 'precio_compra' => 18.00, 'precio_venta' => 24.00, 'stock_actual' => 35, 'codigo_barras' => 'CAR004', 'pasillo' => '7', 'nivel' => 'A', 'unidad_medida' => 'kg', 'proveedor' => 'distribuidora', 'es_perecedero' => true],
            ['nombre' => 'Salchicha Vienesa Rosvil 300g', 'categoria' => 'carnes', 'precio_compra' => 8.00, 'precio_venta' => 11.00, 'stock_actual' => 60, 'codigo_barras' => 'CAR005', 'pasillo' => '7', 'nivel' => 'B', 'unidad_medida' => 'un', 'proveedor' => 'distribuidora', 'es_perecedero' => true, 'en_descuento' => true, 'precio_oferta' => 9.50],
            ['nombre' => 'Jamon de Cerdo Rosvil 250g', 'categoria' => 'carnes', 'precio_compra' => 10.00, 'precio_venta' => 14.00, 'stock_actual' => 45, 'codigo_barras' => 'CAR006', 'pasillo' => '7', 'nivel' => 'B', 'unidad_medida' => 'un', 'proveedor' => 'distribuidora', 'es_perecedero' => true],
            ['nombre' => 'Chorizo Parrillero 500g', 'categoria' => 'carnes', 'precio_compra' => 12.00, 'precio_venta' => 16.00, 'stock_actual' => 35, 'codigo_barras' => 'CAR007', 'pasillo' => '7', 'nivel' => 'A', 'unidad_medida' => 'un', 'proveedor' => 'distribuidora', 'es_perecedero' => true],
            ['nombre' => 'Carne de Cerdo 1kg', 'categoria' => 'carnes', 'precio_compra' => 18.00, 'precio_venta' => 24.00, 'stock_actual' => 25, 'codigo_barras' => 'CAR008', 'pasillo' => '7', 'nivel' => 'A', 'unidad_medida' => 'kg', 'proveedor' => 'distribuidora', 'es_perecedero' => true],
            ['nombre' => 'Milanesa de Pollo 500g', 'categoria' => 'carnes', 'precio_compra' => 10.00, 'precio_venta' => 14.00, 'stock_actual' => 30, 'codigo_barras' => 'CAR009', 'pasillo' => '7', 'nivel' => 'B', 'unidad_medida' => 'un', 'proveedor' => 'distribuidora', 'es_perecedero' => true, 'en_descuento' => true, 'precio_oferta' => 12.00],
            ['nombre' => 'Hamburguesa de Res 500g', 'categoria' => 'carnes', 'precio_compra' => 11.00, 'precio_venta' => 15.00, 'stock_actual' => 40, 'codigo_barras' => 'CAR010', 'pasillo' => '7', 'nivel' => 'B', 'unidad_medida' => 'un', 'proveedor' => 'distribuidora', 'es_perecedero' => true],
            ['nombre' => 'Tocino Ahumado 200g', 'categoria' => 'carnes', 'precio_compra' => 9.00, 'precio_venta' => 12.50, 'stock_actual' => 30, 'codigo_barras' => 'CAR011', 'pasillo' => '7', 'nivel' => 'B', 'unidad_medida' => 'un', 'proveedor' => 'distribuidora', 'es_perecedero' => true],
            ['nombre' => 'Sobrasada Rosvil 200g', 'categoria' => 'carnes', 'precio_compra' => 8.00, 'precio_venta' => 11.00, 'stock_actual' => 35, 'codigo_barras' => 'CAR012', 'pasillo' => '7', 'nivel' => 'B', 'unidad_medida' => 'un', 'proveedor' => 'distribuidora', 'es_perecedero' => true],
            ['nombre' => 'Alitas de Pollo 1kg', 'categoria' => 'carnes', 'precio_compra' => 12.00, 'precio_venta' => 16.00, 'stock_actual' => 30, 'codigo_barras' => 'CAR013', 'pasillo' => '7', 'nivel' => 'A', 'unidad_medida' => 'kg', 'proveedor' => 'distribuidora', 'es_perecedero' => true],
            ['nombre' => 'Mortadela Rosvil 500g', 'categoria' => 'carnes', 'precio_compra' => 7.00, 'precio_venta' => 9.50, 'stock_actual' => 55, 'codigo_barras' => 'CAR014', 'pasillo' => '7', 'nivel' => 'B', 'unidad_medida' => 'un', 'proveedor' => 'distribuidora', 'es_perecedero' => true],
            ['nombre' => 'Costilla de Res 1kg', 'categoria' => 'carnes', 'precio_compra' => 20.00, 'precio_venta' => 27.00, 'stock_actual' => 20, 'codigo_barras' => 'CAR015', 'pasillo' => '7', 'nivel' => 'A', 'unidad_medida' => 'kg', 'proveedor' => 'distribuidora', 'es_perecedero' => true],
            ['nombre' => 'Carne de Cordero 1kg', 'categoria' => 'carnes', 'precio_compra' => 28.00, 'precio_venta' => 38.00, 'stock_actual' => 15, 'codigo_barras' => 'CAR016', 'pasillo' => '7', 'nivel' => 'A', 'unidad_medida' => 'kg', 'proveedor' => 'distribuidora', 'es_perecedero' => true],
            ['nombre' => 'Lomito de Cerdo 500g', 'categoria' => 'carnes', 'precio_compra' => 14.00, 'precio_venta' => 19.00, 'stock_actual' => 25, 'codigo_barras' => 'CAR017', 'pasillo' => '7', 'nivel' => 'A', 'unidad_medida' => 'un', 'proveedor' => 'distribuidora', 'es_perecedero' => true, 'en_descuento' => true, 'precio_oferta' => 16.00],
            ['nombre' => 'Pata de Cerdo 1kg', 'categoria' => 'carnes', 'precio_compra' => 10.00, 'precio_venta' => 14.00, 'stock_actual' => 20, 'codigo_barras' => 'CAR018', 'pasillo' => '7', 'nivel' => 'B', 'unidad_medida' => 'kg', 'proveedor' => 'distribuidora', 'es_perecedero' => true],
            ['nombre' => 'Chuleta de Cerdo 1kg', 'categoria' => 'carnes', 'precio_compra' => 16.00, 'precio_venta' => 22.00, 'stock_actual' => 20, 'codigo_barras' => 'CAR019', 'pasillo' => '7', 'nivel' => 'A', 'unidad_medida' => 'kg', 'proveedor' => 'distribuidora', 'es_perecedero' => true, 'en_descuento' => true, 'precio_oferta' => 18.50],
            ['nombre' => 'Salchicha Parrillera Rosvil 400g', 'categoria' => 'carnes', 'precio_compra' => 9.00, 'precio_venta' => 12.50, 'stock_actual' => 40, 'codigo_barras' => 'CAR020', 'pasillo' => '7', 'nivel' => 'B', 'unidad_medida' => 'un', 'proveedor' => 'distribuidora', 'es_perecedero' => true],

            // -- FRUTAS Y VERDURAS (20) --
            ['nombre' => 'Papa Blanca 1kg', 'categoria' => 'frutas', 'precio_compra' => 3.00, 'precio_venta' => 4.50, 'stock_actual' => 100, 'codigo_barras' => 'FRS001', 'pasillo' => '8', 'nivel' => 'A', 'unidad_medida' => 'kg', 'proveedor' => 'distribuidora', 'es_perecedero' => true],
            ['nombre' => 'Cebolla 1kg', 'categoria' => 'frutas', 'precio_compra' => 3.50, 'precio_venta' => 5.00, 'stock_actual' => 90, 'codigo_barras' => 'FRS002', 'pasillo' => '8', 'nivel' => 'A', 'unidad_medida' => 'kg', 'proveedor' => 'distribuidora', 'es_perecedero' => true],
            ['nombre' => 'Tomate 1kg', 'categoria' => 'frutas', 'precio_compra' => 4.00, 'precio_venta' => 5.50, 'stock_actual' => 80, 'codigo_barras' => 'FRS003', 'pasillo' => '8', 'nivel' => 'A', 'unidad_medida' => 'kg', 'proveedor' => 'distribuidora', 'es_perecedero' => true, 'en_descuento' => true, 'precio_oferta' => 4.50],
            ['nombre' => 'Platano 1kg', 'categoria' => 'frutas', 'precio_compra' => 4.00, 'precio_venta' => 5.50, 'stock_actual' => 85, 'codigo_barras' => 'FRS004', 'pasillo' => '8', 'nivel' => 'B', 'unidad_medida' => 'kg', 'proveedor' => 'distribuidora', 'es_perecedero' => true],
            ['nombre' => 'Manzana Nacional 1kg', 'categoria' => 'frutas', 'precio_compra' => 6.00, 'precio_venta' => 8.50, 'stock_actual' => 60, 'codigo_barras' => 'FRS005', 'pasillo' => '8', 'nivel' => 'B', 'unidad_medida' => 'kg', 'proveedor' => 'distribuidora', 'es_perecedero' => true],
            ['nombre' => 'Naranja 1kg', 'categoria' => 'frutas', 'precio_compra' => 5.00, 'precio_venta' => 7.00, 'stock_actual' => 70, 'codigo_barras' => 'FRS006', 'pasillo' => '8', 'nivel' => 'B', 'unidad_medida' => 'kg', 'proveedor' => 'distribuidora', 'es_perecedero' => true, 'en_descuento' => true, 'precio_oferta' => 5.90],
            ['nombre' => 'Limon 1kg', 'categoria' => 'frutas', 'precio_compra' => 5.00, 'precio_venta' => 7.00, 'stock_actual' => 65, 'codigo_barras' => 'FRS007', 'pasillo' => '8', 'nivel' => 'B', 'unidad_medida' => 'kg', 'proveedor' => 'distribuidora', 'es_perecedero' => true],
            ['nombre' => 'Zanahoria 1kg', 'categoria' => 'frutas', 'precio_compra' => 3.00, 'precio_venta' => 4.50, 'stock_actual' => 75, 'codigo_barras' => 'FRS008', 'pasillo' => '8', 'nivel' => 'A', 'unidad_medida' => 'kg', 'proveedor' => 'distribuidora', 'es_perecedero' => true],
            ['nombre' => 'Lechuga 1un', 'categoria' => 'frutas', 'precio_compra' => 2.00, 'precio_venta' => 3.00, 'stock_actual' => 50, 'codigo_barras' => 'FRS009', 'pasillo' => '8', 'nivel' => 'C', 'unidad_medida' => 'un', 'proveedor' => 'distribuidora', 'es_perecedero' => true],
            ['nombre' => 'Locoto 1kg', 'categoria' => 'frutas', 'precio_compra' => 6.00, 'precio_venta' => 8.50, 'stock_actual' => 40, 'codigo_barras' => 'FRS010', 'pasillo' => '8', 'nivel' => 'B', 'unidad_medida' => 'kg', 'proveedor' => 'distribuidora', 'es_perecedero' => true],
            ['nombre' => 'Durazno 1kg', 'categoria' => 'frutas', 'precio_compra' => 7.00, 'precio_venta' => 9.50, 'stock_actual' => 35, 'codigo_barras' => 'FRS011', 'pasillo' => '8', 'nivel' => 'B', 'unidad_medida' => 'kg', 'proveedor' => 'distribuidora', 'es_perecedero' => true],
            ['nombre' => 'Uva 1kg', 'categoria' => 'frutas', 'precio_compra' => 10.00, 'precio_venta' => 14.00, 'stock_actual' => 25, 'codigo_barras' => 'FRS012', 'pasillo' => '8', 'nivel' => 'B', 'unidad_medida' => 'kg', 'proveedor' => 'distribuidora', 'es_perecedero' => true, 'en_descuento' => true, 'precio_oferta' => 11.90],
            ['nombre' => 'Frutilla 1kg', 'categoria' => 'frutas', 'precio_compra' => 12.00, 'precio_venta' => 16.00, 'stock_actual' => 20, 'codigo_barras' => 'FRS013', 'pasillo' => '8', 'nivel' => 'B', 'unidad_medida' => 'kg', 'proveedor' => 'distribuidora', 'es_perecedero' => true],
            ['nombre' => 'Pimiento Morron 1kg', 'categoria' => 'frutas', 'precio_compra' => 7.00, 'precio_venta' => 9.50, 'stock_actual' => 45, 'codigo_barras' => 'FRS014', 'pasillo' => '8', 'nivel' => 'A', 'unidad_medida' => 'kg', 'proveedor' => 'distribuidora', 'es_perecedero' => true],
            ['nombre' => 'Ajo 250g', 'categoria' => 'frutas', 'precio_compra' => 4.00, 'precio_venta' => 5.50, 'stock_actual' => 60, 'codigo_barras' => 'FRS015', 'pasillo' => '8', 'nivel' => 'C', 'unidad_medida' => 'un', 'proveedor' => 'distribuidora', 'es_perecedero' => true],
            ['nombre' => 'Pera 1kg', 'categoria' => 'frutas', 'precio_compra' => 7.00, 'precio_venta' => 9.50, 'stock_actual' => 30, 'codigo_barras' => 'FRS016', 'pasillo' => '8', 'nivel' => 'B', 'unidad_medida' => 'kg', 'proveedor' => 'distribuidora', 'es_perecedero' => true],
            ['nombre' => 'Sandia 1un', 'categoria' => 'frutas', 'precio_compra' => 12.00, 'precio_venta' => 16.00, 'stock_actual' => 15, 'codigo_barras' => 'FRS017', 'pasillo' => '8', 'nivel' => 'C', 'unidad_medida' => 'un', 'proveedor' => 'distribuidora', 'es_perecedero' => true],
            ['nombre' => 'Pina 1un', 'categoria' => 'frutas', 'precio_compra' => 8.00, 'precio_venta' => 11.00, 'stock_actual' => 20, 'codigo_barras' => 'FRS018', 'pasillo' => '8', 'nivel' => 'C', 'unidad_medida' => 'un', 'proveedor' => 'distribuidora', 'es_perecedero' => true, 'en_descuento' => true, 'precio_oferta' => 9.00],
            ['nombre' => 'Mango 1kg', 'categoria' => 'frutas', 'precio_compra' => 8.00, 'precio_venta' => 11.00, 'stock_actual' => 25, 'codigo_barras' => 'FRS019', 'pasillo' => '8', 'nivel' => 'B', 'unidad_medida' => 'kg', 'proveedor' => 'distribuidora', 'es_perecedero' => true],
            ['nombre' => 'Habas 1kg', 'categoria' => 'frutas', 'precio_compra' => 5.00, 'precio_venta' => 7.00, 'stock_actual' => 35, 'codigo_barras' => 'FRS020', 'pasillo' => '8', 'nivel' => 'A', 'unidad_medida' => 'kg', 'proveedor' => 'distribuidora', 'es_perecedero' => true],

            // -- SNACKS Y GOLOSINAS (20) --
            ['nombre' => 'Papas Fritas Fino 150g', 'categoria' => 'snacks', 'precio_compra' => 4.00, 'precio_venta' => 5.50, 'stock_actual' => 100, 'codigo_barras' => 'SNK001', 'pasillo' => '9', 'nivel' => 'A', 'unidad_medida' => 'un', 'proveedor' => 'fino'],
            ['nombre' => 'Papas Fritas Fino Jalapeno 150g', 'categoria' => 'snacks', 'precio_compra' => 4.50, 'precio_venta' => 6.00, 'stock_actual' => 90, 'codigo_barras' => 'SNK002', 'pasillo' => '9', 'nivel' => 'A', 'unidad_medida' => 'un', 'proveedor' => 'fino'],
            ['nombre' => 'Chizitos Fino 100g', 'categoria' => 'snacks', 'precio_compra' => 3.00, 'precio_venta' => 4.50, 'stock_actual' => 110, 'codigo_barras' => 'SNK003', 'pasillo' => '9', 'nivel' => 'A', 'unidad_medida' => 'un', 'proveedor' => 'fino', 'en_descuento' => true, 'precio_oferta' => 3.50],
            ['nombre' => 'Mani Salado Fino 200g', 'categoria' => 'snacks', 'precio_compra' => 3.50, 'precio_venta' => 5.00, 'stock_actual' => 80, 'codigo_barras' => 'SNK004', 'pasillo' => '9', 'nivel' => 'B', 'unidad_medida' => 'un', 'proveedor' => 'fino'],
            ['nombre' => 'Chocolate Savoy 100g', 'categoria' => 'snacks', 'precio_compra' => 7.00, 'precio_venta' => 9.50, 'stock_actual' => 60, 'codigo_barras' => 'SNK005', 'pasillo' => '9', 'nivel' => 'B', 'unidad_medida' => 'un', 'proveedor' => 'fino', 'en_descuento' => true, 'precio_oferta' => 7.90],
            ['nombre' => 'Caramelos Savoy surtido 200g', 'categoria' => 'snacks', 'precio_compra' => 3.00, 'precio_venta' => 4.50, 'stock_actual' => 120, 'codigo_barras' => 'SNK006', 'pasillo' => '9', 'nivel' => 'B', 'unidad_medida' => 'un', 'proveedor' => 'fino'],
            ['nombre' => 'Chicles Tutti Frutti 50g', 'categoria' => 'snacks', 'precio_compra' => 2.00, 'precio_venta' => 3.00, 'stock_actual' => 150, 'codigo_barras' => 'SNK007', 'pasillo' => '9', 'nivel' => 'C', 'unidad_medida' => 'un', 'proveedor' => 'fino'],
            ['nombre' => 'Palomitas de Maiz Fino 100g', 'categoria' => 'snacks', 'precio_compra' => 3.00, 'precio_venta' => 4.50, 'stock_actual' => 70, 'codigo_barras' => 'SNK008', 'pasillo' => '9', 'nivel' => 'A', 'unidad_medida' => 'un', 'proveedor' => 'fino'],
            ['nombre' => 'Galletas Wafer Vainilla 150g', 'categoria' => 'snacks', 'precio_compra' => 4.00, 'precio_venta' => 5.50, 'stock_actual' => 85, 'codigo_barras' => 'SNK009', 'pasillo' => '9', 'nivel' => 'B', 'unidad_medida' => 'un', 'proveedor' => 'fino'],
            ['nombre' => 'Galletas Wafer Chocolate 150g', 'categoria' => 'snacks', 'precio_compra' => 4.00, 'precio_venta' => 5.50, 'stock_actual' => 80, 'codigo_barras' => 'SNK010', 'pasillo' => '9', 'nivel' => 'B', 'unidad_medida' => 'un', 'proveedor' => 'fino'],
            ['nombre' => 'Mani Japones 100g', 'categoria' => 'snacks', 'precio_compra' => 4.00, 'precio_venta' => 5.50, 'stock_actual' => 65, 'codigo_barras' => 'SNK011', 'pasillo' => '9', 'nivel' => 'B', 'unidad_medida' => 'un', 'proveedor' => 'fino', 'en_descuento' => true, 'precio_oferta' => 4.50],
            ['nombre' => 'Tostada Saltada Fino 100g', 'categoria' => 'snacks', 'precio_compra' => 3.50, 'precio_venta' => 5.00, 'stock_actual' => 75, 'codigo_barras' => 'SNK012', 'pasillo' => '9', 'nivel' => 'A', 'unidad_medida' => 'un', 'proveedor' => 'fino'],
            ['nombre' => 'Alfajor Savoy 50g', 'categoria' => 'snacks', 'precio_compra' => 2.00, 'precio_venta' => 3.00, 'stock_actual' => 130, 'codigo_barras' => 'SNK013', 'pasillo' => '9', 'nivel' => 'B', 'unidad_medida' => 'un', 'proveedor' => 'fino'],
            ['nombre' => 'Gomitas Savoy 100g', 'categoria' => 'snacks', 'precio_compra' => 3.00, 'precio_venta' => 4.50, 'stock_actual' => 90, 'codigo_barras' => 'SNK014', 'pasillo' => '9', 'nivel' => 'B', 'unidad_medida' => 'un', 'proveedor' => 'fino', 'en_descuento' => true, 'precio_oferta' => 3.50],
            ['nombre' => 'Chocolate Blanco Savoy 100g', 'categoria' => 'snacks', 'precio_compra' => 7.00, 'precio_venta' => 9.50, 'stock_actual' => 50, 'codigo_barras' => 'SNK015', 'pasillo' => '9', 'nivel' => 'B', 'unidad_medida' => 'un', 'proveedor' => 'fino'],
            ['nombre' => 'Mix de Frutos Secos 200g', 'categoria' => 'snacks', 'precio_compra' => 8.00, 'precio_venta' => 11.00, 'stock_actual' => 40, 'codigo_barras' => 'SNK016', 'pasillo' => '9', 'nivel' => 'B', 'unidad_medida' => 'un', 'proveedor' => 'fino'],
            ['nombre' => 'Galletas Rellenas Fino Vainilla 200g', 'categoria' => 'snacks', 'precio_compra' => 5.00, 'precio_venta' => 7.00, 'stock_actual' => 70, 'codigo_barras' => 'SNK017', 'pasillo' => '9', 'nivel' => 'A', 'unidad_medida' => 'un', 'proveedor' => 'fino', 'en_descuento' => true, 'precio_oferta' => 5.90],
            ['nombre' => 'Barra de Cereal Savoy Avena 40g', 'categoria' => 'snacks', 'precio_compra' => 2.50, 'precio_venta' => 3.50, 'stock_actual' => 100, 'codigo_barras' => 'SNK018', 'pasillo' => '9', 'nivel' => 'B', 'unidad_medida' => 'un', 'proveedor' => 'fino'],
            ['nombre' => 'Chizitos Fino 200g', 'categoria' => 'snacks', 'precio_compra' => 5.00, 'precio_venta' => 7.00, 'stock_actual' => 60, 'codigo_barras' => 'SNK019', 'pasillo' => '9', 'nivel' => 'A', 'unidad_medida' => 'un', 'proveedor' => 'fino', 'en_descuento' => true, 'precio_oferta' => 5.50],
            ['nombre' => 'Pasankalla Fino 150g', 'categoria' => 'snacks', 'precio_compra' => 4.00, 'precio_venta' => 5.50, 'stock_actual' => 80, 'codigo_barras' => 'SNK020', 'pasillo' => '9', 'nivel' => 'B', 'unidad_medida' => 'un', 'proveedor' => 'fino'],
        ];
    }
}
