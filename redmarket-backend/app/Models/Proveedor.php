<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Proveedor extends Model
{
    use HasFactory;

    protected $fillable = [
        'nombre',
        'contacto',
        'telefono',
        'email',
        'direccion',
    ];

    protected $table = 'proveedores';

    public function productos()
    {
        return $this->belongsToMany(Producto::class, 'producto_proveedor')
                    ->withPivot('precio_compra', 'es_principal')
                    ->withTimestamps();
    }
}
