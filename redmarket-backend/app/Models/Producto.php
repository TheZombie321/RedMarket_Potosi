<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Producto extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'nombre',
        'descripcion',
        'codigo_barras',
        'precio_compra',
        'precio_venta',
        'precio_oferta',
        'en_descuento',
        'stock_actual',
        'stock_minimo',
        'pasillo',
        'nivel',
        'unidad_medida',
        'imagen_url',
        'es_perecedero',
        'fecha_vencimiento',
        'categoria_id',
    ];

    protected $table = 'productos';

    protected function imagenUrl(): Attribute
    {
        return Attribute::make(
            get: fn(?string $value) => $value
                ? url("img/productos/main/{$value}")
                : null,
        );
    }

    public function proveedores()
    {
        return $this->belongsToMany(Proveedor::class, 'producto_proveedor')
                    ->withPivot('precio_compra', 'es_principal')
                    ->withTimestamps();
    }

    public function categoria()
    {
        return $this->belongsTo(Categoria::class);
    }

    public function resenas()
    {
        return $this->hasMany(Resena::class);
    }
}
