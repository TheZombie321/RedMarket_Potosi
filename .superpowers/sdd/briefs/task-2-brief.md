# Task 2: Stripe SDK + Configuration

## Files
- Modify: `redmarket-backend/composer.json`
- Modify: `redmarket-backend/.env`
- Create: `redmarket-backend/config/stripe.php`
- Modify: `redmarket-backend/config/app.php`
- Modify/Create: `RedMarket/.env` (may not exist)

## Steps

### Step 1: Install Stripe PHP SDK

```bash
cd /mnt/e/CARRERA\ INFORMATICA/Taller\ de\ Software/RedMarket/redmarket-backend
/mnt/c/laragon/bin/php/php-8.3.30-Win32-vs16-x64/php.exe composer.phar require stripe/stripe-php:^16.0
```

### Step 2: Add Stripe env vars to backend `.env`

Append to `redmarket-backend/.env`:
```
STRIPE_KEY=pk_test_51...
STRIPE_SECRET=sk_test_51...
STRIPE_WEBHOOK_SECRET=whsec_...
FRONTEND_URL=https://unique-reflection-production-59c9.up.railway.app
```

### Step 3: Create `config/stripe.php`

```php
<?php

return [
    'key' => env('STRIPE_KEY'),
    'secret' => env('STRIPE_SECRET'),
    'webhook_secret' => env('STRIPE_WEBHOOK_SECRET'),
];
```

### Step 4: Add `frontend_url` to `config/app.php`

After `'url' => env('APP_URL', 'http://localhost'),` add:
```php
'frontend_url' => env('FRONTEND_URL', 'http://localhost:5173'),
```

### Step 5: Add Stripe key to frontend `.env`

In `RedMarket/.env` (create if not exists):
```
VITE_STRIPE_PUBLISHABLE_KEY=pk_test_51...
```
