<?php

namespace App\Http\Controllers;

use App\Http\Resources\ProductoResource;
use App\Models\Producto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProductoController extends Controller
{
    public function index(Request $request)
    {
        $query = Producto::with('categoria');

        if ($s = $request->input('search')) {
            $query->where(function ($q) use ($s) {
                $q->where('nombre', 'like', "%{$s}%")
                  ->orWhere('codigo_barras', 'like', "%{$s}%");
            });
        }

        if ($request->boolean('descuento')) {
            $query->where('en_descuento', true)->whereNotNull('precio_oferta');
        }

        if ($catId = $request->input('categoria')) {
            $query->where('categoria_id', $catId);
        }

        if ($request->boolean('random')) {
            $query->inRandomOrder();
        }

        if ($ids = $request->input('ids')) {
            $idsArray = explode(',', $ids);
            $idsArray = array_filter($idsArray, 'is_numeric');
            if (!empty($idsArray)) {
                $query->whereIn('id', $idsArray);
            }
        }

        $perPage = min((int) $request->input('per_page', 20), 100);

        return ProductoResource::collection($query->paginate($perPage));
    }

    public function store(Request $request)
    {
        $this->authorize('create', Producto::class);
        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'codigo_barras' => 'required|string|unique:productos',
            'precio_compra' => 'required|numeric|min:0',
            'precio_venta' => 'required|numeric|min:0',
            'precio_oferta' => 'nullable|numeric|min:0',
            'en_descuento' => 'boolean',
            'stock_actual' => 'required|integer|min:0',
            'stock_minimo' => 'required|integer|min:0',
            'pasillo' => 'required|string|max:50',
            'nivel' => 'required|string|max:50',
            'unidad_medida' => 'required|in:un,kg,lt,gr',
            'es_perecedero' => 'boolean',
            'fecha_vencimiento' => 'nullable|date',
            'proveedor_id' => 'required|exists:proveedores,id',
            'categoria_id' => 'required|exists:categorias,id',
            'imagen' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
        ]);

        $proveedorId = $validated['proveedor_id'];
        unset($validated['proveedor_id']);

        $producto = Producto::create($validated);

        $producto->proveedores()->attach($proveedorId, [
            'precio_compra' => $validated['precio_compra'],
            'es_principal' => true
        ]);

        $filename = $this->processUploadedImage($request);
        if ($filename) {
            $producto->update(['imagen_url' => $filename]);
        }

        return new ProductoResource($producto->load('categoria'));
    }

    public function show(Producto $producto)
    {
        return new ProductoResource(
            $producto->load('categoria')
        );
    }

    public function update(Request $request, Producto $producto)
    {
        $this->authorize('update', $producto);
        $validated = $request->validate([
            'nombre' => 'string|max:255',
            'descripcion' => 'nullable|string',
            'codigo_barras' => 'string|unique:productos,codigo_barras,' . $producto->id,
            'precio_compra' => 'numeric|min:0',
            'precio_venta' => 'numeric|min:0',
            'precio_oferta' => 'nullable|numeric|min:0',
            'en_descuento' => 'boolean',
            'stock_actual' => 'integer|min:0',
            'stock_minimo' => 'integer|min:0',
            'pasillo' => 'string|max:50',
            'nivel' => 'string|max:50',
            'unidad_medida' => 'in:un,kg,lt,gr',
            'es_perecedero' => 'boolean',
            'fecha_vencimiento' => 'nullable|date',
            'categoria_id' => 'exists:categorias,id',
            'imagen' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
        ]);

        $producto->update($validated);

        $filename = $this->processUploadedImage($request);
        if ($filename) {
            if ($producto->imagen_url) {
                $old = basename($producto->imagen_url);
                Storage::disk('root_images')->delete('main/' . $old);
                Storage::disk('root_images')->delete('thumbs/' . $old);
            }
            $producto->update(['imagen_url' => $filename]);
        }

        return new ProductoResource($producto->load('categoria'));
    }

    protected function processUploadedImage(Request $request): ?string
    {
        if (!$request->hasFile('imagen')) {
            return null;
        }

        $image = $request->file('imagen');

        // GD disponible → convertir a WebP con resize
        if (extension_loaded('gd')) {
            $src = @imagecreatefromstring($image->get());
            if ($src) {
                $filename = time() . '_' . uniqid() . '.webp';
                $w = imagesx($src);
                $h = imagesy($src);

                if ($w > 800) {
                    $mainH = (int) round(800 * $h / $w);
                    $main = imagescale($src, 800, $mainH);
                } else {
                    $main = $src;
                }
                ob_start();
                imagewebp($main, null, 80);
                Storage::disk('root_images')->put('main/' . $filename, ob_get_clean());

                if ($w > 200) {
                    $thumbH = (int) round(200 * $h / $w);
                    $thumb = imagescale($src, 200, $thumbH);
                } else {
                    $thumb = $src;
                }
                ob_start();
                imagewebp($thumb, null, 70);
                Storage::disk('root_images')->put('thumbs/' . $filename, ob_get_clean());

                imagedestroy($src);
                if (isset($main) && $main !== $src) imagedestroy($main);
                if (isset($thumb) && $thumb !== $src) imagedestroy($thumb);

                return $filename;
            }
        }

        // Fallback sin GD: guardar original tal cual
        $ext = $image->getClientOriginalExtension() ?: 'jpg';
        $filename = time() . '_' . uniqid() . '.' . $ext;
        $raw = $image->get();
        Storage::disk('root_images')->put('main/' . $filename, $raw);
        Storage::disk('root_images')->put('thumbs/' . $filename, $raw);

        return $filename;
    }

    public function destroy(Producto $producto)
    {
        $this->authorize('delete', $producto);
        if ($producto->imagen_url) {
            $old = basename($producto->imagen_url);
            Storage::disk('root_images')->delete('main/' . $old);
            Storage::disk('root_images')->delete('thumbs/' . $old);
        }
        $producto->delete();
        return response()->noContent();
    }
}
