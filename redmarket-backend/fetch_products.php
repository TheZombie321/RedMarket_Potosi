<?php
/**
 * Script para consumir DummyJSON API (194 productos), descargar imágenes reales
 * y generar el BolivianProductSeeder actualizado.
 *
 * Uso: cd redmarket-backend && php fetch_products.php
 */

$outputDir = __DIR__ . '/storage/app/public/img/productos/main';
$seederPath = __DIR__ . '/database/seeders/BolivianProductSeeder.php';

if (!is_dir($outputDir)) {
    mkdir($outputDir, 0755, true);
}

// ─── 1. FETCH ALL PRODUCTS FROM API ───

function fetchApi(string $url): array {
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_USERAGENT, 'RedMarket/1.0');
    $json = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    if ($httpCode !== 200 || !$json) {
        echo "  Error: HTTP $httpCode\n";
        return [];
    }
    return json_decode($json, true)['products'] ?? [];
}

echo "=== CONSUMIENDO DUMMYJSON API ===\n";
$allProducts = array_merge(
    fetchApi('https://dummyjson.com/products?limit=100&skip=0'),
    fetchApi('https://dummyjson.com/products?limit=100&skip=100')
);
echo "Total: " . count($allProducts) . " productos\n";

// ─── 2. MAP CATEGORIES ───

// Traducción de categorías API a Español
$categoryTranslation = [
    'beauty' => 'Belleza',
    'fragrances' => 'Fragancias',
    'furniture' => 'Muebles',
    'groceries' => null, // se maneja por tags abajo
    'home-decoration' => 'Decoración del Hogar',
    'kitchen-accessories' => 'Accesorios de Cocina',
    'laptops' => 'Laptops',
    'mens-shirts' => 'Camisetas Hombre',
    'mens-shoes' => 'Zapatos Hombre',
    'mens-watches' => 'Relojes Hombre',
    'mobile-accessories' => 'Accesorios Móviles',
    'motorcycle' => 'Motocicletas',
    'skin-care' => 'Cuidado de la Piel',
    'smartphones' => 'Smartphones',
    'sports-accessories' => 'Accesorios Deportivos',
    'sunglasses' => 'Lentes de Sol',
    'tablets' => 'Tablets',
    'tops' => 'Blusas',
    'vehicle' => 'Vehículos',
    'womens-bags' => 'Carteras Mujer',
    'womens-dresses' => 'Vestidos Mujer',
    'womens-jewellery' => 'Joyería Mujer',
    'womens-shoes' => 'Zapatos Mujer',
    'womens-watches' => 'Relojes Mujer',
];

// ─── 3. PROVEEDORES BOLIVIANOS POR MARCA ───

$bolivianProveedores = [
    'distribuidora_mayorista' => ['nombre' => 'Distribuidora Mayorista Bolivia S.R.L.', 'contacto' => 'Carlos López', 'direccion' => 'Av. Circunvalación #500, Potosí', 'telefono' => '26543210', 'email' => 'ventas@distribuidorabolivia.bo'],
    'apple' => ['nombre' => 'iShop Bolivia S.R.L.', 'contacto' => 'María García', 'direccion' => 'MegaCenter Local 15, Av. San Martín, Santa Cruz', 'telefono' => '33567890', 'email' => 'ventas@ishopbolivia.bo'],
    'samsung' => ['nombre' => 'Samsung Electronics Bolivia S.A.', 'contacto' => 'Pedro Quispe', 'direccion' => 'Av. Ballivián #1234, Edif. Samsung, La Paz', 'telefono' => '22456789', 'email' => 'contacto@samsung.bo'],
    'huawei' => ['nombre' => 'Huawei Technologies Bolivia', 'contacto' => 'Ana Morales', 'direccion' => 'Av. 6 de Agosto #2450, La Paz', 'telefono' => '24443322', 'email' => 'ventas@huawei.bo'],
    'nokia' => ['nombre' => 'Nokia Solutions Bolivia', 'contacto' => 'Juan Pérez', 'direccion' => 'Calle Potosí #789, Cochabamba', 'telefono' => '46667788', 'email' => 'info@nokiabolivia.bo'],
    'microsoft' => ['nombre' => 'Microsoft Bolivia S.R.L.', 'contacto' => 'Roberto Vargas', 'direccion' => 'Av. Montenegro #1155, Calacoto, La Paz', 'telefono' => '22778899', 'email' => 'ventas@microsoft.bo'],
    'lenovo' => ['nombre' => 'Lenovo Bolivia S.A.', 'contacto' => 'Laura Méndez', 'direccion' => 'Av. América #345, Cochabamba', 'telefono' => '44556677', 'email' => 'contacto@lenovo.bo'],
    'hp' => ['nombre' => 'HP Bolivia S.R.L.', 'contacto' => 'Diego Gutiérrez', 'direccion' => 'Av. Busch #678, La Paz', 'telefono' => '22334455', 'email' => 'ventas@hp.bo'],
    'dell' => ['nombre' => 'Dell Technologies Bolivia', 'contacto' => 'Carmen Flores', 'direccion' => 'Av. Irala #456, Cochabamba', 'telefono' => '44998877', 'email' => 'soporte@dell.bo'],
    'asus' => ['nombre' => 'ASUS Bolivia S.R.L.', 'contacto' => 'Luis Torrez', 'direccion' => 'Calle 21 #890, Calacoto, La Paz', 'telefono' => '22776655', 'email' => 'ventas@asus.bo'],
    'google' => ['nombre' => 'Google LLC Sucursal Bolivia', 'contacto' => 'Andrea Ríos', 'direccion' => 'Av. Arce #2500, La Paz', 'telefono' => '24446688', 'email' => 'ads@google.bo'],
    'amazon' => ['nombre' => 'Amazon Web Services Bolivia', 'contacto' => 'Pablo Castillo', 'direccion' => 'Av. San Martín #1500, Santa Cruz', 'telefono' => '33557799', 'email' => 'aws@amazon.bo'],
    'sony' => ['nombre' => 'Sony Bolivia S.A.', 'contacto' => 'Jorge Fernández', 'direccion' => 'Av. 6 de Agosto #1987, La Paz', 'telefono' => '24446666', 'email' => 'contacto@sony.bo'],
    'lg' => ['nombre' => 'LG Electronics Bolivia S.R.L.', 'contacto' => 'Mónica Rojas', 'direccion' => 'Av. Blanco Galindo km 5, Cochabamba', 'telefono' => '44889900', 'email' => 'ventas@lg.bo'],
    'philips' => ['nombre' => 'Philips Bolivia S.A.', 'contacto' => 'Ricardo Méndez', 'direccion' => 'Av. Camacho #1356, La Paz', 'telefono' => '22445566', 'email' => 'info@philips.bo'],
    'nike' => ['nombre' => 'Nike Bolivia S.R.L.', 'contacto' => 'Karina Salazar', 'direccion' => 'Ventura Mall Local 25, Santa Cruz', 'telefono' => '33442211', 'email' => 'ventas@nike.bo'],
    'adidas' => ['nombre' => 'Adidas Bolivia S.A.', 'contacto' => 'Fernando Roca', 'direccion' => 'MegaCenter Local 50, Santa Cruz', 'telefono' => '33443344', 'email' => 'contacto@adidas.bo'],
    'puma' => ['nombre' => 'Puma Sports Bolivia', 'contacto' => 'Sofía Vargas', 'direccion' => 'Av. San Martín #800, Santa Cruz', 'telefono' => '33556677', 'email' => 'ventas@puma.bo'],
    'reebok' => ['nombre' => 'Reebok Bolivia S.R.L.', 'contacto' => 'Marcelo Torres', 'direccion' => 'Calle 21 #456, La Paz', 'telefono' => '22778800', 'email' => 'info@reebok.bo'],
    'gucci' => ['nombre' => 'Gucci Bolivia S.A.', 'contacto' => 'Elena Pardo', 'direccion' => 'Av. Ballivián #2000, La Paz', 'telefono' => '22447788', 'email' => 'ventas@gucci.bo'],
    'dior' => ['nombre' => 'Dior Bolivia S.R.L.', 'contacto' => 'Camila Orozco', 'direccion' => 'Calle 10 #345, Calacoto, La Paz', 'telefono' => '22779900', 'email' => 'contacto@dior.bo'],
    'chanel' => ['nombre' => 'Chanel Bolivia S.A.', 'contacto' => 'Ximena Loza', 'direccion' => 'Av. Montenegro #1000, La Paz', 'telefono' => '22446677', 'email' => 'ventas@chanel.bo'],
    'calvin_klein' => ['nombre' => 'Calvin Klein Bolivia', 'contacto' => 'Daniela Peredo', 'direccion' => 'Ventura Mall Local 30, Santa Cruz', 'telefono' => '33440011', 'email' => 'info@ck.bo'],
    'nestle' => ['nombre' => 'Nestlé Bolivia S.A.', 'contacto' => 'Raúl Quispe', 'direccion' => 'Av. 6 de Agosto #2000, La Paz', 'telefono' => '22441122', 'email' => 'pedidos@nestle.bo'],
    'coca_cola' => ['nombre' => 'Embotelladora Boliviana (Embol)', 'contacto' => 'Laura Méndez', 'direccion' => 'Av. Ecológica #200, Santa Cruz', 'telefono' => '35556677', 'email' => 'pedidos@embol.bo'],
    'pepsi' => ['nombre' => 'PepsiCo Alimentos Bolivia', 'contacto' => 'José Luis Ríos', 'direccion' => 'Zona Industrial #789, Cochabamba', 'telefono' => '44778899', 'email' => 'ventas@pepsico.bo'],
    'unilever' => ['nombre' => 'Unilever Andina Bolivia S.R.L.', 'contacto' => 'Patricia Díaz', 'direccion' => 'Av. Ecuador #1500, La Paz', 'telefono' => '22447766', 'email' => 'contacto@unilever.bo'],
    'procter_gamble' => ['nombre' => 'Procter & Gamble Bolivia S.R.L.', 'contacto' => 'Oscar Fernández', 'direccion' => 'Av. Ballivián #500, Cochabamba', 'telefono' => '44881100', 'email' => 'ventas@pg.bo'],
    'loreal' => ['nombre' => "L'Oréal Bolivia S.A.", 'contacto' => 'Claudia Álvarez', 'direccion' => 'Av. Arce #2800, La Paz', 'telefono' => '22446688', 'email' => 'contacto@loreal.bo'],
    'essence' => ['nombre' => 'Importadora Bela Vista S.R.L.', 'contacto' => 'Roberto Pinto', 'direccion' => 'Calle Comercio #456, Potosí', 'telefono' => '26223344', 'email' => 'ventas@belavista.bo'],
    'olay' => ['nombre' => 'Distribuidora de Cosméticos del Sur', 'contacto' => 'María Fernanda Roca', 'direccion' => 'Av. Monseñor Fife #123, Tarija', 'telefono' => '46661234', 'email' => 'ventas@dicosur.bo'],
    'vaseline' => ['nombre' => 'Importadora Unifarma Bolivia', 'contacto' => 'Carlos Mendoza', 'direccion' => 'Calle Bolívar #567, La Paz', 'telefono' => '22443322', 'email' => 'pedidos@unifarma.bo'],
    'puma_energy' => ['nombre' => 'Bebidas Energéticas Bolivia S.R.L.', 'contacto' => 'Mario Vargas', 'direccion' => 'Av. Circunvalación #890, Potosí', 'telefono' => '26554433', 'email' => 'ventas@bebidasenergeticas.bo'],
];

// ─── 4. PROCESS EACH PRODUCT ───

$selected = [];
$downloaded = 0;
$usedCategories = [];

foreach ($allProducts as $p) {
    $catSlug = $p['category'] ?? 'others';
    // Groceries van todas a "Despensa"
    $catName = ($catSlug === 'groceries')
        ? 'Despensa'
        : ($categoryTranslation[$catSlug] ?? $catSlug);
    $usedCategories[$catName] = $catName;

    // Código único: 3 letras del nombre + ID
    $cleanName = preg_replace('/[^a-zA-Z0-9 ]/', '', $p['title']);
    $prefix = strtoupper(substr(preg_replace('/[^a-zA-Z]/', '', $cleanName), 0, 3));
    if (strlen($prefix) < 2) $prefix = 'PRD';
    $code = $prefix . str_pad($p['id'], 3, '0', STR_PAD_LEFT);

    // Descargar thumbnail
    $thumbUrl = $p['thumbnail'] ?? '';
    $filename = "prod_{$code}.webp";
    $filepath = "$outputDir/$filename";

    if (!file_exists($filepath)) {
        $ch = curl_init($thumbUrl);
        $fp = fopen($filepath, 'wb');
        curl_setopt($ch, CURLOPT_FILE, $fp);
        curl_setopt($ch, CURLOPT_TIMEOUT, 15);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_USERAGENT, 'RedMarket/1.0');
        curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        fclose($fp);

        if ($httpCode !== 200 || filesize($filepath) < 100) {
            echo "  ERROR: {$p['title']} (HTTP $httpCode)\n";
            @unlink($filepath);
            continue;
        }
        $downloaded++;
    }

    // Precios BOB
    $precioUsd = $p['price'] ?? 5;
    $precioVenta = round($precioUsd * 7.5, 2);
    $precioCompra = round($precioUsd * 5.5, 2);
    $discount = $p['discountPercentage'] ?? 0;
    $enDescuento = $discount > 8;
    $precioOferta = $enDescuento ? round($precioVenta * (1 - $discount / 100), 2) : null;
    $stock = min($p['stock'] ?? 50, 200);

    $brand = $p['brand'] ?? 'Distribuidora Mayorista';
    $proveedorKey = sanitizeKey($brand);
    if (!isset($bolivianProveedores[$proveedorKey])) {
        $bolivianProveedores[$proveedorKey] = [
            'nombre' => "Importadora " . ucwords(str_replace('_', ' ', $proveedorKey)) . " Bolivia S.R.L.",
            'contacto' => 'Contacto RedMarket',
            'direccion' => 'Calle Comercio #100, Potosí',
            'telefono' => '26220000',
            'email' => str_replace('_', '', $proveedorKey) . '@proveedor.bo',
        ];
    }

    // Perecedero: solo Despensa (alimentos)
    $esPerecedero = $catName === 'Despensa';

    $selected[] = [
        'codigo' => $code,
        'nombre' => $p['title'],
        'descripcion' => $p['description'] ?? '',
        'categoria' => $catName,
        'precio_compra' => $precioCompra,
        'precio_venta' => $precioVenta,
        'precio_oferta' => $precioOferta,
        'en_descuento' => $enDescuento,
        'stock_actual' => $stock,
        'proveedor_key' => $proveedorKey,
        'imagen' => $filename,
        'es_perecedero' => $esPerecedero,
        'pasillo' => rand(1, 9),
        'nivel' => chr(rand(65, 67)),
        'unidad_medida' => match(true) {
            $catSlug === 'groceries' && stripos($p['title'], 'oil') !== false => 'lt',
            $catSlug === 'groceries' && stripos($p['title'], 'milk') !== false => 'lt',
            $catSlug === 'groceries' && stripos($p['title'], 'water') !== false => 'lt',
            $catSlug === 'groceries' && stripos($p['title'], 'juice') !== false => 'lt',
            $catSlug === 'groceries' && stripos($p['title'], 'coffee') !== false => 'un',
            $catSlug === 'groceries' && stripos($p['title'], 'meat') !== false => 'kg',
            $catSlug === 'groceries' && stripos($p['title'], 'chicken') !== false => 'kg',
            $catSlug === 'groceries' && stripos($p['title'], 'fish') !== false => 'kg',
            $catSlug === 'groceries' && stripos($p['title'], 'eggs') !== false => 'un',
            $catSlug === 'groceries' && stripos($p['title'], 'rice') !== false => 'kg',
            $catSlug === 'groceries' && stripos($p['title'], 'potato') !== false => 'kg',
            $catSlug === 'groceries' && stripos($p['title'], 'onion') !== false => 'kg',
            $catSlug === 'groceries' && stripos($p['title'], 'apple') !== false => 'kg',
            $catSlug === 'groceries' && stripos($p['title'], 'fruit') !== false => 'kg',
            $catSlug === 'groceries' && stripos($p['title'], 'vegetable') !== false => 'kg',
            default => 'un',
        },
    ];
}

function sanitizeKey(string $name): string {
    $key = strtolower(trim($name));
    $key = preg_replace('/[^a-z0-9]+/', '_', $key);
    $key = preg_replace(['/^_+|_+$/', '/&/'], ['', 'and'], $key);
    $replacements = [
        "s_" => "_",
        "inc" => "",
        "ltd" => "",
        "corp" => "",
        "_llc" => "",
    ];
    foreach ($replacements as $from => $to) {
        $key = str_replace($from, $to, $key);
    }
    return trim($key, '_') ?: 'distribuidora_mayorista';
}

echo "\n=== DESCARGA COMPLETADA ===\n";
echo "Productos: " . count($selected) . "\n";
echo "Imágenes descargadas: $downloaded\n";
echo "Categorías: " . count($usedCategories) . "\n";
foreach ($usedCategories as $name) {
    $count = count(array_filter($selected, fn($s) => $s['categoria'] === $name));
    echo "  $name: $count\n";
}
echo "Proveedores: " . count($bolivianProveedores) . "\n";

echo "\n¿Generar seeder? (s/n): ";
$input = trim(fgets(STDIN));
if (strtolower($input) !== 's') {
    echo "Cancelado.\n";
    exit;
}

// ─── 5. GENERAR SEEDER ───

// Orden alfabético case-insensitive de categorías
$sortedCats = array_keys($usedCategories);
usort($sortedCats, 'strcasecmp');

// Código de proveedores
$provCode = "        \$proveedores = [\n";
foreach ($bolivianProveedores as $key => $prov) {
    $safeName = addslashes($prov['nombre']);
    $safeContacto = addslashes($prov['contacto']);
    $safeDir = addslashes($prov['direccion']);
    $provCode .= "            '$key' => Proveedor::firstOrCreate(\n";
    $provCode .= "                ['nombre' => '$safeName'],\n";
    $provCode .= "                ['contacto' => '$safeContacto', 'direccion' => '$safeDir', 'telefono' => '{$prov['telefono']}', 'email' => '{$prov['email']}']\n";
    $provCode .= "            ),\n";
}
$provCode .= "        ];\n";
$provCode .= "        return \$proveedores;\n";

// Código de categorías
$catCode = "        \$nombres = [\n";
foreach ($sortedCats as $name) {
    $key = strtolower(str_replace([' ', 'í', 'é', 'á', 'ó', 'ú'], ['_', 'i', 'e', 'a', 'o', 'u'], $name));
    $catCode .= "            '$key' => '$name',\n";
}
$catCode .= "        ];\n";

// Código de productos
$prodCode = "        return [\n";
$currentCat = '';
foreach ($selected as $p) {
    if ($p['categoria'] !== $currentCat) {
        $currentCat = $p['categoria'];
        $prodCode .= "\n            // {$p['categoria']}\n";
    }

    $nombre = addslashes($p['nombre']);
    $desc = addslashes($p['descripcion']);
    $desc = str_replace(["\n", "\r", '  '], ' ', $desc);

    $catKey = strtolower(str_replace([' ', 'í', 'é', 'á', 'ó', 'ú'], ['_', 'i', 'e', 'a', 'o', 'u'], $p['categoria']));

    $extra = '';
    if ($p['en_descuento']) {
        $extra .= "'en_descuento' => true, 'precio_oferta' => {$p['precio_oferta']}, ";
    }
    if ($p['es_perecedero']) {
        $extra .= "'es_perecedero' => true, 'fecha_vencimiento' => '2026-08-15', ";
    }

    $prodCode .= "            ['nombre' => '$nombre', 'categoria' => '$catKey', 'precio_compra' => {$p['precio_compra']}, 'precio_venta' => {$p['precio_venta']}, 'stock_actual' => {$p['stock_actual']}, 'codigo_barras' => '{$p['codigo']}', 'pasillo' => '{$p['pasillo']}', 'nivel' => '{$p['nivel']}', 'unidad_medida' => '{$p['unidad_medida']}', 'proveedor' => '{$p['proveedor_key']}', 'descripcion' => '$desc', {$extra}],\n";
}
$prodCode .= "        ];\n";

// Plantilla del seeder
$phpCode = <<<PHP
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
        \$proveedores = \$this->createProveedores();
        \$categorias = \$this->createCategorias();
        \$productos = \$this->getProductosData();

        foreach (\$productos as \$data) {
            \$catKey = \$data['categoria'];
            \$catId = \$categorias[\$catKey]->id;
            \$imagen = 'prod_' . \$data['codigo_barras'] . '.webp';

            \$producto = Producto::firstOrCreate(
                ['codigo_barras' => \$data['codigo_barras']],
                [
                    'nombre' => \$data['nombre'],
                    'descripcion' => \$data['descripcion'],
                    'categoria_id' => \$catId,
                    'precio_compra' => \$data['precio_compra'],
                    'precio_venta' => \$data['precio_venta'],
                    'precio_oferta' => \$data['precio_oferta'] ?? null,
                    'en_descuento' => \$data['en_descuento'] ?? false,
                    'stock_actual' => \$data['stock_actual'],
                    'stock_minimo' => \$data['stock_minimo'] ?? 10,
                    'pasillo' => \$data['pasillo'],
                    'nivel' => \$data['nivel'],
                    'unidad_medida' => \$data['unidad_medida'],
                    'imagen_url' => \$imagen,
                    'es_perecedero' => \$data['es_perecedero'] ?? false,
                    'fecha_vencimiento' => \$data['fecha_vencimiento'] ?? null,
                    'lote' => \$data['lote'] ?? null,
                    'fecha_ingreso' => \$data['fecha_ingreso'] ?? null,
                ]
            );

            if (\$producto->imagen_url !== \$imagen) {
                \$producto->update(['imagen_url' => \$imagen]);
            }

            \$provKey = \$data['proveedor'];
            \$provId = \$proveedores[\$provKey]->id;
            if (!\$producto->proveedores()->where('proveedor_id', \$provId)->exists()) {
                \$producto->proveedores()->attach(\$provId, [
                    'precio_compra' => \$data['precio_compra'],
                    'es_principal' => true,
                ]);
            }
        }

        \$this->command->info(count(\$productos) . ' productos creados correctamente.');
    }

    protected function createProveedores(): array
    {
{$provCode}
    }

    protected function createCategorias(): array
    {
{$catCode}
        \$map = [];
        foreach (\$nombres as \$key => \$name) {
            \$map[\$key] = Categoria::firstOrCreate(['nombre' => \$name]);
        }
        return \$map;
    }

    protected function getProductosData(): array
    {
{$prodCode}
    }
}
PHP;

file_put_contents($seederPath, $phpCode);
echo "\n✓ Seeder generado: $seederPath\n";
echo "✓ Total: " . count($selected) . " productos\n";
echo "✓ Imágenes: $downloaded descargadas\n";
