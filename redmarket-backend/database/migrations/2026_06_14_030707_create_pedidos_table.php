<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('pedidos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained();
            $table->enum('estado', ['pendiente', 'en_preparacion', 'listo_despacho', 'en_camino', 'entregado', 'cancelado'])->default('pendiente');
            $table->decimal('total_productos', 10, 2);
            $table->decimal('delivery_fee', 10, 2);
            $table->decimal('total_final', 10, 2);
            $table->string('direccion_texto');
            $table->decimal('latitud', 10, 8);
            $table->decimal('longitud', 11, 8);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pedidos');
    }
};
