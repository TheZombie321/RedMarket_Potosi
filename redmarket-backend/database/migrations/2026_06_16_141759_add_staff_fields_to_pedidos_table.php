<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pedidos', function (Blueprint $table) {
            $table->foreignId('picking_user_id')->nullable()->constrained('users')->nullOnDelete()->after('user_id');
            $table->foreignId('repartidor_id')->nullable()->constrained('users')->nullOnDelete()->after('picking_user_id');
        });
    }

    public function down(): void
    {
        Schema::table('pedidos', function (Blueprint $table) {
            $table->dropForeign(['picking_user_id']);
            $table->dropForeign(['repartidor_id']);
            $table->dropColumn(['picking_user_id', 'repartidor_id']);
        });
    }
};
