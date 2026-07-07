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
        $proveedores = $this->createProveedores();
        $categorias = $this->createCategorias();
        $productos = $this->getProductosData();

        foreach ($productos as $data) {
            $catKey = $data['categoria'];
            $catId = $categorias[$catKey]->id;
            $imagen = 'prod_' . $data['codigo_barras'] . '.webp';

            $producto = Producto::firstOrCreate(
                ['codigo_barras' => $data['codigo_barras']],
                [
                    'nombre' => $data['nombre'],
                    'descripcion' => $data['descripcion'],
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
                    'fecha_vencimiento' => $data['fecha_vencimiento'] ?? null,
                    'lote' => $data['lote'] ?? null,
                    'fecha_ingreso' => $data['fecha_ingreso'] ?? null,
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

        $this->command->info(count($productos) . ' productos creados correctamente.');
    }

    protected function createProveedores(): array
    {
        $proveedores = [
            'distribuidora_mayorista' => Proveedor::firstOrCreate(
                ['nombre' => 'Distribuidora Mayorista Bolivia S.R.L.'],
                ['contacto' => 'Carlos López', 'direccion' => 'Av. Circunvalación #500, Potosí', 'telefono' => '26543210', 'email' => 'ventas@distribuidorabolivia.bo']
            ),
            'apple' => Proveedor::firstOrCreate(
                ['nombre' => 'iShop Bolivia S.R.L.'],
                ['contacto' => 'María García', 'direccion' => 'MegaCenter Local 15, Av. San Martín, Santa Cruz', 'telefono' => '33567890', 'email' => 'ventas@ishopbolivia.bo']
            ),
            'samsung' => Proveedor::firstOrCreate(
                ['nombre' => 'Samsung Electronics Bolivia S.A.'],
                ['contacto' => 'Pedro Quispe', 'direccion' => 'Av. Ballivián #1234, Edif. Samsung, La Paz', 'telefono' => '22456789', 'email' => 'contacto@samsung.bo']
            ),
            'huawei' => Proveedor::firstOrCreate(
                ['nombre' => 'Huawei Technologies Bolivia'],
                ['contacto' => 'Ana Morales', 'direccion' => 'Av. 6 de Agosto #2450, La Paz', 'telefono' => '24443322', 'email' => 'ventas@huawei.bo']
            ),
            'nokia' => Proveedor::firstOrCreate(
                ['nombre' => 'Nokia Solutions Bolivia'],
                ['contacto' => 'Juan Pérez', 'direccion' => 'Calle Potosí #789, Cochabamba', 'telefono' => '46667788', 'email' => 'info@nokiabolivia.bo']
            ),
            'microsoft' => Proveedor::firstOrCreate(
                ['nombre' => 'Microsoft Bolivia S.R.L.'],
                ['contacto' => 'Roberto Vargas', 'direccion' => 'Av. Montenegro #1155, Calacoto, La Paz', 'telefono' => '22778899', 'email' => 'ventas@microsoft.bo']
            ),
            'lenovo' => Proveedor::firstOrCreate(
                ['nombre' => 'Lenovo Bolivia S.A.'],
                ['contacto' => 'Laura Méndez', 'direccion' => 'Av. América #345, Cochabamba', 'telefono' => '44556677', 'email' => 'contacto@lenovo.bo']
            ),
            'hp' => Proveedor::firstOrCreate(
                ['nombre' => 'HP Bolivia S.R.L.'],
                ['contacto' => 'Diego Gutiérrez', 'direccion' => 'Av. Busch #678, La Paz', 'telefono' => '22334455', 'email' => 'ventas@hp.bo']
            ),
            'dell' => Proveedor::firstOrCreate(
                ['nombre' => 'Dell Technologies Bolivia'],
                ['contacto' => 'Carmen Flores', 'direccion' => 'Av. Irala #456, Cochabamba', 'telefono' => '44998877', 'email' => 'soporte@dell.bo']
            ),
            'asus' => Proveedor::firstOrCreate(
                ['nombre' => 'ASUS Bolivia S.R.L.'],
                ['contacto' => 'Luis Torrez', 'direccion' => 'Calle 21 #890, Calacoto, La Paz', 'telefono' => '22776655', 'email' => 'ventas@asus.bo']
            ),
            'google' => Proveedor::firstOrCreate(
                ['nombre' => 'Google LLC Sucursal Bolivia'],
                ['contacto' => 'Andrea Ríos', 'direccion' => 'Av. Arce #2500, La Paz', 'telefono' => '24446688', 'email' => 'ads@google.bo']
            ),
            'amazon' => Proveedor::firstOrCreate(
                ['nombre' => 'Amazon Web Services Bolivia'],
                ['contacto' => 'Pablo Castillo', 'direccion' => 'Av. San Martín #1500, Santa Cruz', 'telefono' => '33557799', 'email' => 'aws@amazon.bo']
            ),
            'sony' => Proveedor::firstOrCreate(
                ['nombre' => 'Sony Bolivia S.A.'],
                ['contacto' => 'Jorge Fernández', 'direccion' => 'Av. 6 de Agosto #1987, La Paz', 'telefono' => '24446666', 'email' => 'contacto@sony.bo']
            ),
            'lg' => Proveedor::firstOrCreate(
                ['nombre' => 'LG Electronics Bolivia S.R.L.'],
                ['contacto' => 'Mónica Rojas', 'direccion' => 'Av. Blanco Galindo km 5, Cochabamba', 'telefono' => '44889900', 'email' => 'ventas@lg.bo']
            ),
            'philips' => Proveedor::firstOrCreate(
                ['nombre' => 'Philips Bolivia S.A.'],
                ['contacto' => 'Ricardo Méndez', 'direccion' => 'Av. Camacho #1356, La Paz', 'telefono' => '22445566', 'email' => 'info@philips.bo']
            ),
            'nike' => Proveedor::firstOrCreate(
                ['nombre' => 'Nike Bolivia S.R.L.'],
                ['contacto' => 'Karina Salazar', 'direccion' => 'Ventura Mall Local 25, Santa Cruz', 'telefono' => '33442211', 'email' => 'ventas@nike.bo']
            ),
            'adidas' => Proveedor::firstOrCreate(
                ['nombre' => 'Adidas Bolivia S.A.'],
                ['contacto' => 'Fernando Roca', 'direccion' => 'MegaCenter Local 50, Santa Cruz', 'telefono' => '33443344', 'email' => 'contacto@adidas.bo']
            ),
            'puma' => Proveedor::firstOrCreate(
                ['nombre' => 'Puma Sports Bolivia'],
                ['contacto' => 'Sofía Vargas', 'direccion' => 'Av. San Martín #800, Santa Cruz', 'telefono' => '33556677', 'email' => 'ventas@puma.bo']
            ),
            'reebok' => Proveedor::firstOrCreate(
                ['nombre' => 'Reebok Bolivia S.R.L.'],
                ['contacto' => 'Marcelo Torres', 'direccion' => 'Calle 21 #456, La Paz', 'telefono' => '22778800', 'email' => 'info@reebok.bo']
            ),
            'gucci' => Proveedor::firstOrCreate(
                ['nombre' => 'Gucci Bolivia S.A.'],
                ['contacto' => 'Elena Pardo', 'direccion' => 'Av. Ballivián #2000, La Paz', 'telefono' => '22447788', 'email' => 'ventas@gucci.bo']
            ),
            'dior' => Proveedor::firstOrCreate(
                ['nombre' => 'Dior Bolivia S.R.L.'],
                ['contacto' => 'Camila Orozco', 'direccion' => 'Calle 10 #345, Calacoto, La Paz', 'telefono' => '22779900', 'email' => 'contacto@dior.bo']
            ),
            'chanel' => Proveedor::firstOrCreate(
                ['nombre' => 'Chanel Bolivia S.A.'],
                ['contacto' => 'Ximena Loza', 'direccion' => 'Av. Montenegro #1000, La Paz', 'telefono' => '22446677', 'email' => 'ventas@chanel.bo']
            ),
            'calvin_klein' => Proveedor::firstOrCreate(
                ['nombre' => 'Calvin Klein Bolivia'],
                ['contacto' => 'Daniela Peredo', 'direccion' => 'Ventura Mall Local 30, Santa Cruz', 'telefono' => '33440011', 'email' => 'info@ck.bo']
            ),
            'nestle' => Proveedor::firstOrCreate(
                ['nombre' => 'Nestlé Bolivia S.A.'],
                ['contacto' => 'Raúl Quispe', 'direccion' => 'Av. 6 de Agosto #2000, La Paz', 'telefono' => '22441122', 'email' => 'pedidos@nestle.bo']
            ),
            'coca_cola' => Proveedor::firstOrCreate(
                ['nombre' => 'Embotelladora Boliviana (Embol)'],
                ['contacto' => 'Laura Méndez', 'direccion' => 'Av. Ecológica #200, Santa Cruz', 'telefono' => '35556677', 'email' => 'pedidos@embol.bo']
            ),
            'pepsi' => Proveedor::firstOrCreate(
                ['nombre' => 'PepsiCo Alimentos Bolivia'],
                ['contacto' => 'José Luis Ríos', 'direccion' => 'Zona Industrial #789, Cochabamba', 'telefono' => '44778899', 'email' => 'ventas@pepsico.bo']
            ),
            'unilever' => Proveedor::firstOrCreate(
                ['nombre' => 'Unilever Andina Bolivia S.R.L.'],
                ['contacto' => 'Patricia Díaz', 'direccion' => 'Av. Ecuador #1500, La Paz', 'telefono' => '22447766', 'email' => 'contacto@unilever.bo']
            ),
            'procter_gamble' => Proveedor::firstOrCreate(
                ['nombre' => 'Procter & Gamble Bolivia S.R.L.'],
                ['contacto' => 'Oscar Fernández', 'direccion' => 'Av. Ballivián #500, Cochabamba', 'telefono' => '44881100', 'email' => 'ventas@pg.bo']
            ),
            'loreal' => Proveedor::firstOrCreate(
                ['nombre' => 'L\'Oréal Bolivia S.A.'],
                ['contacto' => 'Claudia Álvarez', 'direccion' => 'Av. Arce #2800, La Paz', 'telefono' => '22446688', 'email' => 'contacto@loreal.bo']
            ),
            'essence' => Proveedor::firstOrCreate(
                ['nombre' => 'Importadora Bela Vista S.R.L.'],
                ['contacto' => 'Roberto Pinto', 'direccion' => 'Calle Comercio #456, Potosí', 'telefono' => '26223344', 'email' => 'ventas@belavista.bo']
            ),
            'olay' => Proveedor::firstOrCreate(
                ['nombre' => 'Distribuidora de Cosméticos del Sur'],
                ['contacto' => 'María Fernanda Roca', 'direccion' => 'Av. Monseñor Fife #123, Tarija', 'telefono' => '46661234', 'email' => 'ventas@dicosur.bo']
            ),
            'vaseline' => Proveedor::firstOrCreate(
                ['nombre' => 'Importadora Unifarma Bolivia'],
                ['contacto' => 'Carlos Mendoza', 'direccion' => 'Calle Bolívar #567, La Paz', 'telefono' => '22443322', 'email' => 'pedidos@unifarma.bo']
            ),
            'puma_energy' => Proveedor::firstOrCreate(
                ['nombre' => 'Bebidas Energéticas Bolivia S.R.L.'],
                ['contacto' => 'Mario Vargas', 'direccion' => 'Av. Circunvalación #890, Potosí', 'telefono' => '26554433', 'email' => 'ventas@bebidasenergeticas.bo']
            ),
            'glamour_beauty' => Proveedor::firstOrCreate(
                ['nombre' => 'Importadora Glamour Beauty Bolivia S.R.L.'],
                ['contacto' => 'Contacto RedMarket', 'direccion' => 'Calle Comercio #100, Potosí', 'telefono' => '26220000', 'email' => 'glamourbeauty@proveedor.bo']
            ),
            'velvet_touch' => Proveedor::firstOrCreate(
                ['nombre' => 'Importadora Velvet Touch Bolivia S.R.L.'],
                ['contacto' => 'Contacto RedMarket', 'direccion' => 'Calle Comercio #100, Potosí', 'telefono' => '26220000', 'email' => 'velvettouch@proveedor.bo']
            ),
            'chic_cosmetics' => Proveedor::firstOrCreate(
                ['nombre' => 'Importadora Chic Cosmetics Bolivia S.R.L.'],
                ['contacto' => 'Contacto RedMarket', 'direccion' => 'Calle Comercio #100, Potosí', 'telefono' => '26220000', 'email' => 'chiccosmetics@proveedor.bo']
            ),
            'nail_couture' => Proveedor::firstOrCreate(
                ['nombre' => 'Importadora Nail Couture Bolivia S.R.L.'],
                ['contacto' => 'Contacto RedMarket', 'direccion' => 'Calle Comercio #100, Potosí', 'telefono' => '26220000', 'email' => 'nailcouture@proveedor.bo']
            ),
            'dolce_gabbana' => Proveedor::firstOrCreate(
                ['nombre' => 'Importadora Dolce Gabbana Bolivia S.R.L.'],
                ['contacto' => 'Contacto RedMarket', 'direccion' => 'Calle Comercio #100, Potosí', 'telefono' => '26220000', 'email' => 'dolcegabbana@proveedor.bo']
            ),
            'annibale_colombo' => Proveedor::firstOrCreate(
                ['nombre' => 'Importadora Annibale Colombo Bolivia S.R.L.'],
                ['contacto' => 'Contacto RedMarket', 'direccion' => 'Calle Comercio #100, Potosí', 'telefono' => '26220000', 'email' => 'annibalecolombo@proveedor.bo']
            ),
            'furniture_co' => Proveedor::firstOrCreate(
                ['nombre' => 'Importadora Furniture Co Bolivia S.R.L.'],
                ['contacto' => 'Contacto RedMarket', 'direccion' => 'Calle Comercio #100, Potosí', 'telefono' => '26220000', 'email' => 'furnitureco@proveedor.bo']
            ),
            'knoll' => Proveedor::firstOrCreate(
                ['nombre' => 'Importadora Knoll Bolivia S.R.L.'],
                ['contacto' => 'Contacto RedMarket', 'direccion' => 'Calle Comercio #100, Potosí', 'telefono' => '26220000', 'email' => 'knoll@proveedor.bo']
            ),
            'bath_trends' => Proveedor::firstOrCreate(
                ['nombre' => 'Importadora Bath Trends Bolivia S.R.L.'],
                ['contacto' => 'Contacto RedMarket', 'direccion' => 'Calle Comercio #100, Potosí', 'telefono' => '26220000', 'email' => 'bathtrends@proveedor.bo']
            ),
            'fashion_trends' => Proveedor::firstOrCreate(
                ['nombre' => 'Importadora Fashion Trends Bolivia S.R.L.'],
                ['contacto' => 'Contacto RedMarket', 'direccion' => 'Calle Comercio #100, Potosí', 'telefono' => '26220000', 'email' => 'fashiontrends@proveedor.bo']
            ),
            'gigabyte' => Proveedor::firstOrCreate(
                ['nombre' => 'Importadora Gigabyte Bolivia S.R.L.'],
                ['contacto' => 'Contacto RedMarket', 'direccion' => 'Calle Comercio #100, Potosí', 'telefono' => '26220000', 'email' => 'gigabyte@proveedor.bo']
            ),
            'classic_wear' => Proveedor::firstOrCreate(
                ['nombre' => 'Importadora Classic Wear Bolivia S.R.L.'],
                ['contacto' => 'Contacto RedMarket', 'direccion' => 'Calle Comercio #100, Potosí', 'telefono' => '26220000', 'email' => 'classicwear@proveedor.bo']
            ),
            'casual_comfort' => Proveedor::firstOrCreate(
                ['nombre' => 'Importadora Casual Comfort Bolivia S.R.L.'],
                ['contacto' => 'Contacto RedMarket', 'direccion' => 'Calle Comercio #100, Potosí', 'telefono' => '26220000', 'email' => 'casualcomfort@proveedor.bo']
            ),
            'urban_chic' => Proveedor::firstOrCreate(
                ['nombre' => 'Importadora Urban Chic Bolivia S.R.L.'],
                ['contacto' => 'Contacto RedMarket', 'direccion' => 'Calle Comercio #100, Potosí', 'telefono' => '26220000', 'email' => 'urbanchic@proveedor.bo']
            ),
            'off_white' => Proveedor::firstOrCreate(
                ['nombre' => 'Importadora Off White Bolivia S.R.L.'],
                ['contacto' => 'Contacto RedMarket', 'direccion' => 'Calle Comercio #100, Potosí', 'telefono' => '26220000', 'email' => 'offwhite@proveedor.bo']
            ),
            'fashion_timepieces' => Proveedor::firstOrCreate(
                ['nombre' => 'Importadora Fashion Timepieces Bolivia S.R.L.'],
                ['contacto' => 'Contacto RedMarket', 'direccion' => 'Calle Comercio #100, Potosí', 'telefono' => '26220000', 'email' => 'fashiontimepieces@proveedor.bo']
            ),
            'longines' => Proveedor::firstOrCreate(
                ['nombre' => 'Importadora Longines Bolivia S.R.L.'],
                ['contacto' => 'Contacto RedMarket', 'direccion' => 'Calle Comercio #100, Potosí', 'telefono' => '26220000', 'email' => 'longines@proveedor.bo']
            ),
            'rolex' => Proveedor::firstOrCreate(
                ['nombre' => 'Importadora Rolex Bolivia S.R.L.'],
                ['contacto' => 'Contacto RedMarket', 'direccion' => 'Calle Comercio #100, Potosí', 'telefono' => '26220000', 'email' => 'rolex@proveedor.bo']
            ),
            'beats' => Proveedor::firstOrCreate(
                ['nombre' => 'Importadora Beats Bolivia S.R.L.'],
                ['contacto' => 'Contacto RedMarket', 'direccion' => 'Calle Comercio #100, Potosí', 'telefono' => '26220000', 'email' => 'beats@proveedor.bo']
            ),
            'techgear' => Proveedor::firstOrCreate(
                ['nombre' => 'Importadora Techgear Bolivia S.R.L.'],
                ['contacto' => 'Contacto RedMarket', 'direccion' => 'Calle Comercio #100, Potosí', 'telefono' => '26220000', 'email' => 'techgear@proveedor.bo']
            ),
            'gadgetmaster' => Proveedor::firstOrCreate(
                ['nombre' => 'Importadora Gadgetmaster Bolivia S.R.L.'],
                ['contacto' => 'Contacto RedMarket', 'direccion' => 'Calle Comercio #100, Potosí', 'telefono' => '26220000', 'email' => 'gadgetmaster@proveedor.bo']
            ),
            'snaptech' => Proveedor::firstOrCreate(
                ['nombre' => 'Importadora Snaptech Bolivia S.R.L.'],
                ['contacto' => 'Contacto RedMarket', 'direccion' => 'Calle Comercio #100, Potosí', 'telefono' => '26220000', 'email' => 'snaptech@proveedor.bo']
            ),
            'provision' => Proveedor::firstOrCreate(
                ['nombre' => 'Importadora Provision Bolivia S.R.L.'],
                ['contacto' => 'Contacto RedMarket', 'direccion' => 'Calle Comercio #100, Potosí', 'telefono' => '26220000', 'email' => 'provision@proveedor.bo']
            ),
            'generic_motors' => Proveedor::firstOrCreate(
                ['nombre' => 'Importadora Generic Motors Bolivia S.R.L.'],
                ['contacto' => 'Contacto RedMarket', 'direccion' => 'Calle Comercio #100, Potosí', 'telefono' => '26220000', 'email' => 'genericmotors@proveedor.bo']
            ),
            'kawasaki' => Proveedor::firstOrCreate(
                ['nombre' => 'Importadora Kawasaki Bolivia S.R.L.'],
                ['contacto' => 'Contacto RedMarket', 'direccion' => 'Calle Comercio #100, Potosí', 'telefono' => '26220000', 'email' => 'kawasaki@proveedor.bo']
            ),
            'motogp' => Proveedor::firstOrCreate(
                ['nombre' => 'Importadora Motogp Bolivia S.R.L.'],
                ['contacto' => 'Contacto RedMarket', 'direccion' => 'Calle Comercio #100, Potosí', 'telefono' => '26220000', 'email' => 'motogp@proveedor.bo']
            ),
            'scootmaster' => Proveedor::firstOrCreate(
                ['nombre' => 'Importadora Scootmaster Bolivia S.R.L.'],
                ['contacto' => 'Contacto RedMarket', 'direccion' => 'Calle Comercio #100, Potosí', 'telefono' => '26220000', 'email' => 'scootmaster@proveedor.bo']
            ),
            'speedmaster' => Proveedor::firstOrCreate(
                ['nombre' => 'Importadora Speedmaster Bolivia S.R.L.'],
                ['contacto' => 'Contacto RedMarket', 'direccion' => 'Calle Comercio #100, Potosí', 'telefono' => '26220000', 'email' => 'speedmaster@proveedor.bo']
            ),
            'attitude' => Proveedor::firstOrCreate(
                ['nombre' => 'Importadora Attitude Bolivia S.R.L.'],
                ['contacto' => 'Contacto RedMarket', 'direccion' => 'Calle Comercio #100, Potosí', 'telefono' => '26220000', 'email' => 'attitude@proveedor.bo']
            ),
            'oppo' => Proveedor::firstOrCreate(
                ['nombre' => 'Importadora Oppo Bolivia S.R.L.'],
                ['contacto' => 'Contacto RedMarket', 'direccion' => 'Calle Comercio #100, Potosí', 'telefono' => '26220000', 'email' => 'oppo@proveedor.bo']
            ),
            'realme' => Proveedor::firstOrCreate(
                ['nombre' => 'Importadora Realme Bolivia S.R.L.'],
                ['contacto' => 'Contacto RedMarket', 'direccion' => 'Calle Comercio #100, Potosí', 'telefono' => '26220000', 'email' => 'realme@proveedor.bo']
            ),
            'vivo' => Proveedor::firstOrCreate(
                ['nombre' => 'Importadora Vivo Bolivia S.R.L.'],
                ['contacto' => 'Contacto RedMarket', 'direccion' => 'Calle Comercio #100, Potosí', 'telefono' => '26220000', 'email' => 'vivo@proveedor.bo']
            ),
            'fashion_shades' => Proveedor::firstOrCreate(
                ['nombre' => 'Importadora Fashion Shades Bolivia S.R.L.'],
                ['contacto' => 'Contacto RedMarket', 'direccion' => 'Calle Comercio #100, Potosí', 'telefono' => '26220000', 'email' => 'fashionshades@proveedor.bo']
            ),
            'fashion_fun' => Proveedor::firstOrCreate(
                ['nombre' => 'Importadora Fashion Fun Bolivia S.R.L.'],
                ['contacto' => 'Contacto RedMarket', 'direccion' => 'Calle Comercio #100, Potosí', 'telefono' => '26220000', 'email' => 'fashionfun@proveedor.bo']
            ),
            'chrysler' => Proveedor::firstOrCreate(
                ['nombre' => 'Importadora Chrysler Bolivia S.R.L.'],
                ['contacto' => 'Contacto RedMarket', 'direccion' => 'Calle Comercio #100, Potosí', 'telefono' => '26220000', 'email' => 'chrysler@proveedor.bo']
            ),
            'dodge' => Proveedor::firstOrCreate(
                ['nombre' => 'Importadora Dodge Bolivia S.R.L.'],
                ['contacto' => 'Contacto RedMarket', 'direccion' => 'Calle Comercio #100, Potosí', 'telefono' => '26220000', 'email' => 'dodge@proveedor.bo']
            ),
            'fashionista' => Proveedor::firstOrCreate(
                ['nombre' => 'Importadora Fashionista Bolivia S.R.L.'],
                ['contacto' => 'Contacto RedMarket', 'direccion' => 'Calle Comercio #100, Potosí', 'telefono' => '26220000', 'email' => 'fashionista@proveedor.bo']
            ),
            'heshe' => Proveedor::firstOrCreate(
                ['nombre' => 'Importadora Heshe Bolivia S.R.L.'],
                ['contacto' => 'Contacto RedMarket', 'direccion' => 'Calle Comercio #100, Potosí', 'telefono' => '26220000', 'email' => 'heshe@proveedor.bo']
            ),
            'prada' => Proveedor::firstOrCreate(
                ['nombre' => 'Importadora Prada Bolivia S.R.L.'],
                ['contacto' => 'Contacto RedMarket', 'direccion' => 'Calle Comercio #100, Potosí', 'telefono' => '26220000', 'email' => 'prada@proveedor.bo']
            ),
            'elegance_collection' => Proveedor::firstOrCreate(
                ['nombre' => 'Importadora Elegance Collection Bolivia S.R.L.'],
                ['contacto' => 'Contacto RedMarket', 'direccion' => 'Calle Comercio #100, Potosí', 'telefono' => '26220000', 'email' => 'elegancecollection@proveedor.bo']
            ),
            'comfort_trends' => Proveedor::firstOrCreate(
                ['nombre' => 'Importadora Comfort Trends Bolivia S.R.L.'],
                ['contacto' => 'Contacto RedMarket', 'direccion' => 'Calle Comercio #100, Potosí', 'telefono' => '26220000', 'email' => 'comforttrends@proveedor.bo']
            ),
            'fashion_diva' => Proveedor::firstOrCreate(
                ['nombre' => 'Importadora Fashion Diva Bolivia S.R.L.'],
                ['contacto' => 'Contacto RedMarket', 'direccion' => 'Calle Comercio #100, Potosí', 'telefono' => '26220000', 'email' => 'fashiondiva@proveedor.bo']
            ),
            'pampi' => Proveedor::firstOrCreate(
                ['nombre' => 'Importadora Pampi Bolivia S.R.L.'],
                ['contacto' => 'Contacto RedMarket', 'direccion' => 'Calle Comercio #100, Potosí', 'telefono' => '26220000', 'email' => 'pampi@proveedor.bo']
            ),
            'fashion_express' => Proveedor::firstOrCreate(
                ['nombre' => 'Importadora Fashion Express Bolivia S.R.L.'],
                ['contacto' => 'Contacto RedMarket', 'direccion' => 'Calle Comercio #100, Potosí', 'telefono' => '26220000', 'email' => 'fashionexpress@proveedor.bo']
            ),
            'iwc' => Proveedor::firstOrCreate(
                ['nombre' => 'Importadora Iwc Bolivia S.R.L.'],
                ['contacto' => 'Contacto RedMarket', 'direccion' => 'Calle Comercio #100, Potosí', 'telefono' => '26220000', 'email' => 'iwc@proveedor.bo']
            ),
            'fashion_gold' => Proveedor::firstOrCreate(
                ['nombre' => 'Importadora Fashion Gold Bolivia S.R.L.'],
                ['contacto' => 'Contacto RedMarket', 'direccion' => 'Calle Comercio #100, Potosí', 'telefono' => '26220000', 'email' => 'fashiongold@proveedor.bo']
            ),
            'fashion_co' => Proveedor::firstOrCreate(
                ['nombre' => 'Importadora Fashion Co Bolivia S.R.L.'],
                ['contacto' => 'Contacto RedMarket', 'direccion' => 'Calle Comercio #100, Potosí', 'telefono' => '26220000', 'email' => 'fashionco@proveedor.bo']
            ),
        ];
        return $proveedores;

    }

    protected function createCategorias(): array
    {
        $nombres = [
            'accesorios_de_cocina' => 'Accesorios de Cocina',
            'accesorios_deportivos' => 'Accesorios Deportivos',
            'accesorios_moviles' => 'Accesorios Móviles',
            'belleza' => 'Belleza',
            'blusas' => 'Blusas',
            'camisetas_hombre' => 'Camisetas Hombre',
            'carteras_mujer' => 'Carteras Mujer',
            'cuidado_de_la_piel' => 'Cuidado de la Piel',
            'decoracion_del_hogar' => 'Decoración del Hogar',
            'despensa' => 'Despensa',
            'fragancias' => 'Fragancias',
            'joyeria_mujer' => 'Joyería Mujer',
            'laptops' => 'Laptops',
            'lentes_de_sol' => 'Lentes de Sol',
            'motocicletas' => 'Motocicletas',
            'muebles' => 'Muebles',
            'relojes_hombre' => 'Relojes Hombre',
            'relojes_mujer' => 'Relojes Mujer',
            'smartphones' => 'Smartphones',
            'tablets' => 'Tablets',
            'vehiculos' => 'Vehículos',
            'vestidos_mujer' => 'Vestidos Mujer',
            'zapatos_hombre' => 'Zapatos Hombre',
            'zapatos_mujer' => 'Zapatos Mujer',
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

            // Belleza
            ['nombre' => 'Essence Mascara Lash Princess', 'categoria' => 'belleza', 'precio_compra' => 54.95, 'precio_venta' => 74.93, 'stock_actual' => 99, 'codigo_barras' => 'ESS001', 'pasillo' => '4', 'nivel' => 'A', 'unidad_medida' => 'un', 'proveedor' => 'essence', 'descripcion' => 'The Essence Mascara Lash Princess is a popular mascara known for its volumizing and lengthening effects. Achieve dramatic lashes with this long-lasting and cruelty-free formula.', 'en_descuento' => true, 'precio_oferta' => 67.08, ],
            ['nombre' => 'Eyeshadow Palette with Mirror', 'categoria' => 'belleza', 'precio_compra' => 109.95, 'precio_venta' => 149.93, 'stock_actual' => 34, 'codigo_barras' => 'EYE002', 'pasillo' => '2', 'nivel' => 'B', 'unidad_medida' => 'un', 'proveedor' => 'glamour_beauty', 'descripcion' => 'The Eyeshadow Palette with Mirror offers a versatile range of eyeshadow shades for creating stunning eye looks. With a built-in mirror, it\'s convenient for on-the-go makeup application.', 'en_descuento' => true, 'precio_oferta' => 122.66, ],
            ['nombre' => 'Powder Canister', 'categoria' => 'belleza', 'precio_compra' => 82.45, 'precio_venta' => 112.43, 'stock_actual' => 89, 'codigo_barras' => 'POW003', 'pasillo' => '1', 'nivel' => 'A', 'unidad_medida' => 'un', 'proveedor' => 'velvet_touch', 'descripcion' => 'The Powder Canister is a finely milled setting powder designed to set makeup and control shine. With a lightweight and translucent formula, it provides a smooth and matte finish.', 'en_descuento' => true, 'precio_oferta' => 101.37, ],
            ['nombre' => 'Red Lipstick', 'categoria' => 'belleza', 'precio_compra' => 71.45, 'precio_venta' => 97.43, 'stock_actual' => 91, 'codigo_barras' => 'RED004', 'pasillo' => '5', 'nivel' => 'A', 'unidad_medida' => 'un', 'proveedor' => 'chic_cosmetics', 'descripcion' => 'The Red Lipstick is a classic and bold choice for adding a pop of color to your lips. With a creamy and pigmented formula, it provides a vibrant and long-lasting finish.', 'en_descuento' => true, 'precio_oferta' => 85.58, ],
            ['nombre' => 'Red Nail Polish', 'categoria' => 'belleza', 'precio_compra' => 49.45, 'precio_venta' => 67.43, 'stock_actual' => 79, 'codigo_barras' => 'RED005', 'pasillo' => '6', 'nivel' => 'B', 'unidad_medida' => 'un', 'proveedor' => 'nail_couture', 'descripcion' => 'The Red Nail Polish offers a rich and glossy red hue for vibrant and polished nails. With a quick-drying formula, it provides a salon-quality finish at home.', 'en_descuento' => true, 'precio_oferta' => 59.72, ],

            // Fragancias
            ['nombre' => 'Calvin Klein CK One', 'categoria' => 'fragancias', 'precio_compra' => 274.95, 'precio_venta' => 374.93, 'stock_actual' => 29, 'codigo_barras' => 'CAL006', 'pasillo' => '7', 'nivel' => 'A', 'unidad_medida' => 'un', 'proveedor' => 'calvin_klein', 'descripcion' => 'CK One by Calvin Klein is a classic unisex fragrance, known for its fresh and clean scent. It\'s a versatile fragrance suitable for everyday wear.', ],
            ['nombre' => 'Chanel Coco Noir Eau De', 'categoria' => 'fragancias', 'precio_compra' => 714.95, 'precio_venta' => 974.93, 'stock_actual' => 58, 'codigo_barras' => 'CHA007', 'pasillo' => '4', 'nivel' => 'B', 'unidad_medida' => 'un', 'proveedor' => 'chanel', 'descripcion' => 'Coco Noir by Chanel is an elegant and mysterious fragrance, featuring notes of grapefruit, rose, and sandalwood. Perfect for evening occasions.', 'en_descuento' => true, 'precio_oferta' => 813.97, ],
            ['nombre' => 'Dior J\'adore', 'categoria' => 'fragancias', 'precio_compra' => 494.95, 'precio_venta' => 674.93, 'stock_actual' => 98, 'codigo_barras' => 'DIO008', 'pasillo' => '5', 'nivel' => 'C', 'unidad_medida' => 'un', 'proveedor' => 'dior', 'descripcion' => 'J\'adore by Dior is a luxurious and floral fragrance, known for its blend of ylang-ylang, rose, and jasmine. It embodies femininity and sophistication.', 'en_descuento' => true, 'precio_oferta' => 575.58, ],
            ['nombre' => 'Dolce Shine Eau de', 'categoria' => 'fragancias', 'precio_compra' => 384.95, 'precio_venta' => 524.93, 'stock_actual' => 4, 'codigo_barras' => 'DOL009', 'pasillo' => '5', 'nivel' => 'A', 'unidad_medida' => 'un', 'proveedor' => 'dolce_gabbana', 'descripcion' => 'Dolce Shine by Dolce & Gabbana is a vibrant and fruity fragrance, featuring notes of mango, jasmine, and blonde woods. It\'s a joyful and youthful scent.', ],
            ['nombre' => 'Gucci Bloom Eau de', 'categoria' => 'fragancias', 'precio_compra' => 439.95, 'precio_venta' => 599.93, 'stock_actual' => 91, 'codigo_barras' => 'GUC010', 'pasillo' => '7', 'nivel' => 'A', 'unidad_medida' => 'un', 'proveedor' => 'gucci', 'descripcion' => 'Gucci Bloom by Gucci is a floral and captivating fragrance, with notes of tuberose, jasmine, and Rangoon creeper. It\'s a modern and romantic scent.', 'en_descuento' => true, 'precio_oferta' => 513.6, ],

            // Muebles
            ['nombre' => 'Annibale Colombo Bed', 'categoria' => 'muebles', 'precio_compra' => 10449.95, 'precio_venta' => 14249.93, 'stock_actual' => 88, 'codigo_barras' => 'ANN011', 'pasillo' => '6', 'nivel' => 'A', 'unidad_medida' => 'un', 'proveedor' => 'annibale_colombo', 'descripcion' => 'The Annibale Colombo Bed is a luxurious and elegant bed frame, crafted with high-quality materials for a comfortable and stylish bedroom.', 'en_descuento' => true, 'precio_oferta' => 13028.71, ],
            ['nombre' => 'Annibale Colombo Sofa', 'categoria' => 'muebles', 'precio_compra' => 13749.95, 'precio_venta' => 18749.93, 'stock_actual' => 60, 'codigo_barras' => 'ANN012', 'pasillo' => '9', 'nivel' => 'A', 'unidad_medida' => 'un', 'proveedor' => 'annibale_colombo', 'descripcion' => 'The Annibale Colombo Sofa is a sophisticated and comfortable seating option, featuring exquisite design and premium upholstery for your living room.', 'en_descuento' => true, 'precio_oferta' => 16049.94, ],
            ['nombre' => 'Bedside Table African Cherry', 'categoria' => 'muebles', 'precio_compra' => 1649.95, 'precio_venta' => 2249.93, 'stock_actual' => 64, 'codigo_barras' => 'BED013', 'pasillo' => '6', 'nivel' => 'C', 'unidad_medida' => 'un', 'proveedor' => 'furniture_co', 'descripcion' => 'The Bedside Table in African Cherry is a stylish and functional addition to your bedroom, providing convenient storage space and a touch of elegance.', 'en_descuento' => true, 'precio_oferta' => 1820.42, ],
            ['nombre' => 'Knoll Saarinen Executive Conference Chair', 'categoria' => 'muebles', 'precio_compra' => 2749.95, 'precio_venta' => 3749.93, 'stock_actual' => 26, 'codigo_barras' => 'KNO014', 'pasillo' => '2', 'nivel' => 'A', 'unidad_medida' => 'un', 'proveedor' => 'knoll', 'descripcion' => 'The Knoll Saarinen Executive Conference Chair is a modern and ergonomic chair, perfect for your office or conference room with its timeless design.', ],
            ['nombre' => 'Wooden Bathroom Sink With Mirror', 'categoria' => 'muebles', 'precio_compra' => 4399.95, 'precio_venta' => 5999.93, 'stock_actual' => 7, 'codigo_barras' => 'WOO015', 'pasillo' => '4', 'nivel' => 'C', 'unidad_medida' => 'un', 'proveedor' => 'bath_trends', 'descripcion' => 'The Wooden Bathroom Sink with Mirror is a unique and stylish addition to your bathroom, featuring a wooden sink countertop and a matching mirror.', 'en_descuento' => true, 'precio_oferta' => 5471.94, ],

            // Despensa
            ['nombre' => 'Apple', 'categoria' => 'despensa', 'precio_compra' => 10.95, 'precio_venta' => 14.93, 'stock_actual' => 8, 'codigo_barras' => 'APP016', 'pasillo' => '4', 'nivel' => 'A', 'unidad_medida' => 'kg', 'proveedor' => 'distribuidora_mayorista', 'descripcion' => 'Fresh and crisp apples, perfect for snacking or incorporating into various recipes.', 'en_descuento' => true, 'precio_oferta' => 13.05, 'es_perecedero' => true, 'fecha_vencimiento' => '2026-08-15', ],
            ['nombre' => 'Beef Steak', 'categoria' => 'despensa', 'precio_compra' => 71.45, 'precio_venta' => 97.43, 'stock_actual' => 86, 'codigo_barras' => 'BEE017', 'pasillo' => '8', 'nivel' => 'C', 'unidad_medida' => 'un', 'proveedor' => 'distribuidora_mayorista', 'descripcion' => 'High-quality beef steak, great for grilling or cooking to your preferred level of doneness.', 'en_descuento' => true, 'precio_oferta' => 88.07, 'es_perecedero' => true, 'fecha_vencimiento' => '2026-08-15', ],
            ['nombre' => 'Cat Food', 'categoria' => 'despensa', 'precio_compra' => 49.45, 'precio_venta' => 67.43, 'stock_actual' => 46, 'codigo_barras' => 'CAT018', 'pasillo' => '9', 'nivel' => 'A', 'unidad_medida' => 'un', 'proveedor' => 'distribuidora_mayorista', 'descripcion' => 'Nutritious cat food formulated to meet the dietary needs of your feline friend.', 'en_descuento' => true, 'precio_oferta' => 60.97, 'es_perecedero' => true, 'fecha_vencimiento' => '2026-08-15', ],
            ['nombre' => 'Chicken Meat', 'categoria' => 'despensa', 'precio_compra' => 54.95, 'precio_venta' => 74.93, 'stock_actual' => 97, 'codigo_barras' => 'CHI019', 'pasillo' => '7', 'nivel' => 'B', 'unidad_medida' => 'kg', 'proveedor' => 'distribuidora_mayorista', 'descripcion' => 'Fresh and tender chicken meat, suitable for various culinary preparations.', 'en_descuento' => true, 'precio_oferta' => 64.66, 'es_perecedero' => true, 'fecha_vencimiento' => '2026-08-15', ],
            ['nombre' => 'Cooking Oil', 'categoria' => 'despensa', 'precio_compra' => 27.45, 'precio_venta' => 37.43, 'stock_actual' => 10, 'codigo_barras' => 'COO020', 'pasillo' => '9', 'nivel' => 'B', 'unidad_medida' => 'lt', 'proveedor' => 'distribuidora_mayorista', 'descripcion' => 'Versatile cooking oil suitable for frying, sautéing, and various culinary applications.', 'en_descuento' => true, 'precio_oferta' => 33.94, 'es_perecedero' => true, 'fecha_vencimiento' => '2026-08-15', ],
            ['nombre' => 'Cucumber', 'categoria' => 'despensa', 'precio_compra' => 8.2, 'precio_venta' => 11.18, 'stock_actual' => 84, 'codigo_barras' => 'CUC021', 'pasillo' => '9', 'nivel' => 'A', 'unidad_medida' => 'un', 'proveedor' => 'distribuidora_mayorista', 'descripcion' => 'Crisp and hydrating cucumbers, ideal for salads, snacks, or as a refreshing side.', 'es_perecedero' => true, 'fecha_vencimiento' => '2026-08-15', ],
            ['nombre' => 'Dog Food', 'categoria' => 'despensa', 'precio_compra' => 60.45, 'precio_venta' => 82.43, 'stock_actual' => 71, 'codigo_barras' => 'DOG022', 'pasillo' => '2', 'nivel' => 'B', 'unidad_medida' => 'un', 'proveedor' => 'distribuidora_mayorista', 'descripcion' => 'Specially formulated dog food designed to provide essential nutrients for your canine companion.', 'en_descuento' => true, 'precio_oferta' => 73.96, 'es_perecedero' => true, 'fecha_vencimiento' => '2026-08-15', ],
            ['nombre' => 'Eggs', 'categoria' => 'despensa', 'precio_compra' => 16.45, 'precio_venta' => 22.43, 'stock_actual' => 9, 'codigo_barras' => 'EGG023', 'pasillo' => '1', 'nivel' => 'C', 'unidad_medida' => 'un', 'proveedor' => 'distribuidora_mayorista', 'descripcion' => 'Fresh eggs, a versatile ingredient for baking, cooking, or breakfast.', 'en_descuento' => true, 'precio_oferta' => 19.95, 'es_perecedero' => true, 'fecha_vencimiento' => '2026-08-15', ],
            ['nombre' => 'Fish Steak', 'categoria' => 'despensa', 'precio_compra' => 82.45, 'precio_venta' => 112.43, 'stock_actual' => 74, 'codigo_barras' => 'FIS024', 'pasillo' => '9', 'nivel' => 'B', 'unidad_medida' => 'kg', 'proveedor' => 'distribuidora_mayorista', 'descripcion' => 'Quality fish steak, suitable for grilling, baking, or pan-searing.', 'es_perecedero' => true, 'fecha_vencimiento' => '2026-08-15', ],
            ['nombre' => 'Green Bell Pepper', 'categoria' => 'despensa', 'precio_compra' => 7.1, 'precio_venta' => 9.68, 'stock_actual' => 33, 'codigo_barras' => 'GRE025', 'pasillo' => '5', 'nivel' => 'B', 'unidad_medida' => 'un', 'proveedor' => 'distribuidora_mayorista', 'descripcion' => 'Fresh and vibrant green bell pepper, perfect for adding color and flavor to your dishes.', 'es_perecedero' => true, 'fecha_vencimiento' => '2026-08-15', ],
            ['nombre' => 'Green Chili Pepper', 'categoria' => 'despensa', 'precio_compra' => 5.45, 'precio_venta' => 7.43, 'stock_actual' => 3, 'codigo_barras' => 'GRE026', 'pasillo' => '2', 'nivel' => 'A', 'unidad_medida' => 'un', 'proveedor' => 'distribuidora_mayorista', 'descripcion' => 'Spicy green chili pepper, ideal for adding heat to your favorite recipes.', 'es_perecedero' => true, 'fecha_vencimiento' => '2026-08-15', ],
            ['nombre' => 'Honey Jar', 'categoria' => 'despensa', 'precio_compra' => 38.45, 'precio_venta' => 52.43, 'stock_actual' => 34, 'codigo_barras' => 'HON027', 'pasillo' => '2', 'nivel' => 'B', 'unidad_medida' => 'un', 'proveedor' => 'distribuidora_mayorista', 'descripcion' => 'Pure and natural honey in a convenient jar, perfect for sweetening beverages or drizzling over food.', 'en_descuento' => true, 'precio_oferta' => 44.88, 'es_perecedero' => true, 'fecha_vencimiento' => '2026-08-15', ],
            ['nombre' => 'Ice Cream', 'categoria' => 'despensa', 'precio_compra' => 30.2, 'precio_venta' => 41.18, 'stock_actual' => 27, 'codigo_barras' => 'ICE028', 'pasillo' => '8', 'nivel' => 'B', 'unidad_medida' => 'un', 'proveedor' => 'distribuidora_mayorista', 'descripcion' => 'Creamy and delicious ice cream, available in various flavors for a delightful treat.', 'en_descuento' => true, 'precio_oferta' => 37.6, 'es_perecedero' => true, 'fecha_vencimiento' => '2026-08-15', ],
            ['nombre' => 'Juice', 'categoria' => 'despensa', 'precio_compra' => 21.95, 'precio_venta' => 29.93, 'stock_actual' => 50, 'codigo_barras' => 'JUI029', 'pasillo' => '1', 'nivel' => 'A', 'unidad_medida' => 'lt', 'proveedor' => 'distribuidora_mayorista', 'descripcion' => 'Refreshing fruit juice, packed with vitamins and great for staying hydrated.', 'en_descuento' => true, 'precio_oferta' => 26.32, 'es_perecedero' => true, 'fecha_vencimiento' => '2026-08-15', ],
            ['nombre' => 'Kiwi', 'categoria' => 'despensa', 'precio_compra' => 13.7, 'precio_venta' => 18.68, 'stock_actual' => 99, 'codigo_barras' => 'KIW030', 'pasillo' => '3', 'nivel' => 'A', 'unidad_medida' => 'un', 'proveedor' => 'distribuidora_mayorista', 'descripcion' => 'Nutrient-rich kiwi, perfect for snacking or adding a tropical twist to your dishes.', 'en_descuento' => true, 'precio_oferta' => 15.84, 'es_perecedero' => true, 'fecha_vencimiento' => '2026-08-15', ],
            ['nombre' => 'Lemon', 'categoria' => 'despensa', 'precio_compra' => 4.35, 'precio_venta' => 5.93, 'stock_actual' => 31, 'codigo_barras' => 'LEM031', 'pasillo' => '3', 'nivel' => 'B', 'unidad_medida' => 'un', 'proveedor' => 'distribuidora_mayorista', 'descripcion' => 'Zesty and tangy lemons, versatile for cooking, baking, or making refreshing beverages.', 'en_descuento' => true, 'precio_oferta' => 5.35, 'es_perecedero' => true, 'fecha_vencimiento' => '2026-08-15', ],
            ['nombre' => 'Milk', 'categoria' => 'despensa', 'precio_compra' => 19.2, 'precio_venta' => 26.18, 'stock_actual' => 27, 'codigo_barras' => 'MIL032', 'pasillo' => '3', 'nivel' => 'B', 'unidad_medida' => 'lt', 'proveedor' => 'distribuidora_mayorista', 'descripcion' => 'Fresh and nutritious milk, a staple for various recipes and daily consumption.', 'en_descuento' => true, 'precio_oferta' => 22.58, 'es_perecedero' => true, 'fecha_vencimiento' => '2026-08-15', ],
            ['nombre' => 'Mulberry', 'categoria' => 'despensa', 'precio_compra' => 27.45, 'precio_venta' => 37.43, 'stock_actual' => 99, 'codigo_barras' => 'MUL033', 'pasillo' => '3', 'nivel' => 'C', 'unidad_medida' => 'un', 'proveedor' => 'distribuidora_mayorista', 'descripcion' => 'Sweet and juicy mulberries, perfect for snacking or adding to desserts and cereals.', 'en_descuento' => true, 'precio_oferta' => 32.61, 'es_perecedero' => true, 'fecha_vencimiento' => '2026-08-15', ],
            ['nombre' => 'Nescafe Coffee', 'categoria' => 'despensa', 'precio_compra' => 43.95, 'precio_venta' => 59.93, 'stock_actual' => 57, 'codigo_barras' => 'NES034', 'pasillo' => '9', 'nivel' => 'A', 'unidad_medida' => 'un', 'proveedor' => 'distribuidora_mayorista', 'descripcion' => 'Quality coffee from Nescafe, available in various blends for a rich and satisfying cup.', 'es_perecedero' => true, 'fecha_vencimiento' => '2026-08-15', ],
            ['nombre' => 'Potatoes', 'categoria' => 'despensa', 'precio_compra' => 12.6, 'precio_venta' => 17.18, 'stock_actual' => 13, 'codigo_barras' => 'POT035', 'pasillo' => '4', 'nivel' => 'B', 'unidad_medida' => 'kg', 'proveedor' => 'distribuidora_mayorista', 'descripcion' => 'Versatile and starchy potatoes, great for roasting, mashing, or as a side dish.', 'es_perecedero' => true, 'fecha_vencimiento' => '2026-08-15', ],
            ['nombre' => 'Protein Powder', 'categoria' => 'despensa', 'precio_compra' => 109.95, 'precio_venta' => 149.93, 'stock_actual' => 80, 'codigo_barras' => 'PRO036', 'pasillo' => '3', 'nivel' => 'B', 'unidad_medida' => 'un', 'proveedor' => 'distribuidora_mayorista', 'descripcion' => 'Nutrient-packed protein powder, ideal for supplementing your diet with essential proteins.', 'es_perecedero' => true, 'fecha_vencimiento' => '2026-08-15', ],
            ['nombre' => 'Red Onions', 'categoria' => 'despensa', 'precio_compra' => 10.95, 'precio_venta' => 14.93, 'stock_actual' => 82, 'codigo_barras' => 'RED037', 'pasillo' => '5', 'nivel' => 'B', 'unidad_medida' => 'kg', 'proveedor' => 'distribuidora_mayorista', 'descripcion' => 'Flavorful and aromatic red onions, perfect for adding depth to your savory dishes.', 'en_descuento' => true, 'precio_oferta' => 13.45, 'es_perecedero' => true, 'fecha_vencimiento' => '2026-08-15', ],
            ['nombre' => 'Rice', 'categoria' => 'despensa', 'precio_compra' => 32.95, 'precio_venta' => 44.93, 'stock_actual' => 59, 'codigo_barras' => 'RIC038', 'pasillo' => '8', 'nivel' => 'B', 'unidad_medida' => 'kg', 'proveedor' => 'distribuidora_mayorista', 'descripcion' => 'High-quality rice, a staple for various cuisines and a versatile base for many dishes.', 'en_descuento' => true, 'precio_oferta' => 40.76, 'es_perecedero' => true, 'fecha_vencimiento' => '2026-08-15', ],
            ['nombre' => 'Soft Drinks', 'categoria' => 'despensa', 'precio_compra' => 10.95, 'precio_venta' => 14.93, 'stock_actual' => 53, 'codigo_barras' => 'SOF039', 'pasillo' => '7', 'nivel' => 'A', 'unidad_medida' => 'un', 'proveedor' => 'distribuidora_mayorista', 'descripcion' => 'Assorted soft drinks in various flavors, perfect for refreshing beverages.', 'en_descuento' => true, 'precio_oferta' => 12.32, 'es_perecedero' => true, 'fecha_vencimiento' => '2026-08-15', ],
            ['nombre' => 'Strawberry', 'categoria' => 'despensa', 'precio_compra' => 21.95, 'precio_venta' => 29.93, 'stock_actual' => 46, 'codigo_barras' => 'STR040', 'pasillo' => '3', 'nivel' => 'B', 'unidad_medida' => 'un', 'proveedor' => 'distribuidora_mayorista', 'descripcion' => 'Sweet and succulent strawberries, great for snacking, desserts, or blending into smoothies.', 'es_perecedero' => true, 'fecha_vencimiento' => '2026-08-15', ],
            ['nombre' => 'Tissue Paper Box', 'categoria' => 'despensa', 'precio_compra' => 13.7, 'precio_venta' => 18.68, 'stock_actual' => 86, 'codigo_barras' => 'TIS041', 'pasillo' => '9', 'nivel' => 'B', 'unidad_medida' => 'un', 'proveedor' => 'distribuidora_mayorista', 'descripcion' => 'Convenient tissue paper box for everyday use, providing soft and absorbent tissues.', 'en_descuento' => true, 'precio_oferta' => 16.2, 'es_perecedero' => true, 'fecha_vencimiento' => '2026-08-15', ],
            ['nombre' => 'Water', 'categoria' => 'despensa', 'precio_compra' => 5.45, 'precio_venta' => 7.43, 'stock_actual' => 53, 'codigo_barras' => 'WAT042', 'pasillo' => '6', 'nivel' => 'B', 'unidad_medida' => 'lt', 'proveedor' => 'distribuidora_mayorista', 'descripcion' => 'Pure and refreshing bottled water, essential for staying hydrated throughout the day.', 'en_descuento' => true, 'precio_oferta' => 6.32, 'es_perecedero' => true, 'fecha_vencimiento' => '2026-08-15', ],

            // Decoración del Hogar
            ['nombre' => 'Decoration Swing', 'categoria' => 'decoracion_del_hogar', 'precio_compra' => 329.95, 'precio_venta' => 449.93, 'stock_actual' => 47, 'codigo_barras' => 'DEC043', 'pasillo' => '6', 'nivel' => 'B', 'unidad_medida' => 'un', 'proveedor' => 'distribuidora_mayorista', 'descripcion' => 'The Decoration Swing is a charming addition to your home decor. Crafted with intricate details, it adds a touch of elegance and whimsy to any room.', 'en_descuento' => true, 'precio_oferta' => 403.09, ],
            ['nombre' => 'Family Tree Photo Frame', 'categoria' => 'decoracion_del_hogar', 'precio_compra' => 164.95, 'precio_venta' => 224.93, 'stock_actual' => 77, 'codigo_barras' => 'FAM044', 'pasillo' => '9', 'nivel' => 'A', 'unidad_medida' => 'un', 'proveedor' => 'distribuidora_mayorista', 'descripcion' => 'The Family Tree Photo Frame is a sentimental and stylish way to display your cherished family memories. With multiple photo slots, it tells the story of your loved ones.', 'en_descuento' => true, 'precio_oferta' => 191.48, ],
            ['nombre' => 'House Showpiece Plant', 'categoria' => 'decoracion_del_hogar', 'precio_compra' => 219.95, 'precio_venta' => 299.93, 'stock_actual' => 28, 'codigo_barras' => 'HOU045', 'pasillo' => '4', 'nivel' => 'A', 'unidad_medida' => 'un', 'proveedor' => 'distribuidora_mayorista', 'descripcion' => 'The House Showpiece Plant is an artificial plant that brings a touch of nature to your home without the need for maintenance. It adds greenery and style to any space.', ],
            ['nombre' => 'Plant Pot', 'categoria' => 'decoracion_del_hogar', 'precio_compra' => 82.45, 'precio_venta' => 112.43, 'stock_actual' => 59, 'codigo_barras' => 'PLA046', 'pasillo' => '4', 'nivel' => 'A', 'unidad_medida' => 'un', 'proveedor' => 'distribuidora_mayorista', 'descripcion' => 'The Plant Pot is a stylish container for your favorite plants. With a sleek design, it complements your indoor or outdoor garden, adding a modern touch to your plant display.', ],
            ['nombre' => 'Table Lamp', 'categoria' => 'decoracion_del_hogar', 'precio_compra' => 274.95, 'precio_venta' => 374.93, 'stock_actual' => 9, 'codigo_barras' => 'TAB047', 'pasillo' => '6', 'nivel' => 'C', 'unidad_medida' => 'un', 'proveedor' => 'distribuidora_mayorista', 'descripcion' => 'The Table Lamp is a functional and decorative lighting solution for your living space. With a modern design, it provides both ambient and task lighting, enhancing the atmosphere.', ],

            // Accesorios de Cocina
            ['nombre' => 'Bamboo Spatula', 'categoria' => 'accesorios_de_cocina', 'precio_compra' => 43.95, 'precio_venta' => 59.93, 'stock_actual' => 37, 'codigo_barras' => 'BAM048', 'pasillo' => '9', 'nivel' => 'B', 'unidad_medida' => 'un', 'proveedor' => 'distribuidora_mayorista', 'descripcion' => 'The Bamboo Spatula is a versatile kitchen tool made from eco-friendly bamboo. Ideal for flipping, stirring, and serving various dishes.', ],
            ['nombre' => 'Black Aluminium Cup', 'categoria' => 'accesorios_de_cocina', 'precio_compra' => 32.95, 'precio_venta' => 44.93, 'stock_actual' => 75, 'codigo_barras' => 'BLA049', 'pasillo' => '3', 'nivel' => 'B', 'unidad_medida' => 'un', 'proveedor' => 'distribuidora_mayorista', 'descripcion' => 'The Black Aluminium Cup is a stylish and durable cup suitable for both hot and cold beverages. Its sleek black design adds a modern touch to your drinkware collection.', 'en_descuento' => true, 'precio_oferta' => 37.9, ],
            ['nombre' => 'Black Whisk', 'categoria' => 'accesorios_de_cocina', 'precio_compra' => 54.95, 'precio_venta' => 74.93, 'stock_actual' => 73, 'codigo_barras' => 'BLA050', 'pasillo' => '7', 'nivel' => 'C', 'unidad_medida' => 'un', 'proveedor' => 'distribuidora_mayorista', 'descripcion' => 'The Black Whisk is a kitchen essential for whisking and beating ingredients. Its ergonomic handle and sleek design make it a practical and stylish tool.', 'en_descuento' => true, 'precio_oferta' => 67.26, ],
            ['nombre' => 'Boxed Blender', 'categoria' => 'accesorios_de_cocina', 'precio_compra' => 219.95, 'precio_venta' => 299.93, 'stock_actual' => 9, 'codigo_barras' => 'BOX051', 'pasillo' => '9', 'nivel' => 'B', 'unidad_medida' => 'un', 'proveedor' => 'distribuidora_mayorista', 'descripcion' => 'The Boxed Blender is a powerful and compact blender perfect for smoothies, shakes, and more. Its convenient design and multiple functions make it a versatile kitchen appliance.', ],
            ['nombre' => 'Carbon Steel Wok', 'categoria' => 'accesorios_de_cocina', 'precio_compra' => 164.95, 'precio_venta' => 224.93, 'stock_actual' => 40, 'codigo_barras' => 'CAR052', 'pasillo' => '8', 'nivel' => 'B', 'unidad_medida' => 'un', 'proveedor' => 'distribuidora_mayorista', 'descripcion' => 'The Carbon Steel Wok is a versatile cooking pan suitable for stir-frying, sautéing, and deep frying. Its sturdy construction ensures even heat distribution for delicious meals.', ],
            ['nombre' => 'Chopping Board', 'categoria' => 'accesorios_de_cocina', 'precio_compra' => 71.45, 'precio_venta' => 97.43, 'stock_actual' => 14, 'codigo_barras' => 'CHO053', 'pasillo' => '6', 'nivel' => 'B', 'unidad_medida' => 'un', 'proveedor' => 'distribuidora_mayorista', 'descripcion' => 'The Chopping Board is an essential kitchen accessory for food preparation. Made from durable material, it provides a safe and hygienic surface for cutting and chopping.', 'en_descuento' => true, 'precio_oferta' => 89.61, ],
            ['nombre' => 'Citrus Squeezer Yellow', 'categoria' => 'accesorios_de_cocina', 'precio_compra' => 49.45, 'precio_venta' => 67.43, 'stock_actual' => 22, 'codigo_barras' => 'CIT054', 'pasillo' => '7', 'nivel' => 'B', 'unidad_medida' => 'un', 'proveedor' => 'distribuidora_mayorista', 'descripcion' => 'The Citrus Squeezer in Yellow is a handy tool for extracting juice from citrus fruits. Its vibrant color adds a cheerful touch to your kitchen gadgets.', 'en_descuento' => true, 'precio_oferta' => 59.27, ],
            ['nombre' => 'Egg Slicer', 'categoria' => 'accesorios_de_cocina', 'precio_compra' => 38.45, 'precio_venta' => 52.43, 'stock_actual' => 40, 'codigo_barras' => 'EGG055', 'pasillo' => '3', 'nivel' => 'A', 'unidad_medida' => 'un', 'proveedor' => 'distribuidora_mayorista', 'descripcion' => 'The Egg Slicer is a convenient tool for slicing boiled eggs evenly. It\'s perfect for salads, sandwiches, and other dishes where sliced eggs are desired.', 'en_descuento' => true, 'precio_oferta' => 44.69, ],
            ['nombre' => 'Electric Stove', 'categoria' => 'accesorios_de_cocina', 'precio_compra' => 274.95, 'precio_venta' => 374.93, 'stock_actual' => 21, 'codigo_barras' => 'ELE056', 'pasillo' => '1', 'nivel' => 'B', 'unidad_medida' => 'un', 'proveedor' => 'distribuidora_mayorista', 'descripcion' => 'The Electric Stove provides a portable and efficient cooking solution. Ideal for small kitchens or as an additional cooking surface for various culinary needs.', 'en_descuento' => true, 'precio_oferta' => 322.29, ],
            ['nombre' => 'Fine Mesh Strainer', 'categoria' => 'accesorios_de_cocina', 'precio_compra' => 54.95, 'precio_venta' => 74.93, 'stock_actual' => 85, 'codigo_barras' => 'FIN057', 'pasillo' => '5', 'nivel' => 'B', 'unidad_medida' => 'un', 'proveedor' => 'distribuidora_mayorista', 'descripcion' => 'The Fine Mesh Strainer is a versatile tool for straining liquids and sifting dry ingredients. Its fine mesh ensures efficient filtering for smooth cooking and baking.', ],
            ['nombre' => 'Fork', 'categoria' => 'accesorios_de_cocina', 'precio_compra' => 21.95, 'precio_venta' => 29.93, 'stock_actual' => 7, 'codigo_barras' => 'FOR058', 'pasillo' => '4', 'nivel' => 'B', 'unidad_medida' => 'un', 'proveedor' => 'distribuidora_mayorista', 'descripcion' => 'The Fork is a classic utensil for various dining and serving purposes. Its durable and ergonomic design makes it a reliable choice for everyday use.', 'en_descuento' => true, 'precio_oferta' => 27.51, ],
            ['nombre' => 'Glass', 'categoria' => 'accesorios_de_cocina', 'precio_compra' => 27.45, 'precio_venta' => 37.43, 'stock_actual' => 46, 'codigo_barras' => 'GLA059', 'pasillo' => '5', 'nivel' => 'B', 'unidad_medida' => 'un', 'proveedor' => 'distribuidora_mayorista', 'descripcion' => 'The Glass is a versatile and elegant drinking vessel suitable for a variety of beverages. Its clear design allows you to enjoy the colors and textures of your drinks.', ],
            ['nombre' => 'Grater Black', 'categoria' => 'accesorios_de_cocina', 'precio_compra' => 60.45, 'precio_venta' => 82.43, 'stock_actual' => 84, 'codigo_barras' => 'GRA060', 'pasillo' => '4', 'nivel' => 'C', 'unidad_medida' => 'un', 'proveedor' => 'distribuidora_mayorista', 'descripcion' => 'The Grater in Black is a handy kitchen tool for grating cheese, vegetables, and more. Its sleek design and sharp blades make food preparation efficient and easy.', ],
            ['nombre' => 'Hand Blender', 'categoria' => 'accesorios_de_cocina', 'precio_compra' => 192.45, 'precio_venta' => 262.43, 'stock_actual' => 84, 'codigo_barras' => 'HAN061', 'pasillo' => '1', 'nivel' => 'B', 'unidad_medida' => 'un', 'proveedor' => 'distribuidora_mayorista', 'descripcion' => 'The Hand Blender is a versatile kitchen appliance for blending, pureeing, and mixing. Its compact design and powerful motor make it a convenient tool for various recipes.', 'en_descuento' => true, 'precio_oferta' => 217.76, ],
            ['nombre' => 'Ice Cube Tray', 'categoria' => 'accesorios_de_cocina', 'precio_compra' => 32.95, 'precio_venta' => 44.93, 'stock_actual' => 13, 'codigo_barras' => 'ICE062', 'pasillo' => '6', 'nivel' => 'A', 'unidad_medida' => 'un', 'proveedor' => 'distribuidora_mayorista', 'descripcion' => 'The Ice Cube Tray is a practical accessory for making ice cubes in various shapes. Perfect for keeping your drinks cool and adding a fun element to your beverages.', ],
            ['nombre' => 'Kitchen Sieve', 'categoria' => 'accesorios_de_cocina', 'precio_compra' => 43.95, 'precio_venta' => 59.93, 'stock_actual' => 68, 'codigo_barras' => 'KIT063', 'pasillo' => '5', 'nivel' => 'A', 'unidad_medida' => 'un', 'proveedor' => 'distribuidora_mayorista', 'descripcion' => 'The Kitchen Sieve is a versatile tool for sifting and straining dry and wet ingredients. Its fine mesh design ensures smooth results in your cooking and baking.', 'en_descuento' => true, 'precio_oferta' => 48.6, ],
            ['nombre' => 'Knife', 'categoria' => 'accesorios_de_cocina', 'precio_compra' => 82.45, 'precio_venta' => 112.43, 'stock_actual' => 7, 'codigo_barras' => 'KNI064', 'pasillo' => '4', 'nivel' => 'B', 'unidad_medida' => 'un', 'proveedor' => 'distribuidora_mayorista', 'descripcion' => 'The Knife is an essential kitchen tool for chopping, slicing, and dicing. Its sharp blade and ergonomic handle make it a reliable choice for food preparation.', 'en_descuento' => true, 'precio_oferta' => 91.23, ],
            ['nombre' => 'Lunch Box', 'categoria' => 'accesorios_de_cocina', 'precio_compra' => 71.45, 'precio_venta' => 97.43, 'stock_actual' => 94, 'codigo_barras' => 'LUN065', 'pasillo' => '1', 'nivel' => 'A', 'unidad_medida' => 'un', 'proveedor' => 'distribuidora_mayorista', 'descripcion' => 'The Lunch Box is a convenient and portable container for packing and carrying your meals. With compartments for different foods, it\'s perfect for on-the-go dining.', 'en_descuento' => true, 'precio_oferta' => 87.36, ],
            ['nombre' => 'Microwave Oven', 'categoria' => 'accesorios_de_cocina', 'precio_compra' => 494.95, 'precio_venta' => 674.93, 'stock_actual' => 59, 'codigo_barras' => 'MIC066', 'pasillo' => '1', 'nivel' => 'B', 'unidad_medida' => 'un', 'proveedor' => 'distribuidora_mayorista', 'descripcion' => 'The Microwave Oven is a versatile kitchen appliance for quick and efficient cooking, reheating, and defrosting. Its compact size makes it suitable for various kitchen setups.', 'en_descuento' => true, 'precio_oferta' => 593.06, ],
            ['nombre' => 'Mug Tree Stand', 'categoria' => 'accesorios_de_cocina', 'precio_compra' => 87.95, 'precio_venta' => 119.93, 'stock_actual' => 88, 'codigo_barras' => 'MUG067', 'pasillo' => '4', 'nivel' => 'C', 'unidad_medida' => 'un', 'proveedor' => 'distribuidora_mayorista', 'descripcion' => 'The Mug Tree Stand is a stylish and space-saving solution for organizing your mugs. Keep your favorite mugs easily accessible and neatly displayed in your kitchen.', 'en_descuento' => true, 'precio_oferta' => 108.84, ],
            ['nombre' => 'Pan', 'categoria' => 'accesorios_de_cocina', 'precio_compra' => 137.45, 'precio_venta' => 187.43, 'stock_actual' => 90, 'codigo_barras' => 'PAN068', 'pasillo' => '7', 'nivel' => 'A', 'unidad_medida' => 'un', 'proveedor' => 'distribuidora_mayorista', 'descripcion' => 'The Pan is a versatile and essential cookware item for frying, sautéing, and cooking various dishes. Its non-stick coating ensures easy food release and cleanup.', ],
            ['nombre' => 'Plate', 'categoria' => 'accesorios_de_cocina', 'precio_compra' => 21.95, 'precio_venta' => 29.93, 'stock_actual' => 66, 'codigo_barras' => 'PLA069', 'pasillo' => '8', 'nivel' => 'A', 'unidad_medida' => 'un', 'proveedor' => 'distribuidora_mayorista', 'descripcion' => 'The Plate is a classic and essential dishware item for serving meals. Its durable and stylish design makes it suitable for everyday use or special occasions.', ],
            ['nombre' => 'Red Tongs', 'categoria' => 'accesorios_de_cocina', 'precio_compra' => 38.45, 'precio_venta' => 52.43, 'stock_actual' => 82, 'codigo_barras' => 'RED070', 'pasillo' => '2', 'nivel' => 'C', 'unidad_medida' => 'un', 'proveedor' => 'distribuidora_mayorista', 'descripcion' => 'The Red Tongs are versatile kitchen tongs suitable for various cooking and serving tasks. Their vibrant color adds a pop of excitement to your kitchen utensils.', 'en_descuento' => true, 'precio_oferta' => 44.82, ],
            ['nombre' => 'Silver Pot With Glass Cap', 'categoria' => 'accesorios_de_cocina', 'precio_compra' => 219.95, 'precio_venta' => 299.93, 'stock_actual' => 40, 'codigo_barras' => 'SIL071', 'pasillo' => '4', 'nivel' => 'C', 'unidad_medida' => 'un', 'proveedor' => 'distribuidora_mayorista', 'descripcion' => 'The Silver Pot with Glass Cap is a stylish and functional cookware item for boiling, simmering, and preparing delicious meals. Its glass cap allows you to monitor cooking progress.', ],
            ['nombre' => 'Slotted Turner', 'categoria' => 'accesorios_de_cocina', 'precio_compra' => 49.45, 'precio_venta' => 67.43, 'stock_actual' => 88, 'codigo_barras' => 'SLO072', 'pasillo' => '2', 'nivel' => 'A', 'unidad_medida' => 'un', 'proveedor' => 'distribuidora_mayorista', 'descripcion' => 'The Slotted Turner is a kitchen utensil designed for flipping and turning food items. Its slotted design allows excess liquid to drain, making it ideal for frying and sautéing.', 'en_descuento' => true, 'precio_oferta' => 58.43, ],
            ['nombre' => 'Spice Rack', 'categoria' => 'accesorios_de_cocina', 'precio_compra' => 109.95, 'precio_venta' => 149.93, 'stock_actual' => 79, 'codigo_barras' => 'SPI073', 'pasillo' => '7', 'nivel' => 'B', 'unidad_medida' => 'un', 'proveedor' => 'distribuidora_mayorista', 'descripcion' => 'The Spice Rack is a convenient organizer for your spices and seasonings. Keep your kitchen essentials within reach and neatly arranged with this stylish spice rack.', 'en_descuento' => true, 'precio_oferta' => 131.8, ],
            ['nombre' => 'Spoon', 'categoria' => 'accesorios_de_cocina', 'precio_compra' => 27.45, 'precio_venta' => 37.43, 'stock_actual' => 59, 'codigo_barras' => 'SPO074', 'pasillo' => '8', 'nivel' => 'A', 'unidad_medida' => 'un', 'proveedor' => 'distribuidora_mayorista', 'descripcion' => 'The Spoon is a versatile kitchen utensil for stirring, serving, and tasting. Its ergonomic design and durable construction make it an essential tool for every kitchen.', ],
            ['nombre' => 'Tray', 'categoria' => 'accesorios_de_cocina', 'precio_compra' => 93.45, 'precio_venta' => 127.43, 'stock_actual' => 71, 'codigo_barras' => 'TRA075', 'pasillo' => '1', 'nivel' => 'A', 'unidad_medida' => 'un', 'proveedor' => 'distribuidora_mayorista', 'descripcion' => 'The Tray is a functional and decorative item for serving snacks, appetizers, or drinks. Its stylish design makes it a versatile accessory for entertaining guests.', ],
            ['nombre' => 'Wooden Rolling Pin', 'categoria' => 'accesorios_de_cocina', 'precio_compra' => 65.95, 'precio_venta' => 89.93, 'stock_actual' => 80, 'codigo_barras' => 'WOO076', 'pasillo' => '8', 'nivel' => 'C', 'unidad_medida' => 'un', 'proveedor' => 'distribuidora_mayorista', 'descripcion' => 'The Wooden Rolling Pin is a classic kitchen tool for rolling out dough for baking. Its smooth surface and sturdy handles make it easy to achieve uniform thickness.', 'en_descuento' => true, 'precio_oferta' => 81.16, ],
            ['nombre' => 'Yellow Peeler', 'categoria' => 'accesorios_de_cocina', 'precio_compra' => 32.95, 'precio_venta' => 44.93, 'stock_actual' => 35, 'codigo_barras' => 'YEL077', 'pasillo' => '7', 'nivel' => 'B', 'unidad_medida' => 'un', 'proveedor' => 'distribuidora_mayorista', 'descripcion' => 'The Yellow Peeler is a handy tool for peeling fruits and vegetables with ease. Its bright yellow color adds a cheerful touch to your kitchen gadgets.', 'en_descuento' => true, 'precio_oferta' => 39.32, ],

            // Laptops
            ['nombre' => 'Apple MacBook Pro 14 Inch Space Grey', 'categoria' => 'laptops', 'precio_compra' => 10999.95, 'precio_venta' => 14999.93, 'stock_actual' => 24, 'codigo_barras' => 'APP078', 'pasillo' => '1', 'nivel' => 'C', 'unidad_medida' => 'un', 'proveedor' => 'apple', 'descripcion' => 'The MacBook Pro 14 Inch in Space Grey is a powerful and sleek laptop, featuring Apple\'s M1 Pro chip for exceptional performance and a stunning Retina display.', ],
            ['nombre' => 'Asus Zenbook Pro Dual Screen Laptop', 'categoria' => 'laptops', 'precio_compra' => 9899.95, 'precio_venta' => 13499.93, 'stock_actual' => 45, 'codigo_barras' => 'ASU079', 'pasillo' => '7', 'nivel' => 'C', 'unidad_medida' => 'un', 'proveedor' => 'asus', 'descripcion' => 'The Asus Zenbook Pro Dual Screen Laptop is a high-performance device with dual screens, providing productivity and versatility for creative professionals.', 'en_descuento' => true, 'precio_oferta' => 11996.04, ],
            ['nombre' => 'Huawei Matebook X Pro', 'categoria' => 'laptops', 'precio_compra' => 7699.95, 'precio_venta' => 10499.93, 'stock_actual' => 75, 'codigo_barras' => 'HUA080', 'pasillo' => '7', 'nivel' => 'A', 'unidad_medida' => 'un', 'proveedor' => 'huawei', 'descripcion' => 'The Huawei Matebook X Pro is a slim and stylish laptop with a high-resolution touchscreen display, offering a premium experience for users on the go.', 'en_descuento' => true, 'precio_oferta' => 9515.04, ],
            ['nombre' => 'Lenovo Yoga 920', 'categoria' => 'laptops', 'precio_compra' => 6049.95, 'precio_venta' => 8249.93, 'stock_actual' => 40, 'codigo_barras' => 'LEN081', 'pasillo' => '9', 'nivel' => 'C', 'unidad_medida' => 'un', 'proveedor' => 'lenovo', 'descripcion' => 'The Lenovo Yoga 920 is a 2-in-1 convertible laptop with a flexible hinge, allowing you to use it as a laptop or tablet, offering versatility and portability.', ],
            ['nombre' => 'New DELL XPS 13 9300 Laptop', 'categoria' => 'laptops', 'precio_compra' => 8249.95, 'precio_venta' => 11249.93, 'stock_actual' => 74, 'codigo_barras' => 'NEW082', 'pasillo' => '1', 'nivel' => 'A', 'unidad_medida' => 'un', 'proveedor' => 'dell', 'descripcion' => 'The New DELL XPS 13 9300 Laptop is a compact and powerful device, featuring a virtually borderless InfinityEdge display and high-end performance for various tasks.', 'en_descuento' => true, 'precio_oferta' => 9912.31, ],

            // Camisetas Hombre
            ['nombre' => 'Blue & Black Check Shirt', 'categoria' => 'camisetas_hombre', 'precio_compra' => 164.95, 'precio_venta' => 224.93, 'stock_actual' => 38, 'codigo_barras' => 'BLU083', 'pasillo' => '5', 'nivel' => 'A', 'unidad_medida' => 'un', 'proveedor' => 'fashion_trends', 'descripcion' => 'The Blue & Black Check Shirt is a stylish and comfortable men\'s shirt featuring a classic check pattern. Made from high-quality fabric, it\'s suitable for both casual and semi-formal occasions.', 'en_descuento' => true, 'precio_oferta' => 190.4, ],
            ['nombre' => 'Gigabyte Aorus Men Tshirt', 'categoria' => 'camisetas_hombre', 'precio_compra' => 137.45, 'precio_venta' => 187.43, 'stock_actual' => 90, 'codigo_barras' => 'GIG084', 'pasillo' => '8', 'nivel' => 'C', 'unidad_medida' => 'un', 'proveedor' => 'gigabyte', 'descripcion' => 'The Gigabyte Aorus Men Tshirt is a cool and casual shirt for gaming enthusiasts. With the Aorus logo and sleek design, it\'s perfect for expressing your gaming style.', ],
            ['nombre' => 'Man Plaid Shirt', 'categoria' => 'camisetas_hombre', 'precio_compra' => 192.45, 'precio_venta' => 262.43, 'stock_actual' => 82, 'codigo_barras' => 'MAN085', 'pasillo' => '1', 'nivel' => 'B', 'unidad_medida' => 'un', 'proveedor' => 'classic_wear', 'descripcion' => 'The Man Plaid Shirt is a timeless and versatile men\'s shirt with a classic plaid pattern. Its comfortable fit and casual style make it a wardrobe essential for various occasions.', 'en_descuento' => true, 'precio_oferta' => 211.26, ],
            ['nombre' => 'Man Short Sleeve Shirt', 'categoria' => 'camisetas_hombre', 'precio_compra' => 109.95, 'precio_venta' => 149.93, 'stock_actual' => 2, 'codigo_barras' => 'MAN086', 'pasillo' => '2', 'nivel' => 'C', 'unidad_medida' => 'un', 'proveedor' => 'casual_comfort', 'descripcion' => 'The Man Short Sleeve Shirt is a breezy and stylish option for warm days. With a comfortable fit and short sleeves, it\'s perfect for a laid-back yet polished look.', ],
            ['nombre' => 'Men Check Shirt', 'categoria' => 'camisetas_hombre', 'precio_compra' => 153.95, 'precio_venta' => 209.93, 'stock_actual' => 95, 'codigo_barras' => 'MEN087', 'pasillo' => '4', 'nivel' => 'B', 'unidad_medida' => 'un', 'proveedor' => 'urban_chic', 'descripcion' => 'The Men Check Shirt is a classic and versatile shirt featuring a stylish check pattern. Suitable for various occasions, it adds a smart and polished touch to your wardrobe.', 'en_descuento' => true, 'precio_oferta' => 186.04, ],

            // Zapatos Hombre
            ['nombre' => 'Nike Air Jordan 1 Red And Black', 'categoria' => 'zapatos_hombre', 'precio_compra' => 824.95, 'precio_venta' => 1124.93, 'stock_actual' => 7, 'codigo_barras' => 'NIK088', 'pasillo' => '2', 'nivel' => 'B', 'unidad_medida' => 'un', 'proveedor' => 'nike', 'descripcion' => 'The Nike Air Jordan 1 in Red and Black is an iconic basketball sneaker known for its stylish design and high-performance features, making it a favorite among sneaker enthusiasts and athletes.', ],
            ['nombre' => 'Nike Baseball Cleats', 'categoria' => 'zapatos_hombre', 'precio_compra' => 439.95, 'precio_venta' => 599.93, 'stock_actual' => 12, 'codigo_barras' => 'NIK089', 'pasillo' => '3', 'nivel' => 'C', 'unidad_medida' => 'un', 'proveedor' => 'nike', 'descripcion' => 'Nike Baseball Cleats are designed for maximum traction and performance on the baseball field. They provide stability and support for players during games and practices.', 'en_descuento' => true, 'precio_oferta' => 491.7, ],
            ['nombre' => 'Puma Future Rider Trainers', 'categoria' => 'zapatos_hombre', 'precio_compra' => 494.95, 'precio_venta' => 674.93, 'stock_actual' => 90, 'codigo_barras' => 'PUM090', 'pasillo' => '9', 'nivel' => 'A', 'unidad_medida' => 'un', 'proveedor' => 'puma', 'descripcion' => 'The Puma Future Rider Trainers offer a blend of retro style and modern comfort. Perfect for casual wear, these trainers provide a fashionable and comfortable option for everyday use.', ],
            ['nombre' => 'Sports Sneakers Off White & Red', 'categoria' => 'zapatos_hombre', 'precio_compra' => 659.95, 'precio_venta' => 899.93, 'stock_actual' => 17, 'codigo_barras' => 'SPO091', 'pasillo' => '1', 'nivel' => 'A', 'unidad_medida' => 'un', 'proveedor' => 'off_white', 'descripcion' => 'The Sports Sneakers in Off White and Red combine style and functionality, making them a fashionable choice for sports enthusiasts. The red and off-white color combination adds a bold and energetic touch.', ],
            ['nombre' => 'Sports Sneakers Off White Red', 'categoria' => 'zapatos_hombre', 'precio_compra' => 604.95, 'precio_venta' => 824.93, 'stock_actual' => 62, 'codigo_barras' => 'SPO092', 'pasillo' => '8', 'nivel' => 'B', 'unidad_medida' => 'un', 'proveedor' => 'off_white', 'descripcion' => 'Another variant of the Sports Sneakers in Off White Red, featuring a unique design. These sneakers offer style and comfort for casual occasions.', ],

            // Relojes Hombre
            ['nombre' => 'Brown Leather Belt Watch', 'categoria' => 'relojes_hombre', 'precio_compra' => 494.95, 'precio_venta' => 674.93, 'stock_actual' => 32, 'codigo_barras' => 'BRO093', 'pasillo' => '4', 'nivel' => 'A', 'unidad_medida' => 'un', 'proveedor' => 'fashion_timepieces', 'descripcion' => 'The Brown Leather Belt Watch is a stylish timepiece with a classic design. Featuring a genuine leather strap and a sleek dial, it adds a touch of sophistication to your look.', ],
            ['nombre' => 'Longines Master Collection', 'categoria' => 'relojes_hombre', 'precio_compra' => 8249.95, 'precio_venta' => 11249.93, 'stock_actual' => 100, 'codigo_barras' => 'LON094', 'pasillo' => '4', 'nivel' => 'A', 'unidad_medida' => 'un', 'proveedor' => 'longines', 'descripcion' => 'The Longines Master Collection is an elegant and refined watch known for its precision and craftsmanship. With a timeless design, it\'s a symbol of luxury and sophistication.', 'en_descuento' => true, 'precio_oferta' => 9310.44, ],
            ['nombre' => 'Rolex Cellini Date Black Dial', 'categoria' => 'relojes_hombre', 'precio_compra' => 49499.95, 'precio_venta' => 67499.93, 'stock_actual' => 40, 'codigo_barras' => 'ROL095', 'pasillo' => '5', 'nivel' => 'B', 'unidad_medida' => 'un', 'proveedor' => 'rolex', 'descripcion' => 'The Rolex Cellini Date with Black Dial is a classic and prestigious watch. With a black dial and date complication, it exudes sophistication and is a symbol of Rolex\'s heritage.', 'en_descuento' => true, 'precio_oferta' => 61505.94, ],
            ['nombre' => 'Rolex Cellini Moonphase', 'categoria' => 'relojes_hombre', 'precio_compra' => 71499.95, 'precio_venta' => 97499.93, 'stock_actual' => 36, 'codigo_barras' => 'ROL096', 'pasillo' => '7', 'nivel' => 'B', 'unidad_medida' => 'un', 'proveedor' => 'rolex', 'descripcion' => 'The Rolex Cellini Moonphase is a masterpiece of horology, featuring a moon phase complication and exquisite design. It reflects Rolex\'s commitment to precision and elegance.', 'en_descuento' => true, 'precio_oferta' => 80417.94, ],
            ['nombre' => 'Rolex Datejust', 'categoria' => 'relojes_hombre', 'precio_compra' => 60499.95, 'precio_venta' => 82499.93, 'stock_actual' => 86, 'codigo_barras' => 'ROL097', 'pasillo' => '5', 'nivel' => 'C', 'unidad_medida' => 'un', 'proveedor' => 'rolex', 'descripcion' => 'The Rolex Datejust is an iconic and versatile timepiece with a date window. Known for its timeless design and reliability, it\'s a symbol of Rolex\'s watchmaking excellence.', ],
            ['nombre' => 'Rolex Submariner Watch', 'categoria' => 'relojes_hombre', 'precio_compra' => 76999.95, 'precio_venta' => 104999.93, 'stock_actual' => 55, 'codigo_barras' => 'ROL098', 'pasillo' => '6', 'nivel' => 'B', 'unidad_medida' => 'un', 'proveedor' => 'rolex', 'descripcion' => 'The Rolex Submariner is a legendary dive watch with a rich history. Known for its durability and water resistance, it\'s a symbol of adventure and exploration.', ],

            // Accesorios Móviles
            ['nombre' => 'Amazon Echo Plus', 'categoria' => 'accesorios_moviles', 'precio_compra' => 549.95, 'precio_venta' => 749.93, 'stock_actual' => 61, 'codigo_barras' => 'AMA099', 'pasillo' => '2', 'nivel' => 'C', 'unidad_medida' => 'un', 'proveedor' => 'amazon', 'descripcion' => 'The Amazon Echo Plus is a smart speaker with built-in Alexa voice control. It features premium sound quality and serves as a hub for controlling smart home devices.', 'en_descuento' => true, 'precio_oferta' => 659.41, ],
            ['nombre' => 'Apple Airpods', 'categoria' => 'accesorios_moviles', 'precio_compra' => 714.95, 'precio_venta' => 974.93, 'stock_actual' => 67, 'codigo_barras' => 'APP100', 'pasillo' => '2', 'nivel' => 'C', 'unidad_medida' => 'un', 'proveedor' => 'apple', 'descripcion' => 'The Apple Airpods offer a seamless wireless audio experience. With easy pairing, high-quality sound, and Siri integration, they are perfect for on-the-go listening.', 'en_descuento' => true, 'precio_oferta' => 823.43, ],
            ['nombre' => 'Apple AirPods Max Silver', 'categoria' => 'accesorios_moviles', 'precio_compra' => 3024.95, 'precio_venta' => 4124.93, 'stock_actual' => 59, 'codigo_barras' => 'APP101', 'pasillo' => '3', 'nivel' => 'B', 'unidad_medida' => 'un', 'proveedor' => 'apple', 'descripcion' => 'The Apple AirPods Max in Silver are premium over-ear headphones with high-fidelity audio, adaptive EQ, and active noise cancellation. Experience immersive sound in style.', 'en_descuento' => true, 'precio_oferta' => 3561.05, ],
            ['nombre' => 'Apple Airpower Wireless Charger', 'categoria' => 'accesorios_moviles', 'precio_compra' => 439.95, 'precio_venta' => 599.93, 'stock_actual' => 1, 'codigo_barras' => 'APP102', 'pasillo' => '8', 'nivel' => 'B', 'unidad_medida' => 'un', 'proveedor' => 'apple', 'descripcion' => 'The Apple AirPower Wireless Charger provides a convenient way to charge your compatible Apple devices wirelessly. Simply place your devices on the charging mat for effortless charging.', ],
            ['nombre' => 'Apple HomePod Mini Cosmic Grey', 'categoria' => 'accesorios_moviles', 'precio_compra' => 549.95, 'precio_venta' => 749.93, 'stock_actual' => 27, 'codigo_barras' => 'APP103', 'pasillo' => '1', 'nivel' => 'B', 'unidad_medida' => 'un', 'proveedor' => 'apple', 'descripcion' => 'The Apple HomePod Mini in Cosmic Grey is a compact smart speaker that delivers impressive audio and integrates seamlessly with the Apple ecosystem for a smart home experience.', 'en_descuento' => true, 'precio_oferta' => 614.19, ],
            ['nombre' => 'Apple iPhone Charger', 'categoria' => 'accesorios_moviles', 'precio_compra' => 109.95, 'precio_venta' => 149.93, 'stock_actual' => 31, 'codigo_barras' => 'APP104', 'pasillo' => '9', 'nivel' => 'C', 'unidad_medida' => 'un', 'proveedor' => 'apple', 'descripcion' => 'The Apple iPhone Charger is a high-quality charger designed for fast and efficient charging of your iPhone. Ensure your device stays powered up and ready to go.', 'en_descuento' => true, 'precio_oferta' => 122.16, ],
            ['nombre' => 'Apple MagSafe Battery Pack', 'categoria' => 'accesorios_moviles', 'precio_compra' => 549.95, 'precio_venta' => 749.93, 'stock_actual' => 1, 'codigo_barras' => 'APP105', 'pasillo' => '9', 'nivel' => 'C', 'unidad_medida' => 'un', 'proveedor' => 'apple', 'descripcion' => 'The Apple MagSafe Battery Pack is a portable and convenient way to add extra battery life to your MagSafe-compatible iPhone. Attach it magnetically for a secure connection.', 'en_descuento' => true, 'precio_oferta' => 621.17, ],
            ['nombre' => 'Apple Watch Series 4 Gold', 'categoria' => 'accesorios_moviles', 'precio_compra' => 1924.95, 'precio_venta' => 2624.93, 'stock_actual' => 33, 'codigo_barras' => 'APP106', 'pasillo' => '7', 'nivel' => 'B', 'unidad_medida' => 'un', 'proveedor' => 'apple', 'descripcion' => 'The Apple Watch Series 4 in Gold is a stylish and advanced smartwatch with features like heart rate monitoring, fitness tracking, and a beautiful Retina display.', 'en_descuento' => true, 'precio_oferta' => 2309.41, ],
            ['nombre' => 'Beats Flex Wireless Earphones', 'categoria' => 'accesorios_moviles', 'precio_compra' => 274.95, 'precio_venta' => 374.93, 'stock_actual' => 50, 'codigo_barras' => 'BEA107', 'pasillo' => '9', 'nivel' => 'A', 'unidad_medida' => 'un', 'proveedor' => 'beats', 'descripcion' => 'The Beats Flex Wireless Earphones offer a comfortable and versatile audio experience. With magnetic earbuds and up to 12 hours of battery life, they are ideal for everyday use.', ],
            ['nombre' => 'iPhone 12 Silicone Case with MagSafe Plum', 'categoria' => 'accesorios_moviles', 'precio_compra' => 164.95, 'precio_venta' => 224.93, 'stock_actual' => 69, 'codigo_barras' => 'IPH108', 'pasillo' => '7', 'nivel' => 'B', 'unidad_medida' => 'un', 'proveedor' => 'apple', 'descripcion' => 'The iPhone 12 Silicone Case with MagSafe in Plum is a stylish and protective case designed for the iPhone 12. It features MagSafe technology for easy attachment of accessories.', 'en_descuento' => true, 'precio_oferta' => 193.78, ],
            ['nombre' => 'Monopod', 'categoria' => 'accesorios_moviles', 'precio_compra' => 109.95, 'precio_venta' => 149.93, 'stock_actual' => 48, 'codigo_barras' => 'MON109', 'pasillo' => '4', 'nivel' => 'A', 'unidad_medida' => 'un', 'proveedor' => 'techgear', 'descripcion' => 'The Monopod is a versatile camera accessory for stable and adjustable shooting. Perfect for capturing selfies, group photos, and videos with ease.', 'en_descuento' => true, 'precio_oferta' => 137.07, ],
            ['nombre' => 'Selfie Lamp with iPhone', 'categoria' => 'accesorios_moviles', 'precio_compra' => 82.45, 'precio_venta' => 112.43, 'stock_actual' => 58, 'codigo_barras' => 'SEL110', 'pasillo' => '6', 'nivel' => 'C', 'unidad_medida' => 'un', 'proveedor' => 'gadgetmaster', 'descripcion' => 'The Selfie Lamp with iPhone is a portable and adjustable LED light designed to enhance your selfies and video calls. Attach it to your iPhone for well-lit photos.', 'en_descuento' => true, 'precio_oferta' => 90.62, ],
            ['nombre' => 'Selfie Stick Monopod', 'categoria' => 'accesorios_moviles', 'precio_compra' => 71.45, 'precio_venta' => 97.43, 'stock_actual' => 11, 'codigo_barras' => 'SEL111', 'pasillo' => '8', 'nivel' => 'C', 'unidad_medida' => 'un', 'proveedor' => 'snaptech', 'descripcion' => 'The Selfie Stick Monopod is a extendable and foldable device for capturing the perfect selfie or group photo. Compatible with smartphones and cameras.', 'en_descuento' => true, 'precio_oferta' => 78.8, ],
            ['nombre' => 'TV Studio Camera Pedestal', 'categoria' => 'accesorios_moviles', 'precio_compra' => 2749.95, 'precio_venta' => 3749.93, 'stock_actual' => 15, 'codigo_barras' => 'TVS112', 'pasillo' => '4', 'nivel' => 'A', 'unidad_medida' => 'un', 'proveedor' => 'provision', 'descripcion' => 'The TV Studio Camera Pedestal is a professional-grade camera support system for smooth and precise camera movements in a studio setting. Ideal for broadcast and production.', 'en_descuento' => true, 'precio_oferta' => 3438.31, ],

            // Motocicletas
            ['nombre' => 'Generic Motorcycle', 'categoria' => 'motocicletas', 'precio_compra' => 21999.95, 'precio_venta' => 29999.93, 'stock_actual' => 34, 'codigo_barras' => 'GEN113', 'pasillo' => '3', 'nivel' => 'A', 'unidad_medida' => 'un', 'proveedor' => 'generic_motors', 'descripcion' => 'The Generic Motorcycle is a versatile and reliable bike suitable for various riding preferences. With a balanced design, it provides a comfortable and efficient riding experience.', 'en_descuento' => true, 'precio_oferta' => 26369.94, ],
            ['nombre' => 'Kawasaki Z800', 'categoria' => 'motocicletas', 'precio_compra' => 49499.95, 'precio_venta' => 67499.93, 'stock_actual' => 52, 'codigo_barras' => 'KAW114', 'pasillo' => '2', 'nivel' => 'B', 'unidad_medida' => 'un', 'proveedor' => 'kawasaki', 'descripcion' => 'The Kawasaki Z800 is a powerful and agile sportbike known for its striking design and performance. It\'s equipped with advanced features, making it a favorite among motorcycle enthusiasts.', 'en_descuento' => true, 'precio_oferta' => 60905.19, ],
            ['nombre' => 'MotoGP CI.H1', 'categoria' => 'motocicletas', 'precio_compra' => 82499.95, 'precio_venta' => 112499.93, 'stock_actual' => 10, 'codigo_barras' => 'MOT115', 'pasillo' => '5', 'nivel' => 'B', 'unidad_medida' => 'un', 'proveedor' => 'motogp', 'descripcion' => 'The MotoGP CI.H1 is a high-performance motorcycle inspired by MotoGP racing technology. It offers cutting-edge features and precision engineering for an exhilarating riding experience.', ],
            ['nombre' => 'Scooter Motorcycle', 'categoria' => 'motocicletas', 'precio_compra' => 16499.95, 'precio_venta' => 22499.93, 'stock_actual' => 84, 'codigo_barras' => 'SCO116', 'pasillo' => '6', 'nivel' => 'B', 'unidad_medida' => 'un', 'proveedor' => 'scootmaster', 'descripcion' => 'The Scooter Motorcycle is a practical and fuel-efficient bike ideal for urban commuting. It features a step-through design and user-friendly controls for easy maneuverability.', ],
            ['nombre' => 'Sportbike Motorcycle', 'categoria' => 'motocicletas', 'precio_compra' => 41249.95, 'precio_venta' => 56249.93, 'stock_actual' => 0, 'codigo_barras' => 'SPO117', 'pasillo' => '2', 'nivel' => 'C', 'unidad_medida' => 'un', 'proveedor' => 'speedmaster', 'descripcion' => 'The Sportbike Motorcycle is designed for speed and agility, with a sleek and aerodynamic profile. It\'s suitable for riders looking for a dynamic and thrilling riding experience.', 'en_descuento' => true, 'precio_oferta' => 49736.19, ],

            // Cuidado de la Piel
            ['nombre' => 'Attitude Super Leaves Hand Soap', 'categoria' => 'cuidado_de_la_piel', 'precio_compra' => 49.45, 'precio_venta' => 67.43, 'stock_actual' => 94, 'codigo_barras' => 'ATT118', 'pasillo' => '9', 'nivel' => 'A', 'unidad_medida' => 'un', 'proveedor' => 'attitude', 'descripcion' => 'Attitude Super Leaves Hand Soap is a natural and nourishing hand soap enriched with the goodness of super leaves. It cleanses and moisturizes your hands, leaving them feeling fresh and soft.', 'en_descuento' => true, 'precio_oferta' => 54.96, ],
            ['nombre' => 'Olay Ultra Moisture Shea Butter Body Wash', 'categoria' => 'cuidado_de_la_piel', 'precio_compra' => 71.45, 'precio_venta' => 97.43, 'stock_actual' => 34, 'codigo_barras' => 'OLA119', 'pasillo' => '4', 'nivel' => 'A', 'unidad_medida' => 'un', 'proveedor' => 'olay', 'descripcion' => 'Olay Ultra Moisture Shea Butter Body Wash is a luxurious body wash that hydrates and nourishes your skin with the moisturizing power of shea butter. Enjoy a rich lather and silky-smooth skin.', 'en_descuento' => true, 'precio_oferta' => 81.46, ],
            ['nombre' => 'Vaseline Men Body and Face Lotion', 'categoria' => 'cuidado_de_la_piel', 'precio_compra' => 54.95, 'precio_venta' => 74.93, 'stock_actual' => 95, 'codigo_barras' => 'VAS120', 'pasillo' => '9', 'nivel' => 'A', 'unidad_medida' => 'un', 'proveedor' => 'vaseline', 'descripcion' => 'Vaseline Men Body and Face Lotion is a specially formulated lotion designed to provide long-lasting moisture to men\'s skin. It absorbs quickly and helps keep the skin hydrated and healthy.', 'en_descuento' => true, 'precio_oferta' => 64.9, ],

            // Smartphones
            ['nombre' => 'iPhone 5s', 'categoria' => 'smartphones', 'precio_compra' => 1099.95, 'precio_venta' => 1499.93, 'stock_actual' => 25, 'codigo_barras' => 'IPH121', 'pasillo' => '4', 'nivel' => 'C', 'unidad_medida' => 'un', 'proveedor' => 'apple', 'descripcion' => 'The iPhone 5s is a classic smartphone known for its compact design and advanced features during its release. While it\'s an older model, it still provides a reliable user experience.', 'en_descuento' => true, 'precio_oferta' => 1306.29, ],
            ['nombre' => 'iPhone 6', 'categoria' => 'smartphones', 'precio_compra' => 1649.95, 'precio_venta' => 2249.93, 'stock_actual' => 60, 'codigo_barras' => 'IPH122', 'pasillo' => '1', 'nivel' => 'A', 'unidad_medida' => 'un', 'proveedor' => 'apple', 'descripcion' => 'The iPhone 6 is a stylish and capable smartphone with a larger display and improved performance. It introduced new features and design elements, making it a popular choice in its time.', ],
            ['nombre' => 'iPhone 13 Pro', 'categoria' => 'smartphones', 'precio_compra' => 6049.95, 'precio_venta' => 8249.93, 'stock_actual' => 56, 'codigo_barras' => 'IPH123', 'pasillo' => '8', 'nivel' => 'A', 'unidad_medida' => 'un', 'proveedor' => 'apple', 'descripcion' => 'The iPhone 13 Pro is a cutting-edge smartphone with a powerful camera system, high-performance chip, and stunning display. It offers advanced features for users who demand top-notch technology.', 'en_descuento' => true, 'precio_oferta' => 7476.91, ],
            ['nombre' => 'iPhone X', 'categoria' => 'smartphones', 'precio_compra' => 4949.95, 'precio_venta' => 6749.93, 'stock_actual' => 37, 'codigo_barras' => 'IPH124', 'pasillo' => '2', 'nivel' => 'A', 'unidad_medida' => 'un', 'proveedor' => 'apple', 'descripcion' => 'The iPhone X is a flagship smartphone featuring a bezel-less OLED display, facial recognition technology (Face ID), and impressive performance. It represents a milestone in iPhone design and innovation.', 'en_descuento' => true, 'precio_oferta' => 5427.62, ],
            ['nombre' => 'Oppo A57', 'categoria' => 'smartphones', 'precio_compra' => 1374.95, 'precio_venta' => 1874.93, 'stock_actual' => 19, 'codigo_barras' => 'OPP125', 'pasillo' => '9', 'nivel' => 'C', 'unidad_medida' => 'un', 'proveedor' => 'oppo', 'descripcion' => 'The Oppo A57 is a mid-range smartphone known for its sleek design and capable features. It offers a balance of performance and affordability, making it a popular choice.', ],
            ['nombre' => 'Oppo F19 Pro Plus', 'categoria' => 'smartphones', 'precio_compra' => 2199.95, 'precio_venta' => 2999.93, 'stock_actual' => 78, 'codigo_barras' => 'OPP126', 'pasillo' => '4', 'nivel' => 'B', 'unidad_medida' => 'un', 'proveedor' => 'oppo', 'descripcion' => 'The Oppo F19 Pro Plus is a feature-rich smartphone with a focus on camera capabilities. It boasts advanced photography features and a powerful performance for a premium user experience.', 'en_descuento' => true, 'precio_oferta' => 2440.74, ],
            ['nombre' => 'Oppo K1', 'categoria' => 'smartphones', 'precio_compra' => 1649.95, 'precio_venta' => 2249.93, 'stock_actual' => 55, 'codigo_barras' => 'OPP127', 'pasillo' => '9', 'nivel' => 'B', 'unidad_medida' => 'un', 'proveedor' => 'oppo', 'descripcion' => 'The Oppo K1 series offers a range of smartphones with various features and specifications. Known for their stylish design and reliable performance, the Oppo K1 series caters to diverse user preferences.', 'en_descuento' => true, 'precio_oferta' => 1838.42, ],
            ['nombre' => 'Realme C35', 'categoria' => 'smartphones', 'precio_compra' => 824.95, 'precio_venta' => 1124.93, 'stock_actual' => 48, 'codigo_barras' => 'REA128', 'pasillo' => '2', 'nivel' => 'A', 'unidad_medida' => 'un', 'proveedor' => 'realme', 'descripcion' => 'The Realme C35 is a budget-friendly smartphone with a focus on providing essential features for everyday use. It offers a reliable performance and user-friendly experience.', 'en_descuento' => true, 'precio_oferta' => 952.82, ],
            ['nombre' => 'Realme X', 'categoria' => 'smartphones', 'precio_compra' => 1649.95, 'precio_venta' => 2249.93, 'stock_actual' => 12, 'codigo_barras' => 'REA129', 'pasillo' => '5', 'nivel' => 'C', 'unidad_medida' => 'un', 'proveedor' => 'realme', 'descripcion' => 'The Realme X is a mid-range smartphone known for its sleek design and impressive display. It offers a good balance of performance and camera capabilities for users seeking a quality device.', ],
            ['nombre' => 'Realme XT', 'categoria' => 'smartphones', 'precio_compra' => 1924.95, 'precio_venta' => 2624.93, 'stock_actual' => 80, 'codigo_barras' => 'REA130', 'pasillo' => '1', 'nivel' => 'C', 'unidad_medida' => 'un', 'proveedor' => 'realme', 'descripcion' => 'The Realme XT is a feature-rich smartphone with a focus on camera technology. It comes equipped with advanced camera sensors, delivering high-quality photos and videos for photography enthusiasts.', 'en_descuento' => true, 'precio_oferta' => 2322.8, ],
            ['nombre' => 'Samsung Galaxy S7', 'categoria' => 'smartphones', 'precio_compra' => 1649.95, 'precio_venta' => 2249.93, 'stock_actual' => 67, 'codigo_barras' => 'SAM131', 'pasillo' => '7', 'nivel' => 'C', 'unidad_medida' => 'un', 'proveedor' => 'samsung', 'descripcion' => 'The Samsung Galaxy S7 is a flagship smartphone known for its sleek design and advanced features. It features a high-resolution display, powerful camera, and robust performance.', 'en_descuento' => true, 'precio_oferta' => 1810.07, ],
            ['nombre' => 'Samsung Galaxy S8', 'categoria' => 'smartphones', 'precio_compra' => 2749.95, 'precio_venta' => 3749.93, 'stock_actual' => 0, 'codigo_barras' => 'SAM132', 'pasillo' => '1', 'nivel' => 'B', 'unidad_medida' => 'un', 'proveedor' => 'samsung', 'descripcion' => 'The Samsung Galaxy S8 is a premium smartphone with an Infinity Display, offering a stunning visual experience. It boasts advanced camera capabilities and cutting-edge technology.', 'en_descuento' => true, 'precio_oferta' => 3020.57, ],
            ['nombre' => 'Samsung Galaxy S10', 'categoria' => 'smartphones', 'precio_compra' => 3849.95, 'precio_venta' => 5249.93, 'stock_actual' => 19, 'codigo_barras' => 'SAM133', 'pasillo' => '8', 'nivel' => 'B', 'unidad_medida' => 'un', 'proveedor' => 'samsung', 'descripcion' => 'The Samsung Galaxy S10 is a flagship device featuring a dynamic AMOLED display, versatile camera system, and powerful performance. It represents innovation and excellence in smartphone technology.', ],
            ['nombre' => 'Vivo S1', 'categoria' => 'smartphones', 'precio_compra' => 1374.95, 'precio_venta' => 1874.93, 'stock_actual' => 50, 'codigo_barras' => 'VIV134', 'pasillo' => '8', 'nivel' => 'C', 'unidad_medida' => 'un', 'proveedor' => 'vivo', 'descripcion' => 'The Vivo S1 is a stylish and mid-range smartphone offering a blend of design and performance. It features a vibrant display, capable camera system, and reliable functionality.', 'en_descuento' => true, 'precio_oferta' => 1684.25, ],
            ['nombre' => 'Vivo V9', 'categoria' => 'smartphones', 'precio_compra' => 1649.95, 'precio_venta' => 2249.93, 'stock_actual' => 82, 'codigo_barras' => 'VIV135', 'pasillo' => '2', 'nivel' => 'C', 'unidad_medida' => 'un', 'proveedor' => 'vivo', 'descripcion' => 'The Vivo V9 is a smartphone known for its sleek design and emphasis on capturing high-quality selfies. It features a notch display, dual-camera setup, and a modern design.', 'en_descuento' => true, 'precio_oferta' => 1852.37, ],
            ['nombre' => 'Vivo X21', 'categoria' => 'smartphones', 'precio_compra' => 2749.95, 'precio_venta' => 3749.93, 'stock_actual' => 7, 'codigo_barras' => 'VIV136', 'pasillo' => '4', 'nivel' => 'A', 'unidad_medida' => 'un', 'proveedor' => 'vivo', 'descripcion' => 'The Vivo X21 is a premium smartphone with a focus on cutting-edge technology. It features an in-display fingerprint sensor, a high-resolution display, and advanced camera capabilities.', 'en_descuento' => true, 'precio_oferta' => 3097.07, ],

            // Accesorios Deportivos
            ['nombre' => 'American Football', 'categoria' => 'accesorios_deportivos', 'precio_compra' => 109.95, 'precio_venta' => 149.93, 'stock_actual' => 53, 'codigo_barras' => 'AME137', 'pasillo' => '4', 'nivel' => 'B', 'unidad_medida' => 'un', 'proveedor' => 'distribuidora_mayorista', 'descripcion' => 'The American Football is a classic ball used in American football games. It is designed for throwing and catching, making it an essential piece of equipment for the sport.', ],
            ['nombre' => 'Baseball Ball', 'categoria' => 'accesorios_deportivos', 'precio_compra' => 49.45, 'precio_venta' => 67.43, 'stock_actual' => 100, 'codigo_barras' => 'BAS138', 'pasillo' => '9', 'nivel' => 'B', 'unidad_medida' => 'un', 'proveedor' => 'distribuidora_mayorista', 'descripcion' => 'The Baseball Ball is a standard baseball used in baseball games. It features a durable leather cover and is designed for pitching, hitting, and fielding in the game of baseball.', ],
            ['nombre' => 'Baseball Glove', 'categoria' => 'accesorios_deportivos', 'precio_compra' => 137.45, 'precio_venta' => 187.43, 'stock_actual' => 22, 'codigo_barras' => 'BAS139', 'pasillo' => '9', 'nivel' => 'A', 'unidad_medida' => 'un', 'proveedor' => 'distribuidora_mayorista', 'descripcion' => 'The Baseball Glove is a protective glove worn by baseball players. It is designed to catch and field the baseball, providing players with comfort and control during the game.', ],
            ['nombre' => 'Basketball', 'categoria' => 'accesorios_deportivos', 'precio_compra' => 82.45, 'precio_venta' => 112.43, 'stock_actual' => 75, 'codigo_barras' => 'BAS140', 'pasillo' => '8', 'nivel' => 'C', 'unidad_medida' => 'un', 'proveedor' => 'distribuidora_mayorista', 'descripcion' => 'The Basketball is a standard ball used in basketball games. It is designed for dribbling, shooting, and passing in the game of basketball, suitable for both indoor and outdoor play.', ],
            ['nombre' => 'Basketball Rim', 'categoria' => 'accesorios_deportivos', 'precio_compra' => 219.95, 'precio_venta' => 299.93, 'stock_actual' => 43, 'codigo_barras' => 'BAS141', 'pasillo' => '2', 'nivel' => 'B', 'unidad_medida' => 'un', 'proveedor' => 'distribuidora_mayorista', 'descripcion' => 'The Basketball Rim is a sturdy hoop and net assembly mounted on a basketball backboard. It provides a target for shooting and scoring in the game of basketball.', ],
            ['nombre' => 'Cricket Ball', 'categoria' => 'accesorios_deportivos', 'precio_compra' => 71.45, 'precio_venta' => 97.43, 'stock_actual' => 30, 'codigo_barras' => 'CRI142', 'pasillo' => '2', 'nivel' => 'C', 'unidad_medida' => 'un', 'proveedor' => 'distribuidora_mayorista', 'descripcion' => 'The Cricket Ball is a hard leather ball used in the sport of cricket. It is bowled and batted in the game, and its hardness and seam contribute to the dynamics of cricket play.', 'en_descuento' => true, 'precio_oferta' => 89.02, ],
            ['nombre' => 'Cricket Bat', 'categoria' => 'accesorios_deportivos', 'precio_compra' => 164.95, 'precio_venta' => 224.93, 'stock_actual' => 98, 'codigo_barras' => 'CRI143', 'pasillo' => '6', 'nivel' => 'C', 'unidad_medida' => 'un', 'proveedor' => 'distribuidora_mayorista', 'descripcion' => 'The Cricket Bat is an essential piece of cricket equipment used by batsmen to hit the cricket ball. It is made of wood and comes in various sizes and designs.', ],
            ['nombre' => 'Cricket Helmet', 'categoria' => 'accesorios_deportivos', 'precio_compra' => 247.45, 'precio_venta' => 337.43, 'stock_actual' => 10, 'codigo_barras' => 'CRI144', 'pasillo' => '5', 'nivel' => 'B', 'unidad_medida' => 'un', 'proveedor' => 'distribuidora_mayorista', 'descripcion' => 'The Cricket Helmet is a protective headgear worn by cricket players, especially batsmen and wicketkeepers. It provides protection against fast bowling and bouncers.', 'en_descuento' => true, 'precio_oferta' => 304.9, ],
            ['nombre' => 'Cricket Wicket', 'categoria' => 'accesorios_deportivos', 'precio_compra' => 164.95, 'precio_venta' => 224.93, 'stock_actual' => 25, 'codigo_barras' => 'CRI145', 'pasillo' => '5', 'nivel' => 'A', 'unidad_medida' => 'un', 'proveedor' => 'distribuidora_mayorista', 'descripcion' => 'The Cricket Wicket is a set of three stumps and two bails, forming a wicket used in the sport of cricket. Batsmen aim to protect the wicket while scoring runs.', 'en_descuento' => true, 'precio_oferta' => 186.85, ],
            ['nombre' => 'Feather Shuttlecock', 'categoria' => 'accesorios_deportivos', 'precio_compra' => 32.95, 'precio_venta' => 44.93, 'stock_actual' => 95, 'codigo_barras' => 'FEA146', 'pasillo' => '9', 'nivel' => 'A', 'unidad_medida' => 'un', 'proveedor' => 'distribuidora_mayorista', 'descripcion' => 'The Feather Shuttlecock is used in the sport of badminton. It features natural feathers and is designed for high-speed play, providing stability and accuracy during matches.', ],
            ['nombre' => 'Football', 'categoria' => 'accesorios_deportivos', 'precio_compra' => 98.95, 'precio_venta' => 134.93, 'stock_actual' => 96, 'codigo_barras' => 'FOO147', 'pasillo' => '3', 'nivel' => 'B', 'unidad_medida' => 'un', 'proveedor' => 'distribuidora_mayorista', 'descripcion' => 'The Football, also known as a soccer ball, is the standard ball used in the sport of football (soccer). It is designed for kicking and passing in the game.', ],
            ['nombre' => 'Golf Ball', 'categoria' => 'accesorios_deportivos', 'precio_compra' => 54.95, 'precio_venta' => 74.93, 'stock_actual' => 84, 'codigo_barras' => 'GOL148', 'pasillo' => '2', 'nivel' => 'B', 'unidad_medida' => 'un', 'proveedor' => 'distribuidora_mayorista', 'descripcion' => 'The Golf Ball is a small ball used in the sport of golf. It features dimples on its surface, providing aerodynamic lift and distance when struck by a golf club.', 'en_descuento' => true, 'precio_oferta' => 61.91, ],
            ['nombre' => 'Iron Golf', 'categoria' => 'accesorios_deportivos', 'precio_compra' => 274.95, 'precio_venta' => 374.93, 'stock_actual' => 90, 'codigo_barras' => 'IRO149', 'pasillo' => '8', 'nivel' => 'A', 'unidad_medida' => 'un', 'proveedor' => 'distribuidora_mayorista', 'descripcion' => 'The Iron Golf is a type of golf club designed for various golf shots. It features a solid metal head and is used for approach shots, chipping, and other golfing techniques.', ],
            ['nombre' => 'Metal Baseball Bat', 'categoria' => 'accesorios_deportivos', 'precio_compra' => 164.95, 'precio_venta' => 224.93, 'stock_actual' => 16, 'codigo_barras' => 'MET150', 'pasillo' => '8', 'nivel' => 'A', 'unidad_medida' => 'un', 'proveedor' => 'distribuidora_mayorista', 'descripcion' => 'The Metal Baseball Bat is a durable and lightweight baseball bat made from metal alloys. It is commonly used in baseball games for hitting and batting practice.', 'en_descuento' => true, 'precio_oferta' => 181.05, ],
            ['nombre' => 'Tennis Ball', 'categoria' => 'accesorios_deportivos', 'precio_compra' => 38.45, 'precio_venta' => 52.43, 'stock_actual' => 28, 'codigo_barras' => 'TEN151', 'pasillo' => '4', 'nivel' => 'C', 'unidad_medida' => 'un', 'proveedor' => 'distribuidora_mayorista', 'descripcion' => 'The Tennis Ball is a standard ball used in the sport of tennis. It is designed for bouncing and hitting with tennis rackets during matches or practice sessions.', 'en_descuento' => true, 'precio_oferta' => 46.26, ],
            ['nombre' => 'Tennis Racket', 'categoria' => 'accesorios_deportivos', 'precio_compra' => 274.95, 'precio_venta' => 374.93, 'stock_actual' => 6, 'codigo_barras' => 'TEN152', 'pasillo' => '7', 'nivel' => 'C', 'unidad_medida' => 'un', 'proveedor' => 'distribuidora_mayorista', 'descripcion' => 'The Tennis Racket is an essential piece of equipment used in the sport of tennis. It features a frame with strings and a grip, allowing players to hit the tennis ball.', 'en_descuento' => true, 'precio_oferta' => 301.41, ],
            ['nombre' => 'Volleyball', 'categoria' => 'accesorios_deportivos', 'precio_compra' => 65.95, 'precio_venta' => 89.93, 'stock_actual' => 0, 'codigo_barras' => 'VOL153', 'pasillo' => '1', 'nivel' => 'C', 'unidad_medida' => 'un', 'proveedor' => 'distribuidora_mayorista', 'descripcion' => 'The Volleyball is a standard ball used in the sport of volleyball. It is designed for passing, setting, and spiking over the net during volleyball matches.', 'en_descuento' => true, 'precio_oferta' => 78.91, ],

            // Lentes de Sol
            ['nombre' => 'Black Sun Glasses', 'categoria' => 'lentes_de_sol', 'precio_compra' => 164.95, 'precio_venta' => 224.93, 'stock_actual' => 60, 'codigo_barras' => 'BLA154', 'pasillo' => '2', 'nivel' => 'A', 'unidad_medida' => 'un', 'proveedor' => 'fashion_shades', 'descripcion' => 'The Black Sun Glasses are a classic and stylish choice, featuring a sleek black frame and tinted lenses. They provide both UV protection and a fashionable look.', ],
            ['nombre' => 'Classic Sun Glasses', 'categoria' => 'lentes_de_sol', 'precio_compra' => 137.45, 'precio_venta' => 187.43, 'stock_actual' => 1, 'codigo_barras' => 'CLA155', 'pasillo' => '6', 'nivel' => 'A', 'unidad_medida' => 'un', 'proveedor' => 'fashion_shades', 'descripcion' => 'The Classic Sun Glasses offer a timeless design with a neutral frame and UV-protected lenses. These sunglasses are versatile and suitable for various occasions.', ],
            ['nombre' => 'Green and Black Glasses', 'categoria' => 'lentes_de_sol', 'precio_compra' => 192.45, 'precio_venta' => 262.43, 'stock_actual' => 24, 'codigo_barras' => 'GRE156', 'pasillo' => '2', 'nivel' => 'B', 'unidad_medida' => 'un', 'proveedor' => 'fashion_shades', 'descripcion' => 'The Green and Black Glasses feature a bold combination of green and black colors, adding a touch of vibrancy to your eyewear collection. They are both stylish and eye-catching.', ],
            ['nombre' => 'Party Glasses', 'categoria' => 'lentes_de_sol', 'precio_compra' => 109.95, 'precio_venta' => 149.93, 'stock_actual' => 86, 'codigo_barras' => 'PAR157', 'pasillo' => '4', 'nivel' => 'C', 'unidad_medida' => 'un', 'proveedor' => 'fashion_fun', 'descripcion' => 'The Party Glasses are designed to add flair to your party outfit. With unique shapes or colorful frames, they\'re perfect for adding a playful touch to your look during celebrations.', 'en_descuento' => true, 'precio_oferta' => 133.11, ],
            ['nombre' => 'Sunglasses', 'categoria' => 'lentes_de_sol', 'precio_compra' => 126.45, 'precio_venta' => 172.43, 'stock_actual' => 27, 'codigo_barras' => 'SUN158', 'pasillo' => '5', 'nivel' => 'C', 'unidad_medida' => 'un', 'proveedor' => 'fashion_shades', 'descripcion' => 'The Sunglasses offer a classic and simple design with a focus on functionality. These sunglasses provide essential UV protection while maintaining a timeless look.', ],

            // Tablets
            ['nombre' => 'iPad Mini 2021 Starlight', 'categoria' => 'tablets', 'precio_compra' => 2749.95, 'precio_venta' => 3749.93, 'stock_actual' => 47, 'codigo_barras' => 'IPA159', 'pasillo' => '7', 'nivel' => 'A', 'unidad_medida' => 'un', 'proveedor' => 'apple', 'descripcion' => 'The iPad Mini 2021 in Starlight is a compact and powerful tablet from Apple. Featuring a stunning Retina display, powerful A-series chip, and a sleek design, it offers a premium tablet experience.', ],
            ['nombre' => 'Samsung Galaxy Tab S8 Plus Grey', 'categoria' => 'tablets', 'precio_compra' => 3299.95, 'precio_venta' => 4499.93, 'stock_actual' => 62, 'codigo_barras' => 'SAM160', 'pasillo' => '1', 'nivel' => 'C', 'unidad_medida' => 'un', 'proveedor' => 'samsung', 'descripcion' => 'The Samsung Galaxy Tab S8 Plus in Grey is a high-performance Android tablet by Samsung. With a large AMOLED display, powerful processor, and S Pen support, it\'s ideal for productivity and entertainment.', 'en_descuento' => true, 'precio_oferta' => 3900.99, ],
            ['nombre' => 'Samsung Galaxy Tab White', 'categoria' => 'tablets', 'precio_compra' => 1924.95, 'precio_venta' => 2624.93, 'stock_actual' => 92, 'codigo_barras' => 'SAM161', 'pasillo' => '1', 'nivel' => 'A', 'unidad_medida' => 'un', 'proveedor' => 'samsung', 'descripcion' => 'The Samsung Galaxy Tab in White is a sleek and versatile Android tablet. With a vibrant display, long-lasting battery, and a range of features, it offers a great user experience for various tasks.', 'en_descuento' => true, 'precio_oferta' => 2147.19, ],

            // Blusas
            ['nombre' => 'Blue Frock', 'categoria' => 'blusas', 'precio_compra' => 164.95, 'precio_venta' => 224.93, 'stock_actual' => 52, 'codigo_barras' => 'BLU162', 'pasillo' => '5', 'nivel' => 'A', 'unidad_medida' => 'un', 'proveedor' => 'distribuidora_mayorista', 'descripcion' => 'The Blue Frock is a charming and stylish dress for various occasions. With a vibrant blue color and a comfortable design, it adds a touch of elegance to your wardrobe.', 'en_descuento' => true, 'precio_oferta' => 197.65, ],
            ['nombre' => 'Girl Summer Dress', 'categoria' => 'blusas', 'precio_compra' => 109.95, 'precio_venta' => 149.93, 'stock_actual' => 43, 'codigo_barras' => 'GIR163', 'pasillo' => '3', 'nivel' => 'C', 'unidad_medida' => 'un', 'proveedor' => 'distribuidora_mayorista', 'descripcion' => 'The Girl Summer Dress is a cute and breezy dress designed for warm weather. With playful patterns and lightweight fabric, it\'s perfect for keeping cool and stylish during the summer.', 'en_descuento' => true, 'precio_oferta' => 121.14, ],
            ['nombre' => 'Gray Dress', 'categoria' => 'blusas', 'precio_compra' => 192.45, 'precio_venta' => 262.43, 'stock_actual' => 55, 'codigo_barras' => 'GRA164', 'pasillo' => '8', 'nivel' => 'B', 'unidad_medida' => 'un', 'proveedor' => 'distribuidora_mayorista', 'descripcion' => 'The Gray Dress is a versatile and chic option for various occasions. With a neutral gray color, it can be dressed up or down, making it a wardrobe staple for any fashion-forward individual.', 'en_descuento' => true, 'precio_oferta' => 224.95, ],
            ['nombre' => 'Short Frock', 'categoria' => 'blusas', 'precio_compra' => 137.45, 'precio_venta' => 187.43, 'stock_actual' => 22, 'codigo_barras' => 'SHO165', 'pasillo' => '7', 'nivel' => 'C', 'unidad_medida' => 'un', 'proveedor' => 'distribuidora_mayorista', 'descripcion' => 'The Short Frock is a playful and trendy dress with a shorter length. Ideal for casual outings or special occasions, it combines style and comfort for a fashionable look.', 'en_descuento' => true, 'precio_oferta' => 162.22, ],
            ['nombre' => 'Tartan Dress', 'categoria' => 'blusas', 'precio_compra' => 219.95, 'precio_venta' => 299.93, 'stock_actual' => 73, 'codigo_barras' => 'TAR166', 'pasillo' => '6', 'nivel' => 'B', 'unidad_medida' => 'un', 'proveedor' => 'distribuidora_mayorista', 'descripcion' => 'The Tartan Dress features a classic tartan pattern, bringing a timeless and sophisticated touch to your wardrobe. Perfect for fall and winter, it adds a hint of traditional charm.', 'en_descuento' => true, 'precio_oferta' => 261.09, ],

            // Vehículos
            ['nombre' => '300 Touring', 'categoria' => 'vehiculos', 'precio_compra' => 159499.95, 'precio_venta' => 217499.93, 'stock_actual' => 54, 'codigo_barras' => 'TOU167', 'pasillo' => '6', 'nivel' => 'C', 'unidad_medida' => 'un', 'proveedor' => 'chrysler', 'descripcion' => 'The 300 Touring is a stylish and comfortable sedan, known for its luxurious features and smooth performance.', ],
            ['nombre' => 'Charger SXT RWD', 'categoria' => 'vehiculos', 'precio_compra' => 181499.95, 'precio_venta' => 247499.93, 'stock_actual' => 57, 'codigo_barras' => 'CHA168', 'pasillo' => '1', 'nivel' => 'C', 'unidad_medida' => 'un', 'proveedor' => 'dodge', 'descripcion' => 'The Charger SXT RWD is a powerful and sporty rear-wheel-drive sedan, offering a blend of performance and practicality.', 'en_descuento' => true, 'precio_oferta' => 227130.69, ],
            ['nombre' => 'Dodge Hornet GT Plus', 'categoria' => 'vehiculos', 'precio_compra' => 137499.95, 'precio_venta' => 187499.93, 'stock_actual' => 82, 'codigo_barras' => 'DOD169', 'pasillo' => '6', 'nivel' => 'C', 'unidad_medida' => 'un', 'proveedor' => 'dodge', 'descripcion' => 'The Dodge Hornet GT Plus is a compact and agile hatchback, perfect for urban driving with a touch of sportiness.', ],
            ['nombre' => 'Durango SXT RWD', 'categoria' => 'vehiculos', 'precio_compra' => 203499.95, 'precio_venta' => 277499.93, 'stock_actual' => 95, 'codigo_barras' => 'DUR170', 'pasillo' => '7', 'nivel' => 'A', 'unidad_medida' => 'un', 'proveedor' => 'dodge', 'descripcion' => 'The Durango SXT RWD is a spacious and versatile SUV, known for its strong performance and family-friendly features.', 'en_descuento' => true, 'precio_oferta' => 231878.94, ],
            ['nombre' => 'Pacifica Touring', 'categoria' => 'vehiculos', 'precio_compra' => 175999.95, 'precio_venta' => 239999.93, 'stock_actual' => 53, 'codigo_barras' => 'PAC171', 'pasillo' => '2', 'nivel' => 'C', 'unidad_medida' => 'un', 'proveedor' => 'chrysler', 'descripcion' => 'The Pacifica Touring is a stylish and well-equipped minivan, offering comfort and convenience for family journeys.', 'en_descuento' => true, 'precio_oferta' => 204575.94, ],

            // Carteras Mujer
            ['nombre' => 'Blue Women\'s Handbag', 'categoria' => 'carteras_mujer', 'precio_compra' => 274.95, 'precio_venta' => 374.93, 'stock_actual' => 76, 'codigo_barras' => 'BLU172', 'pasillo' => '5', 'nivel' => 'A', 'unidad_medida' => 'un', 'proveedor' => 'fashionista', 'descripcion' => 'The Blue Women\'s Handbag is a stylish and spacious accessory for everyday use. With a vibrant blue color and multiple compartments, it combines fashion and functionality.', 'en_descuento' => true, 'precio_oferta' => 307.89, ],
            ['nombre' => 'Heshe Women\'s Leather Bag', 'categoria' => 'carteras_mujer', 'precio_compra' => 714.95, 'precio_venta' => 974.93, 'stock_actual' => 99, 'codigo_barras' => 'HES173', 'pasillo' => '7', 'nivel' => 'B', 'unidad_medida' => 'un', 'proveedor' => 'heshe', 'descripcion' => 'The Heshe Women\'s Leather Bag is a luxurious and high-quality leather bag for the sophisticated woman. With a timeless design and durable craftsmanship, it\'s a versatile accessory.', ],
            ['nombre' => 'Prada Women Bag', 'categoria' => 'carteras_mujer', 'precio_compra' => 3299.95, 'precio_venta' => 4499.93, 'stock_actual' => 75, 'codigo_barras' => 'PRA174', 'pasillo' => '2', 'nivel' => 'B', 'unidad_medida' => 'un', 'proveedor' => 'prada', 'descripcion' => 'The Prada Women Bag is an iconic designer bag that exudes elegance and luxury. Crafted with precision and featuring the Prada logo, it\'s a statement piece for fashion enthusiasts.', 'en_descuento' => true, 'precio_oferta' => 3865.89, ],
            ['nombre' => 'White Faux Leather Backpack', 'categoria' => 'carteras_mujer', 'precio_compra' => 219.95, 'precio_venta' => 299.93, 'stock_actual' => 39, 'codigo_barras' => 'WHI175', 'pasillo' => '4', 'nivel' => 'B', 'unidad_medida' => 'un', 'proveedor' => 'urban_chic', 'descripcion' => 'The White Faux Leather Backpack is a trendy and practical backpack for the modern woman. With a sleek white design and ample storage space, it\'s perfect for both casual and on-the-go styles.', 'en_descuento' => true, 'precio_oferta' => 254.34, ],
            ['nombre' => 'Women Handbag Black', 'categoria' => 'carteras_mujer', 'precio_compra' => 329.95, 'precio_venta' => 449.93, 'stock_actual' => 11, 'codigo_barras' => 'WOM176', 'pasillo' => '9', 'nivel' => 'B', 'unidad_medida' => 'un', 'proveedor' => 'elegance_collection', 'descripcion' => 'The Women Handbag in Black is a classic and versatile accessory that complements various outfits. With a timeless black color and functional design, it\'s a must-have in every woman\'s wardrobe.', 'en_descuento' => true, 'precio_oferta' => 397.6, ],

            // Vestidos Mujer
            ['nombre' => 'Black Women\'s Gown', 'categoria' => 'vestidos_mujer', 'precio_compra' => 714.95, 'precio_venta' => 974.93, 'stock_actual' => 25, 'codigo_barras' => 'BLA177', 'pasillo' => '5', 'nivel' => 'C', 'unidad_medida' => 'un', 'proveedor' => 'distribuidora_mayorista', 'descripcion' => 'The Black Women\'s Gown is an elegant and timeless evening gown. With a sleek black design, it\'s perfect for formal events and special occasions, exuding sophistication and style.', 'en_descuento' => true, 'precio_oferta' => 872.76, ],
            ['nombre' => 'Corset Leather With Skirt', 'categoria' => 'vestidos_mujer', 'precio_compra' => 494.95, 'precio_venta' => 674.93, 'stock_actual' => 30, 'codigo_barras' => 'COR178', 'pasillo' => '5', 'nivel' => 'B', 'unidad_medida' => 'un', 'proveedor' => 'distribuidora_mayorista', 'descripcion' => 'The Corset Leather With Skirt is a bold and edgy ensemble that combines a stylish corset with a matching skirt. Ideal for fashion-forward individuals, it makes a statement at any event.', 'en_descuento' => true, 'precio_oferta' => 565.19, ],
            ['nombre' => 'Corset With Black Skirt', 'categoria' => 'vestidos_mujer', 'precio_compra' => 439.95, 'precio_venta' => 599.93, 'stock_actual' => 33, 'codigo_barras' => 'COR179', 'pasillo' => '8', 'nivel' => 'B', 'unidad_medida' => 'un', 'proveedor' => 'distribuidora_mayorista', 'descripcion' => 'The Corset With Black Skirt is a chic and versatile outfit that pairs a fashionable corset with a classic black skirt. It offers a trendy and coordinated look for various occasions.', 'en_descuento' => true, 'precio_oferta' => 509.58, ],
            ['nombre' => 'Dress Pea', 'categoria' => 'vestidos_mujer', 'precio_compra' => 274.95, 'precio_venta' => 374.93, 'stock_actual' => 6, 'codigo_barras' => 'DRE180', 'pasillo' => '1', 'nivel' => 'A', 'unidad_medida' => 'un', 'proveedor' => 'distribuidora_mayorista', 'descripcion' => 'The Dress Pea is a stylish and comfortable dress with a pea pattern. Perfect for casual outings, it adds a playful and fun element to your wardrobe, making it a great choice for day-to-day wear.', 'en_descuento' => true, 'precio_oferta' => 308.64, ],
            ['nombre' => 'Marni Red & Black Suit', 'categoria' => 'vestidos_mujer', 'precio_compra' => 989.95, 'precio_venta' => 1349.93, 'stock_actual' => 62, 'codigo_barras' => 'MAR181', 'pasillo' => '4', 'nivel' => 'A', 'unidad_medida' => 'un', 'proveedor' => 'distribuidora_mayorista', 'descripcion' => 'The Marni Red & Black Suit is a sophisticated and fashion-forward suit ensemble. With a combination of red and black tones, it showcases a modern design for a bold and confident look.', 'en_descuento' => true, 'precio_oferta' => 1093.17, ],

            // Joyería Mujer
            ['nombre' => 'Green Crystal Earring', 'categoria' => 'joyeria_mujer', 'precio_compra' => 164.95, 'precio_venta' => 224.93, 'stock_actual' => 54, 'codigo_barras' => 'GRE182', 'pasillo' => '5', 'nivel' => 'B', 'unidad_medida' => 'un', 'proveedor' => 'distribuidora_mayorista', 'descripcion' => 'The Green Crystal Earring is a dazzling accessory that features a vibrant green crystal. With a classic design, it adds a touch of elegance to your ensemble, perfect for formal or special occasions.', 'en_descuento' => true, 'precio_oferta' => 190.65, ],
            ['nombre' => 'Green Oval Earring', 'categoria' => 'joyeria_mujer', 'precio_compra' => 137.45, 'precio_venta' => 187.43, 'stock_actual' => 73, 'codigo_barras' => 'GRE183', 'pasillo' => '4', 'nivel' => 'B', 'unidad_medida' => 'un', 'proveedor' => 'distribuidora_mayorista', 'descripcion' => 'The Green Oval Earring is a stylish and versatile accessory with a unique oval shape. Whether for casual or dressy occasions, its green hue and contemporary design make it a standout piece.', 'en_descuento' => true, 'precio_oferta' => 158.98, ],
            ['nombre' => 'Tropical Earring', 'categoria' => 'joyeria_mujer', 'precio_compra' => 109.95, 'precio_venta' => 149.93, 'stock_actual' => 1, 'codigo_barras' => 'TRO184', 'pasillo' => '5', 'nivel' => 'B', 'unidad_medida' => 'un', 'proveedor' => 'distribuidora_mayorista', 'descripcion' => 'The Tropical Earring is a fun and playful accessory inspired by tropical elements. Featuring vibrant colors and a lively design, it\'s perfect for adding a touch of summer to your look.', ],

            // Zapatos Mujer
            ['nombre' => 'Black & Brown Slipper', 'categoria' => 'zapatos_mujer', 'precio_compra' => 109.95, 'precio_venta' => 149.93, 'stock_actual' => 3, 'codigo_barras' => 'BLA185', 'pasillo' => '8', 'nivel' => 'B', 'unidad_medida' => 'un', 'proveedor' => 'comfort_trends', 'descripcion' => 'The Black & Brown Slipper is a comfortable and stylish choice for casual wear. Featuring a blend of black and brown colors, it adds a touch of sophistication to your relaxation.', ],
            ['nombre' => 'Calvin Klein Heel Shoes', 'categoria' => 'zapatos_mujer', 'precio_compra' => 439.95, 'precio_venta' => 599.93, 'stock_actual' => 93, 'codigo_barras' => 'CAL186', 'pasillo' => '1', 'nivel' => 'A', 'unidad_medida' => 'un', 'proveedor' => 'calvin_klein', 'descripcion' => 'Calvin Klein Heel Shoes are elegant and sophisticated, designed for formal occasions. With a classic design and high-quality materials, they complement your stylish ensemble.', ],
            ['nombre' => 'Golden Shoes Woman', 'categoria' => 'zapatos_mujer', 'precio_compra' => 274.95, 'precio_venta' => 374.93, 'stock_actual' => 88, 'codigo_barras' => 'GOL187', 'pasillo' => '7', 'nivel' => 'B', 'unidad_medida' => 'un', 'proveedor' => 'fashion_diva', 'descripcion' => 'The Golden Shoes for Women are a glamorous choice for special occasions. Featuring a golden hue and stylish design, they add a touch of luxury to your outfit.', 'en_descuento' => true, 'precio_oferta' => 322.7, ],
            ['nombre' => 'Pampi Shoes', 'categoria' => 'zapatos_mujer', 'precio_compra' => 164.95, 'precio_venta' => 224.93, 'stock_actual' => 49, 'codigo_barras' => 'PAM188', 'pasillo' => '5', 'nivel' => 'A', 'unidad_medida' => 'un', 'proveedor' => 'pampi', 'descripcion' => 'Pampi Shoes offer a blend of comfort and style for everyday use. With a versatile design, they are suitable for various casual occasions, providing a trendy and relaxed look.', 'en_descuento' => true, 'precio_oferta' => 193.12, ],
            ['nombre' => 'Red Shoes', 'categoria' => 'zapatos_mujer', 'precio_compra' => 192.45, 'precio_venta' => 262.43, 'stock_actual' => 7, 'codigo_barras' => 'RED189', 'pasillo' => '5', 'nivel' => 'A', 'unidad_medida' => 'un', 'proveedor' => 'fashion_express', 'descripcion' => 'The Red Shoes make a bold statement with their vibrant red color. Whether for a party or a casual outing, these shoes add a pop of color and style to your wardrobe.', 'en_descuento' => true, 'precio_oferta' => 216.01, ],

            // Relojes Mujer
            ['nombre' => 'IWC Ingenieur Automatic Steel', 'categoria' => 'relojes_mujer', 'precio_compra' => 27499.95, 'precio_venta' => 37499.93, 'stock_actual' => 90, 'codigo_barras' => 'IWC190', 'pasillo' => '7', 'nivel' => 'B', 'unidad_medida' => 'un', 'proveedor' => 'iwc', 'descripcion' => 'The IWC Ingenieur Automatic Steel watch is a durable and sophisticated timepiece. With a stainless steel case and automatic movement, it combines precision and style for watch enthusiasts.', 'en_descuento' => true, 'precio_oferta' => 33956.19, ],
            ['nombre' => 'Rolex Cellini Moonphase', 'categoria' => 'relojes_mujer', 'precio_compra' => 87999.95, 'precio_venta' => 119999.93, 'stock_actual' => 52, 'codigo_barras' => 'ROL191', 'pasillo' => '4', 'nivel' => 'B', 'unidad_medida' => 'un', 'proveedor' => 'rolex', 'descripcion' => 'The Rolex Cellini Moonphase watch is a masterpiece of horology. Featuring a moon phase complication, it showcases the craftsmanship and elegance that Rolex is renowned for.', ],
            ['nombre' => 'Rolex Datejust Women', 'categoria' => 'relojes_mujer', 'precio_compra' => 60499.95, 'precio_venta' => 82499.93, 'stock_actual' => 4, 'codigo_barras' => 'ROL192', 'pasillo' => '6', 'nivel' => 'B', 'unidad_medida' => 'un', 'proveedor' => 'rolex', 'descripcion' => 'The Rolex Datejust Women\'s watch is an iconic timepiece designed for women. With a timeless design and a date complication, it offers both elegance and functionality.', 'en_descuento' => true, 'precio_oferta' => 69349.44, ],
            ['nombre' => 'Watch Gold for Women', 'categoria' => 'relojes_mujer', 'precio_compra' => 4399.95, 'precio_venta' => 5999.93, 'stock_actual' => 0, 'codigo_barras' => 'WAT193', 'pasillo' => '6', 'nivel' => 'C', 'unidad_medida' => 'un', 'proveedor' => 'fashion_gold', 'descripcion' => 'The Gold Women\'s Watch is a stunning accessory that combines luxury and style. Featuring a gold-plated case and a chic design, it adds a touch of glamour to any outfit.', 'en_descuento' => true, 'precio_oferta' => 4899.54, ],
            ['nombre' => 'Women\'s Wrist Watch', 'categoria' => 'relojes_mujer', 'precio_compra' => 714.95, 'precio_venta' => 974.93, 'stock_actual' => 12, 'codigo_barras' => 'WOM194', 'pasillo' => '7', 'nivel' => 'C', 'unidad_medida' => 'un', 'proveedor' => 'fashion_co', 'descripcion' => 'The Women\'s Wrist Watch is a versatile and fashionable timepiece for everyday wear. With a comfortable strap and a simple yet elegant design, it complements various styles.', 'en_descuento' => true, 'precio_oferta' => 852.09, ],
        ];

    }
}