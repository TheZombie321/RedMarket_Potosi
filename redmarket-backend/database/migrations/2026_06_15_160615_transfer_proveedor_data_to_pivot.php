<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $productos = DB::table('productos')->get();
        foreach ($productos as $producto) {
            if ($producto->proveedor_id) {
                DB::table('producto_proveedor')->insert([
                    'producto_id' => $producto->id,
                    'proveedor_id' => $producto->proveedor_id,
                    'precio_compra' => $producto->precio_compra, // Using current price as initial
                    'es_principal' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('producto_proveedor')->truncate();
    }
};
