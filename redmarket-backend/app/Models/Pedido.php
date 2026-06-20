<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pedido extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'estado',
        'total_productos',
        'delivery_fee',
        'total_final',
        'direccion_texto',
        'latitud',
        'longitud',
        'picking_user_id',
        'repartidor_id',
        'ubicacion_actual',
    ];

    protected $table = 'pedidos';

    protected $casts = [
        'ubicacion_actual' => 'array',
    ];

    protected $appends = ['codigo'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function items()
    {
        return $this->hasMany(PedidoItem::class);
    }

    public function preparador()
    {
        return $this->belongsTo(User::class, 'picking_user_id');
    }

    public function repartidor()
    {
        return $this->belongsTo(User::class, 'repartidor_id');
    }

    protected function codigo(): Attribute
    {
        return Attribute::make(
            get: fn() => 'RM-' . str_pad($this->id, 4, '0', STR_PAD_LEFT),
        );
    }
}
