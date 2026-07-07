<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('productos', function (Blueprint $table) {
            $table->string('lote', 50)->nullable()->after('codigo_barras');
            $table->date('fecha_ingreso')->nullable()->after('fecha_vencimiento');
        });
    }

    public function down(): void
    {
        Schema::table('productos', function (Blueprint $table) {
            $table->dropColumn(['lote', 'fecha_ingreso']);
        });
    }
};
