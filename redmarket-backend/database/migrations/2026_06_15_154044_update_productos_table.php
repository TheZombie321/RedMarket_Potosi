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
        Schema::table('productos', function (Blueprint $table) {
            $table->softDeletes();
            $table->decimal('precio_oferta', 10, 2)->nullable();
            $table->boolean('en_descuento')->default(false);
            $table->dropColumn('activo');
            $table->index(['pasillo', 'nivel']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('productos', function (Blueprint $table) {
            $table->dropSoftDeletes();
            $table->dropColumn(['precio_oferta', 'en_descuento']);
            $table->boolean('activo')->default(true);
            $table->dropIndex(['pasillo', 'nivel']);
        });
    }
};
