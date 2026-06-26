<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class DownloadProductImages extends Command
{
    protected $signature = 'redmarket:download-images';
    protected $description = 'Descarga imagenes para productos del seeder usando DummyJSON + Picsum seeds';

    private array $dummyjsonMap = [
        'ABR001' => 'https://cdn.dummyjson.com/product-images/groceries/rice/1.webp',
        'ABR002' => 'https://cdn.dummyjson.com/product-images/groceries/cooking-oil/1.webp',
        'ABR008' => 'https://cdn.dummyjson.com/product-images/groceries/nescafe-coffee/1.webp',
        'BEB003' => 'https://cdn.dummyjson.com/product-images/groceries/water/1.webp',
        'BEB010' => 'https://cdn.dummyjson.com/product-images/groceries/juice/1.webp',
        'BEB015' => 'https://cdn.dummyjson.com/product-images/groceries/soft-drinks/1.webp',
        'LAC001' => 'https://cdn.dummyjson.com/product-images/groceries/milk/1.webp',
        'LAC005' => 'https://cdn.dummyjson.com/product-images/groceries/eggs/1.webp',
        'LAC010' => 'https://cdn.dummyjson.com/product-images/groceries/eggs/1.webp',
        'CAR002' => 'https://cdn.dummyjson.com/product-images/groceries/beef-steak/1.webp',
        'CAR003' => 'https://cdn.dummyjson.com/product-images/groceries/chicken-meat/1.webp',
        'CAR004' => 'https://cdn.dummyjson.com/product-images/groceries/chicken-meat/1.webp',
        'FRS001' => 'https://cdn.dummyjson.com/product-images/groceries/potatoes/1.webp',
        'FRS002' => 'https://cdn.dummyjson.com/product-images/groceries/red-onions/1.webp',
        'FRS005' => 'https://cdn.dummyjson.com/product-images/groceries/apple/1.webp',
        'FRS007' => 'https://cdn.dummyjson.com/product-images/groceries/lemon/1.webp',
        'FRS010' => 'https://cdn.dummyjson.com/product-images/groceries/green-chili-pepper/1.webp',
        'FRS013' => 'https://cdn.dummyjson.com/product-images/groceries/strawberry/1.webp',
        'FRS014' => 'https://cdn.dummyjson.com/product-images/groceries/green-bell-pepper/1.webp',
        'FRS019' => 'https://cdn.dummyjson.com/product-images/groceries/kiwi/1.webp',
    ];

    private array $picsumSeeds = [];

    public function handle(): int
    {
        $dir = base_path('IMGproductos/main');
        if (!is_dir($dir)) {
            mkdir($dir, 0775, true);
        }

        $codes = array_keys($this->getProductSearchTerms());
        $this->generateSeeds($codes);

        $downloaded = 0;
        $failed = 0;

        foreach ($codes as $code) {
            $filename = "prod_{$code}.jpg";
            $filepath = "{$dir}/{$filename}";

            if (file_exists($filepath) && filesize($filepath) > 1000) {
                $this->line("  [SKIP] {$filename} ya existe");
                continue;
            }

            $imageUrl = null;

            if (isset($this->dummyjsonMap[$code])) {
                $imageUrl = $this->dummyjsonMap[$code];
            } else {
                $seed = $this->picsumSeeds[$code] ?? mt_rand(1, 1000);
                $imageUrl = "https://picsum.photos/seed/{$code}/400/400";
            }

            $raw = @file_get_contents($imageUrl, false, stream_context_create([
                'http' => ['timeout' => 15, 'user_agent' => 'RedMarketBot/1.0'],
                'ssl'  => ['verify_peer' => false, 'verify_peer_name' => false],
            ]));

            if (!$raw || strlen($raw) < 500) {
                $this->warn("  [FAIL] {$code}");
                $failed++;
                continue;
            }

            file_put_contents($filepath, $raw);
            $downloaded++;
            $this->line("  [OK] {$filename} (" . strlen($raw) . " bytes)");
            usleep(150000);
        }

        $this->info("Completado: {$downloaded} descargadas, {$failed} fallos, " . count($codes) . " total");
        return 0;
    }

    private function generateSeeds(array $codes): void
    {
        foreach ($codes as $i => $code) {
            $hash = crc32($code);
            $this->picsumSeeds[$code] = ($hash % 1000) + 1;
        }
    }

    private function getProductSearchTerms(): array
    {
        return [
            'ABR001' => 'rice', 'ABR002' => 'oil', 'ABR003' => 'sugar',
            'ABR004' => 'pasta', 'ABR005' => 'flour', 'ABR006' => 'salt',
            'ABR007' => 'cookies', 'ABR008' => 'coffee', 'ABR009' => 'milk',
            'ABR010' => 'jam', 'ABR011' => 'tuna', 'ABR012' => 'sardines',
            'ABR013' => 'mayonnaise', 'ABR014' => 'mustard', 'ABR015' => 'vinegar',
            'BEB001' => 'cola', 'BEB002' => 'soda', 'BEB003' => 'water',
            'BEB004' => 'juice', 'BEB005' => 'beer', 'BEB006' => 'beer',
            'BEB007' => 'sprite', 'BEB008' => 'fanta', 'BEB009' => 'sparkling',
            'BEB010' => 'juice', 'BEB011' => 'energy', 'BEB012' => 'nectar',
            'BEB013' => 'water', 'BEB014' => 'malt', 'BEB015' => 'soda',
            'LAC001' => 'milk', 'LAC002' => 'milk', 'LAC003' => 'yogurt',
            'LAC004' => 'yogurt', 'LAC005' => 'cheese', 'LAC006' => 'mozzarella',
            'LAC007' => 'butter', 'LAC008' => 'margarine', 'LAC009' => 'cream',
            'LAC010' => 'eggs', 'LAC011' => 'milk', 'LAC012' => 'cream',
            'LAC013' => 'chocolate', 'LAC014' => 'yogurt', 'LAC015' => 'dulce',
            'LIM001' => 'detergent', 'LIM002' => 'detergent', 'LIM003' => 'bleach',
            'LIM004' => 'soap', 'LIM005' => 'dishwash', 'LIM006' => 'disinfectant',
            'LIM007' => 'soap', 'LIM008' => 'paper', 'LIM009' => 'paper',
            'LIM010' => 'cleaning', 'LIM011' => 'polish', 'LIM012' => 'freshener',
            'LIM013' => 'sponge', 'LIM014' => 'bags', 'LIM015' => 'wax',
            'PAN001' => 'bread', 'PAN002' => 'bread', 'PAN003' => 'cookies',
            'PAN004' => 'crackers', 'PAN005' => 'biscuit', 'PAN006' => 'tortilla',
            'PAN007' => 'panettone', 'PAN008' => 'roll', 'PAN009' => 'cookies',
            'PAN010' => 'cookies', 'PAN011' => 'bread', 'PAN012' => 'croissant',
            'PAN013' => 'muffin', 'PAN014' => 'bun', 'PAN015' => 'donut',
            'CAR001' => 'beef', 'CAR002' => 'steak', 'CAR003' => 'chicken',
            'CAR004' => 'chicken', 'CAR005' => 'sausage', 'CAR006' => 'ham',
            'CAR007' => 'sausage', 'CAR008' => 'pork', 'CAR009' => 'chicken',
            'CAR010' => 'hamburger', 'CAR011' => 'bacon', 'CAR012' => 'salami',
            'CAR013' => 'chicken', 'CAR014' => 'mortadella', 'CAR015' => 'ribs',
            'CAR016' => 'lamb', 'CAR017' => 'pork', 'CAR018' => 'pork',
            'CAR019' => 'pork', 'CAR020' => 'sausage',
            'FRS001' => 'potato', 'FRS002' => 'onion', 'FRS003' => 'tomato',
            'FRS004' => 'banana', 'FRS005' => 'apple', 'FRS006' => 'orange',
            'FRS007' => 'lemon', 'FRS008' => 'carrot', 'FRS009' => 'lettuce',
            'FRS010' => 'chili', 'FRS011' => 'peach', 'FRS012' => 'grapes',
            'FRS013' => 'strawberry', 'FRS014' => 'pepper', 'FRS015' => 'garlic',
            'FRS016' => 'pear', 'FRS017' => 'watermelon', 'FRS018' => 'pineapple',
            'FRS019' => 'mango', 'FRS020' => 'beans',
            'SNK001' => 'chips', 'SNK002' => 'chips', 'SNK003' => 'cheese',
            'SNK004' => 'peanuts', 'SNK005' => 'chocolate', 'SNK006' => 'candy',
            'SNK007' => 'gum', 'SNK008' => 'popcorn', 'SNK009' => 'wafer',
            'SNK010' => 'chocolate', 'SNK011' => 'peanuts', 'SNK012' => 'chips',
            'SNK013' => 'alfajor', 'SNK014' => 'gummy', 'SNK015' => 'chocolate',
            'SNK016' => 'nuts', 'SNK017' => 'cookies', 'SNK018' => 'cereal',
            'SNK019' => 'cheese', 'SNK020' => 'quinoa',
        ];
    }
}
