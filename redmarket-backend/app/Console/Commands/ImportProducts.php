<?php

namespace App\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use App\Models\Categoria;
use App\Models\Proveedor;
use App\Models\Producto;

#[Signature('redmarket:import-products')]
#[Description('Importa productos desde API externa y procesa imagenes a WebP local')]
class ImportProducts extends Command
{
    protected const API_URL = 'https://fakestoreapi.com/products';
    protected const MAIN_W = 800;
    protected const THUMB_W = 200;

    public function handle()
    {
        $this->info('Iniciando importación desde ' . self::API_URL . '...');

        $prov = Proveedor::first();
        if (!$prov) {
            $this->error('Debes tener al menos un proveedor. Ejecuta primero: php artisan db:seed --class=RoleSeeder');
            return;
        }

        $response = @file_get_contents(self::API_URL);
        if (!$response) {
            $this->error('No se pudo conectar con la API externa.');
            return;
        }

        $products = json_decode($response, true);
        if (empty($products)) {
            $this->error('La API devolvió datos vacíos.');
            return;
        }

        $this->info("Se encontraron " . count($products) . " productos.");

        $imported = 0;
        foreach ($products as $p) {
            $this->line("  → {$p['title']}");

            $cat = $this->getOrCreateCategory($p['category'] ?? 'General');

            $filename = $this->downloadAndProcessImage($p['image'] ?? null, $p['id']);
            if ($filename) {
                $this->line("     Imagen: {$filename}");
            }

            $precio = (float) ($p['price'] ?? 10);
            $precioCompra = round($precio * 0.7, 2);

            try {
                $prod = Producto::create([
                    'nombre'          => substr($p['title'], 0, 255),
                    'descripcion'     => substr($p['description'] ?? '', 0, 500),
                    'codigo_barras'   => 'EXT-' . $p['id'],
                    'precio_compra'   => $precioCompra,
                    'precio_venta'    => $precio,
                    'stock_actual'    => rand(10, 100),
                    'stock_minimo'    => 5,
                    'pasillo'         => 'A' . rand(1, 5),
                    'nivel'           => (string) rand(1, 3),
                    'unidad_medida'   => 'un',
                    'imagen_url'      => $filename,
                    'es_perecedero'   => false,
                    'categoria_id'    => $cat->id,
                ]);

                $prod->proveedores()->attach($prov->id, [
                    'precio_compra' => $precioCompra,
                    'es_principal'  => true,
                ]);

                $imported++;
            } catch (\Exception $e) {
                $this->warn("     Error: " . $e->getMessage());
            }
        }

        $this->info("Importación finalizada. {$imported} productos creados.");
    }

    protected function getOrCreateCategory(string $name): Categoria
    {
        $name = trim(ucfirst($name));
        $cat = Categoria::where('nombre', $name)->first();
        if (!$cat) {
            $cat = Categoria::create([
                'nombre'      => $name,
                'descripcion' => "Productos de {$name}",
            ]);
            $this->line("     Nueva categoría: {$name}");
        }
        return $cat;
    }

    protected function downloadAndProcessImage(?string $url, int $id): ?string
    {
        if (!$url) {
            return null;
        }

        $ext = strtolower(pathinfo(parse_url($url, PHP_URL_PATH), PATHINFO_EXTENSION));
        if (!in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
            $ext = 'jpg';
        }

        $raw = @file_get_contents($url, false, stream_context_create([
            'http' => ['timeout' => 15, 'user_agent' => 'RedMarketImport/1.0'],
            'ssl'  => ['verify_peer' => false, 'verify_peer_name' => false],
        ]));

        if (!$raw) {
            return null;
        }

        $src = @imagecreatefromstring($raw);
        if (!$src) {
            return null;
        }

        $filename = 'prod_' . $id . '_' . time() . '.webp';

        $w = imagesx($src);
        $h = imagesy($src);

        // Main 800px
        if ($w > self::MAIN_W) {
            $mainH = (int) round(self::MAIN_W * $h / $w);
            $main = imagescale($src, self::MAIN_W, $mainH);
        } else {
            $main = $src;
        }
        ob_start();
        imagewebp($main, null, 80);
        $mainData = ob_get_clean();
        Storage::disk('root_images')->put('main/' . $filename, $mainData);

        // Thumb 200px
        if ($w > self::THUMB_W) {
            $thumbH = (int) round(self::THUMB_W * $h / $w);
            $thumb = imagescale($src, self::THUMB_W, $thumbH);
        } else {
            $thumb = $src;
        }
        ob_start();
        imagewebp($thumb, null, 70);
        $thumbData = ob_get_clean();
        Storage::disk('root_images')->put('thumbs/' . $filename, $thumbData);

        imagedestroy($src);
        if (isset($main) && $main !== $src) imagedestroy($main);
        if (isset($thumb) && $thumb !== $src) imagedestroy($thumb);

        return $filename;
    }
}
