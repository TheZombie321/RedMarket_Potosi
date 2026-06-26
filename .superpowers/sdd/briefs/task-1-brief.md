# Task 1: Migration + Model changes

## Files
- **Create:** `redmarket-backend/database/migrations/2026_06_22_000001_add_payment_fields_to_pedidos_table.php`
- **Modify:** `redmarket-backend/app/Models/Pedido.php`

## Steps

### Step 1: Create migration

`redmarket-backend/database/migrations/2026_06_22_000001_add_payment_fields_to_pedidos_table.php`:

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pedidos', function (Blueprint $table) {
            $table->string('payment_method', 10)->default('cash');
            $table->string('payment_status', 10)->default('unpaid');
            $table->string('stripe_session_id')->nullable()->unique()->after('payment_status');
        });
    }

    public function down(): void
    {
        Schema::table('pedidos', function (Blueprint $table) {
            $table->dropColumn(['payment_method', 'payment_status', 'stripe_session_id']);
        });
    }
};
```

### Step 2: Add fields to Pedido model

In `redmarket-backend/app/Models/Pedido.php`, add to `$fillable`:
```php
'payment_method', 'payment_status', 'stripe_session_id',
```

In the `$casts` array:
```php
'payment_method' => 'string',
'payment_status' => 'string',
```

### Step 3: Run migration

```bash
/mnt/c/laragon/bin/php/php-8.3.30-Win32-vs16-x64/php.exe artisan migrate
```
