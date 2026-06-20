<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Categoria extends Model
{
    use HasFactory;

    protected $fillable = [
        'nombre',
        'descripcion',
        'parent_id',
    ];

    protected $table = 'categorias';

    public function productos()
    {
        return $this->hasMany(Producto::class);
    }

    public function subcategorias()
    {
        return $this->hasMany(Categoria::class, 'parent_id');
    }

    public function parent()
    {
        return $this->belongsTo(Categoria::class, 'parent_id');
    }
}
