<?php

use App\Models\Producto;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        $images = [
            'AR001' => 'prod_1_1781571715.webp',
            'AC001' => 'prod_2_1781571718.webp',
            'AZ001' => 'prod_3_1781571720.webp',
            'FD001' => 'prod_4_1781571721.webp',
            'HA001' => 'prod_5_1781571723.webp',
            'CC001' => 'prod_6_1781571724.webp',
            'PE001' => 'prod_7_1781571726.webp',
            'AG001' => 'prod_8_1781571728.webp',
            'JU001' => 'prod_9_1781571729.webp',
            'LC001' => 'prod_10_1781571731.webp',
            'YG001' => 'prod_11_1781571733.webp',
            'QS001' => 'prod_12_1781571734.webp',
            'MT001' => 'prod_13_1781571736.webp',
            'DT001' => 'prod_14_1781571739.webp',
            'CL001' => 'prod_15_1781571740.webp',
            'JB001' => 'prod_16_1781571742.webp',
            'LV001' => 'prod_17_1781571743.webp',
            'GL001' => 'prod_18_1781571744.webp',
            'CF001' => 'prod_19_1781571745.webp',
        ];

        foreach ($images as $codigo => $filename) {
            Producto::where('codigo_barras', $codigo)
                ->whereNull('imagen_url')
                ->update(['imagen_url' => $filename]);
        }
    }

    public function down(): void
    {
        // No reversible — data backfill
    }
};
