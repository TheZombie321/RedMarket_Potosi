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
        Schema::create('productos', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->text('descripcion')->nullable();
            $table->string('codigo_barras')->unique();
            $table->decimal('precio_compra', 10, 2);
            $table->decimal('precio_venta', 10, 2);
            $table->integer('stock_actual')->default(0);
            $table->integer('stock_minimo')->default(5);
            $table->string('pasillo');
            $table->string('nivel');
            $table->string('unidad_medida'); // kg, lt, un
            $table->string('imagen_url')->nullable();
            $table->boolean('es_perecedero')->default(false);
            $table->date('fecha_vencimiento')->nullable();
            $table->foreignId('proveedor_id')->constrained('proveedores');
            $table->foreignId('categoria_id')->constrained('categorias');
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('productos');
    }
};
