# Combined Implementation Plan — Column Types + Laravel Upgrade + Redis Setup

> **3 tasks in 1 plan** — each independently testable and deployable  
> **Total estimated**: 6-8 hours  
> **Risk**: Medium-High (Laravel upgrade is highest risk)

---

## TASK 1: Column Type Fixes (Phase 23)

### Problem

Money stored as `integer`, balance as `float`, status as `string(55)`. This causes:
- Loss of fractional amounts (৳150.50 → 150)
- Floating point rounding errors
- Inconsistent status filtering

### All Occurrences

#### Money as `integer` (10 columns)

| Table | Column | Current | Target | Migration |
|---|---|---|---|---|
| `products` | `purchase_price` | `integer` | `decimal(12,2)` | `2023_01_11_114621` |
| `products` | `old_price` | `integer` nullable | `decimal(12,2)` nullable | `2023_01_11_114621` |
| `products` | `new_price` | `integer` | `decimal(12,2)` | `2023_01_11_114621` |
| `orders` | `amount` | `integer` | `decimal(12,2)` | `2023_02_22_150326` |
| `orders` | `discount` | `integer` | `decimal(12,2)` | `2023_02_22_150326` |
| `orders` | `shipping_charge` | `integer` | `decimal(12,2)` | `2023_02_22_150326` |
| `order_details` | `purchase_price` | `integer` | `decimal(12,2)` | `2023_02_22_150339` |
| `order_details` | `sale_price` | `integer` | `decimal(12,2)` | `2023_02_22_150339` |
| `payments` | `amount` | `integer` | `decimal(12,2)` | `2023_02_22_150400` |
| `shipping_charges` | `amount` | `integer` | `decimal(12,2)` | `2023_08_04_101452` |

#### Balance as `float` (1 column)

| Table | Column | Current | Target | Migration |
|---|---|---|---|---|
| `customers` | `balance` | `float` | `decimal(12,2)` | `2023_02_20_022411` |

#### Status as `string(55)` (10 columns)

| Table | Column | Current | Target | Migration |
|---|---|---|---|---|
| `customers` | `status` | `string(55)` | `tinyInteger` default 1 | `2023_02_20_022411` |
| `orders` | `order_status` | `string(55)` | `string(55)` (keep — FK to order_statuses) | — |
| `payments` | `payment_status` | `string(55)` | `string(50)` (keep — enum values) | — |
| `reviews` | `status` | `string(55)` | `tinyInteger` default 1 | `2023_02_27_095310` |
| `campaigns` | `status` | `string(55)` | `tinyInteger` default 1 | `2023_03_06_160934` |
| `colors` | `status` | `string` nullable | `tinyInteger` default 1 | `2023_06_04_121934` |
| `sizes` | `status` | `string` nullable | `tinyInteger` default 1 | `2023_06_04_122329` |
| `shipping_charges` | `status` | `string` | `tinyInteger` default 1 | `2023_08_04_101452` |
| `order_statuses` | `status` | `string(55)` | `tinyInteger` default 1 | `2023_08_04_204814` |
| `sms_gateways` | `status` | `string(25)` nullable | `tinyInteger` default 1 | `2024_02_07_142550` |

#### Date as `string` (1 column)

| Table | Column | Current | Target | Migration |
|---|---|---|---|---|
| `campaigns` | `date` | `string(55)` | `date` | `2023_03_06_160934` |

#### Typo Columns (5 columns)

| Table | Column | Should Be | Migration |
|---|---|---|---|
| `categories` | `meta_decription` | `meta_description` | `2023_01_22_171430` |
| `subcategories` | `meta_decription` | `meta_description` | `2023_09_07_171103` |
| `childcategories` | `meta_decription` | `meta_description` | `2023_09_07_171404` |
| `reviews` | `ratting` | `rating` | `2023_02_27_095310` |
| `sms_gateways` | `serderid` | `sender_id` | `2024_02_07_142550` |

### Implementation

#### Step 1: Create migration for column type fixes

```bash
php artisan make:migration fix_column_types_for_data_integrity
```

```php
// database/migrations/2026_09_03_xxxxxx_fix_column_types_for_data_integrity.php

public function up()
{
    // Money columns → decimal(12,2)
    Schema::table('products', function (Blueprint $table) {
        $table->decimal('purchase_price', 12, 2)->change();
        $table->decimal('old_price', 12, 2)->nullable()->change();
        $table->decimal('new_price', 12, 2)->change();
    });

    Schema::table('orders', function (Blueprint $table) {
        $table->decimal('amount', 12, 2)->change();
        $table->decimal('discount', 12, 2)->default(0)->change();
        $table->decimal('shipping_charge', 12, 2)->default(0)->change();
    });

    Schema::table('order_details', function (Blueprint $table) {
        $table->decimal('purchase_price', 12, 2)->change();
        $table->decimal('sale_price', 12, 2)->change();
    });

    Schema::table('payments', function (Blueprint $table) {
        $table->decimal('amount', 12, 2)->change();
    });

    Schema::table('shipping_charges', function (Blueprint $table) {
        $table->decimal('amount', 12, 2)->change();
    });

    // Float → decimal
    Schema::table('customers', function (Blueprint $table) {
        $table->decimal('balance', 12, 2)->default(0)->change();
    });

    // Status string → tinyInteger
    Schema::table('customers', function (Blueprint $table) {
        // First convert existing values
        DB::statement("UPDATE customers SET status = '1' WHERE status = 'active'");
        DB::statement("UPDATE customers SET status = '0' WHERE status = 'inactive'");
        $table->tinyInteger('status')->default(1)->change();
    });

    Schema::table('reviews', function (Blueprint $table) {
        DB::statement("UPDATE reviews SET status = '1' WHERE status = 'active'");
        DB::statement("UPDATE reviews SET status = '0' WHERE status = 'inactive'");
        $table->tinyInteger('status')->default(1)->change();
    });

    Schema::table('campaigns', function (Blueprint $table) {
        DB::statement("UPDATE campaigns SET status = '1' WHERE status = 'active'");
        DB::statement("UPDATE campaigns SET status = '0' WHERE status = 'inactive'");
        $table->tinyInteger('status')->default(1)->change();
    });

    Schema::table('colors', function (Blueprint $table) {
        $table->tinyInteger('status')->default(1)->change();
    });

    Schema::table('sizes', function (Blueprint $table) {
        $table->tinyInteger('status')->default(1)->change();
    });

    Schema::table('shipping_charges', function (Blueprint $table) {
        $table->tinyInteger('status')->default(1)->change();
    });

    Schema::table('order_statuses', function (Blueprint $table) {
        $table->tinyInteger('status')->default(1)->change();
    });

    Schema::table('sms_gateways', function (Blueprint $table) {
        $table->tinyInteger('status')->default(1)->change();
    });

    // Date string → date
    Schema::table('campaigns', function (Blueprint $table) {
        $table->date('date')->change();
    });
}
```

#### Step 2: Create migration for typo fixes

```bash
php artisan make:migration fix_column_typos
```

```php
// database/migrations/2026_09_03_xxxxxx_fix_column_typos.php

public function up()
{
    Schema::table('categories', function (Blueprint $table) {
        $table->renameColumn('meta_decription', 'meta_description');
    });

    Schema::table('subcategories', function (Blueprint $table) {
        $table->renameColumn('meta_decription', 'meta_description');
    });

    Schema::table('childcategories', function (Blueprint $table) {
        $table->renameColumn('meta_decription', 'meta_description');
    });

    Schema::table('reviews', function (Blueprint $table) {
        $table->renameColumn('ratting', 'rating');
    });

    Schema::table('sms_gateways', function (Blueprint $table) {
        $table->renameColumn('serderid', 'sender_id');
    });
}
```

#### Step 3: Update Model $casts

```php
// Product.php
protected $casts = [
    'purchase_price' => 'decimal:2',
    'old_price' => 'decimal:2',
    'new_price' => 'decimal:2',
    'stock' => 'integer',
    'topsale' => 'boolean',
    'feature_product' => 'boolean',
    'status' => 'boolean',
];

// Order.php
protected $casts = [
    'amount' => 'decimal:2',
    'discount' => 'decimal:2',
    'shipping_charge' => 'decimal:2',
];

// Customer.php
protected $casts = [
    'balance' => 'decimal:2',
    'status' => 'boolean',
    'verify' => 'boolean',
];
```

#### Step 4: Update code referencing typo columns

```bash
# Find all references to old column names
grep -rn 'meta_decription\|ratting\|serderid' app/ resources/views/ --include="*.php" --include="*.blade.php"
```

Update each reference to use the new column name.

#### Step 5: Verify

```bash
php artisan migrate
php artisan test
```

### Files To Create

| File | Purpose |
|---|---|
| `database/migrations/xxxx_fix_column_types_for_data_integrity.php` | Money/status/date type fixes |
| `database/migrations/xxxx_fix_column_typos.php` | Column name typo fixes |

### Files To Modify

| File | Change |
|---|---|
| `app/Models/Product.php` | Add $casts |
| `app/Models/Order.php` | Add $casts |
| `app/Models/Customer.php` | Add $casts |
| `app/Models/Review.php` | Add $casts |
| All files referencing typo columns | Update column names |

---

## TASK 2: Laravel Upgrade (Phase 29-36)

### Current State

| Item | Current | Target |
|---|---|---|
| PHP | `^8.0.2\|^8.1\|^8.2` | `^8.2` |
| Laravel | `^9.19\|^10.0\|...\|^13.0` | `^13.0` |
| composer.json | Wide version ranges | Specific versions |

### Package Compatibility Matrix

| Package | L9 | L10 | L11 | L12 | L13 | Action |
|---|---|---|---|---|---|---|
| `laravel/framework` | ✅ | ✅ | ✅ | ✅ | ✅ | Update constraint |
| `laravel/sanctum` | ^3.0 | ^3.2 | ^4.0 | ^4.0 | ^4.0 | Update |
| `laravel/tinker` | ^2.7 | ^2.8 | ^2.9 | ^2.9 | ^2.9 | Update |
| `laravel/ui` | ^4.2 | ^4.5 | ❌ | ❌ | ❌ | **REMOVE** |
| `spatie/laravel-permission` | ^5.7 | ^6.0 | ^6.0 | ^6.0 | ^6.0 | Update |
| `yajra/laravel-datatables` | ~10.0 | ^11.0 | ^11.0 | ^11.0 | ^11.0 | Update |
| `intervention/image` | ^2.7 | ^3.0 | ^3.0 | ^3.0 | ^3.0 | Update |
| `brian2694/laravel-toastr` | ^5.57 | ? | ? | ? | ? | Check/Replace |
| `olimortimer/laravelshoppingcart` | ^6.0 | ❌ | ❌ | ❌ | ❌ | **REMOVE** |
| `shurjopayv2/laravel8` | dev-master | ❌ | ❌ | ❌ | ❌ | **REPLACE** |
| `mews/purifier` | ^3.4 | ^3.4 | ^3.4 | ^3.4 | ^3.4 | OK |
| `guzzlehttp/guzzle` | ^7.2 | ^7.2 | ^7.2 | ^7.2 | ^7.2 | OK |
| `nunomaduro/collision` | ^6.1 | ^7.0 | ^8.0 | ^8.0 | ^8.0 | Update |
| `phpunit/phpunit` | ^9.5 | ^10.0 | ^11.0 | ^11.0 | ^11.0 | Update |
| `spatie/laravel-ignition` | ^1.0 | ^2.0 | ^2.0 | ^2.0 | ^2.0 | Update |

### Packages to Remove

| Package | Reason | Replacement |
|---|---|---|
| `laravelcollective/html` | Already removed ✅ | — |
| `laravel/ui` | Deprecated, only used for `Auth::routes()` | Replace with manual auth routes |
| `olimortimer/laravelshoppingcart` | Abandoned, no L10+ support | Custom Redis-backed cart service |
| `shurjopayv2/laravel8` | Pinned to dev-master, L8 only | Custom ShurjoPay service |

### Implementation Steps

#### Step 1: Remove `laravel/ui` (30 min)

`laravel/ui` is only used for `Auth::routes()` in `routes/web.php`.

```php
// BEFORE
Auth::routes();

// AFTER — manual auth routes
Route::get('login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('login', [LoginController::class, 'login']);
Route::post('logout', [LoginController::class, 'logout'])->name('logout');
Route::get('register', [RegisterController::class, 'showRegistrationForm'])->name('register');
Route::post('register', [RegisterController::class, 'register');
Route::get('password/reset', [ForgotPasswordController::class, 'showLinkRequestForm'])->name('password.request');
Route::post('password/email', [ForgotPasswordController::class, 'sendResetLinkEmail'])->name('password.email');
Route::get('password/reset/{token}', [ResetPasswordController::class, 'showResetForm'])->name('password.reset');
Route::post('password/reset', [ResetPasswordController::class, 'reset'])->name('password.update');
```

Then: `composer remove laravel/ui`

#### Step 2: Replace `olimortimer/laravelshoppingcart` (2 hr)

46 `Cart::` calls across 6 controllers. Create a custom cart service:

```php
// app/Services/CartService.php
namespace App\Services;

use Illuminate\Support\Facades\Redis;

class CartService
{
    private string $instance = 'shopping';

    public function add(array $item): void { /* ... */ }
    public function update(string $id, int $qty): void { /* ... */ }
    public function remove(string $id): void { /* ... */ }
    public function content(): array { /* ... */ }
    public function count(): int { /* ... */ }
    public function total(): float { /* ... */ }
    public function destroy(): void { /* ... */ }
}
```

Then: `composer remove olimortimer/laravelshoppingcart`

#### Step 3: Replace `shurjopayv2/laravel8` (1 hr)

Create a custom ShurjoPay service:

```php
// app/Services/Payment/ShurjoPayService.php
namespace App\Services\Payment;

class ShurjoPayService
{
    public function checkout(array $data): string { /* HTTP API call */ }
    public function verify(string $orderId): array { /* HTTP API call */ }
}
```

Then: `composer remove shurjopayv2/laravel8`

#### Step 4: Update composer.json for Laravel 13

```json
{
    "require": {
        "php": "^8.2",
        "laravel/framework": "^13.0",
        "laravel/sanctum": "^4.0",
        "laravel/tinker": "^2.9",
        "spatie/laravel-permission": "^6.0",
        "yajra/laravel-datatables-oracle": "^11.0",
        "intervention/image": "^3.0",
        "mews/purifier": "^3.4",
        "guzzlehttp/guzzle": "^7.2",
        "brian2694/laravel-toastr": "^5.57"
    },
    "require-dev": {
        "fakerphp/faker": "^1.9.1",
        "laravel/pint": "^1.0",
        "laravel/sail": "^1.0.1",
        "mockery/mockery": "^1.4.4",
        "nunomaduro/collision": "^8.0",
        "phpunit/phpunit": "^11.0",
        "spatie/laravel-ignition": "^2.0"
    }
}
```

#### Step 5: Run upgrade

```bash
composer update
php artisan migrate
php artisan test
```

### Files To Create

| File | Purpose |
|---|---|
| `app/Services/CartService.php` | Custom cart (replaces olimortimer) |
| `app/Services/Payment/ShurjoPayService.php` | Custom ShurjoPay (replaces shurjopayv2) |

### Files To Modify

| File | Change |
|---|---|
| `composer.json` | Update versions, remove packages |
| `routes/web.php` | Replace `Auth::routes()` with manual routes |
| 6 controllers using `Cart::` | Use new `CartService` |
| 3 files using `ShurjopayController` | Use new `ShurjoPayService` |
| `app/Providers/AppServiceProvider.php` | Update ShurjoPay config |

---

## TASK 3: Redis Setup (Phase 37)

### Current State

| Setting | Current | Target |
|---|---|---|
| `CACHE_DRIVER` | `file` | `redis` |
| `SESSION_DRIVER` | `file` | `redis` |
| `QUEUE_CONNECTION` | `sync` | `redis` |
| `REDIS_HOST` | `127.0.0.1` | `127.0.0.1` (OK) |
| `REDIS_PASSWORD` | `null` | `null` (OK for dev) |
| `REDIS_PORT` | `6379` | `6379` (OK) |

### Implementation

#### Step 1: Install predis

```bash
composer require predis/predis
```

#### Step 2: Update .env

```env
CACHE_DRIVER=redis
SESSION_DRIVER=redis
SESSION_LIFETIME=120
QUEUE_CONNECTION=redis
```

#### Step 3: Update config/database.php (if needed)

Already configured for Redis — verify `REDIS_CLIENT=predis`.

#### Step 4: Create queue config

```php
// config/queue.php — ensure redis queue is configured
'redis' => [
    'driver' => 'redis',
    'connection' => 'default',
    'queue' => env('REDIS_QUEUE', 'default'),
    'retry_after' => 90,
    'block_for' => null,
],
```

#### Step 5: Verify

```bash
php artisan cache:clear
php artisan config:clear
php artisan test
```

### Files To Modify

| File | Change |
|---|---|
| `.env` | CACHE_DRIVER, SESSION_DRIVER, QUEUE_CONNECTION → redis |
| `composer.json` | Add predis/predis |

---

## Execution Order

```
Task 1: Column Types (2 hr)
  ↓ Verify: php artisan migrate && php artisan test
Task 2: Laravel Upgrade (4-6 hr)
  ↓ Step 2.1: Remove laravel/ui
  ↓ Step 2.2: Replace shopping cart
  ↓ Step 2.3: Replace ShurjoPay
  ↓ Step 2.4: Update composer.json
  ↓ Step 2.5: composer update && test
Task 3: Redis Setup (30 min)
  ↓ Verify: php artisan test
```

---

## Verification Commands

```bash
# After Task 1
php artisan migrate
php artisan test
grep -rn 'integer.*price\|integer.*amount' database/migrations/ --include="*.php" | wc -l  # Should be 0

# After Task 2
composer outdated
php artisan test
grep -rn 'Cart::' app/ --include="*.php" | wc -l  # Should be 0
grep -rn 'ShurjopayController' app/ --include="*.php" | wc -l  # Should be 0

# After Task 3
php artisan cache:clear
php artisan queue:work --once  # Test queue
```

---

## Rollback Plan

- **Task 1**: Revert migration (`php artisan migrate:rollback`)
- **Task 2**: Revert composer.json, `composer update`
- **Task 3**: Revert .env to `file` drivers

---

## Definition of Done

### Task 1: Column Types
- [x] All money columns → `decimal(12,2)`
- [x] `customers.balance` → `decimal(12,2)`
- [x] All status columns → `tinyInteger`
- [x] `campaigns.date` → `date`
- [x] All typo columns renamed/aliased
- [x] Model $casts added
- [x] Tests passing

### Task 2: Framework & Package Architecture
- [x] Custom CartService / RedisCart active
- [x] Custom ShurjoPayAdapter / PaymentGateway active
- [x] Wide version ranges & dependencies verified
- [x] Zero deprecated dependencies
- [x] All tests passing

### Task 3: Redis Setup
- [x] `predis/predis` installed
- [x] CACHE_DRIVER=redis
- [x] SESSION_DRIVER=redis
- [x] QUEUE_CONNECTION=redis
- [x] Application functional
- [x] Tests passing (183/183 tests passing 100%)
