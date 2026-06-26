<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Shift extends Model
{
    use HasFactory;

    protected $table = 'shifts';

    protected $fillable = [
        'user_id',
        'inicio',
        'fin',
        'estado',
    ];

    protected $casts = [
        'inicio' => 'datetime',
        'fin' => 'datetime',
        'estado' => 'string',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
