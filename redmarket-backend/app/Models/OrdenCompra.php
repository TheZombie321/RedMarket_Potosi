<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrdenCompra extends Model
{
    use HasFactory;

    protected $table = 'ordenes_compra';

    protected $fillable = [
        'codigo',
        'proveedor_id',
        'user_id',
        'estado',
        'notas',
        'total',
    ];

    protected $casts = [
        'total' => 'decimal:2',
        'estado' => 'string',
    ];

    protected $appends = ['codigo_formateado'];

    protected function codigoFormateado(): Attribute
    {
        return Attribute::make(
            get: fn() => 'OC-' . str_pad($this->id, 4, '0', STR_PAD_LEFT),
        );
    }

    public function proveedor()
    {
        return $this->belongsTo(Proveedor::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function items()
    {
        return $this->hasMany(OrdenCompraItem::class);
    }

    public function movimientos()
    {
        return $this->morphMany(StockMovement::class, 'referencia');
    }
}
