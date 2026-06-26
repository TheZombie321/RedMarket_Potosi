<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pedidos', function (Blueprint $table) {
            $table->index('user_id');
            $table->index('estado');
            $table->index('payment_status');
        });

        Schema::table('pedido_items', function (Blueprint $table) {
            $table->index('pedido_id');
        });

        Schema::table('productos', function (Blueprint $table) {
            $table->index('categoria_id');
        });
    }

    public function down(): void
    {
        Schema::table('pedidos', function (Blueprint $table) {
            $table->dropIndex(['user_id']);
            $table->dropIndex(['estado']);
            $table->dropIndex(['payment_status']);
        });

        Schema::table('pedido_items', function (Blueprint $table) {
            $table->dropIndex(['pedido_id']);
        });

        Schema::table('productos', function (Blueprint $table) {
            $table->dropIndex(['categoria_id']);
        });
    }
};
