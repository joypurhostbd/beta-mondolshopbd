# Combined Plan — Module Contracts + Architecture Tests + Catalog Module Extraction

> **3 tasks in 1 plan** — connect existing src/ modules to app/  
> **Total estimated**: 5-6 hours  
> **Risk**: Medium

---

## Current State Analysis

### ✅ Already Exists in `src/`

| Component | Count | Status |
|---|---|---|
| Module directories | 8 (Catalog, Customer, Inventory, Order, Payment, Promotion, Setting, Shipping) | ✅ Structure exists |
| Shared Contracts | 13 interfaces | ✅ |
| Shared Enums | 7 enums | ✅ |
| Shared ValueObjects | 6 (Money, Email, Phone, Quantity, Discount, TrackingCode) | ✅ |
| Shared Exceptions | 7 exceptions | ✅ |
| Shared Infrastructure | 17 files (Cache, HTTP Resources, ViewModels, Components) | ✅ |
| Catalog Module | 39 files (Actions, DTOs, Services, Entities, Contracts, Repositories) | ✅ |
| Customer Module | 22 files | ✅ |
| Order Module | 30 files | ✅ |
| Payment Module | 17 files | ✅ |
| Shipping Module | 15 files | ✅ |
| **Total src/ files** | **166 PHP files** | ✅ |

### ❌ Not Connected

| Issue | Details |
|---|---|
| `app/` controllers don't use `src/` modules | Controllers still use `app/Models/` directly |
| `app/Models/` still the source of truth | `src/Modules/*/Domain/Entities/` are unused |
| No ServiceProvider registration | Module providers not registered |
| No architecture tests | Nothing enforces module boundaries |
| `composer.json` autoload | `Shared\\` and `Modules\\` may not be autoloaded |

---

## TASK 1: Module Contracts & Wiring (Phase 47)

### Goal

Connect existing `src/` module structure to `app/` so controllers can use module Actions/Services instead of direct Model access.

### Step 1: Verify Autoload

```json
// composer.json — verify these exist
"autoload": {
    "psr-4": {
        "App\\": "app/",
        "Shared\\": "src/Shared/",
        "Modules\\": "src/Modules/",
        ...
    }
}
```

If missing, add and run `composer dump-autoload`.

### Step 2: Register Module ServiceProviders

```php
// app/Providers/AppServiceProvider.php — add to register()
$this->app->register(\Modules\Catalog\Infrastructure\Providers\CatalogServiceProvider::class);
$this->app->register(\Modules\Customer\Infrastructure\Providers\CustomerServiceProvider::class);
$this->app->register(\Modules\Order\Infrastructure\Providers\OrderServiceProvider::class);
$this->app->register(\Modules\Payment\Infrastructure\Providers\PaymentServiceProvider::class);
$this->app->register(\Modules\Shipping\Infrastructure\Providers\ShippingServiceProvider::class);
$this->app->register(\Modules\Setting\Infrastructure\Providers\SettingServiceProvider::class);
```

### Step 3: Bind Contracts to Implementations

Each module's ServiceProvider should bind its Repository interface to Eloquent implementation:

```php
// Modules/Catalog/Infrastructure/Providers/CatalogServiceProvider.php
public function register()
{
    $this->app->bind(
        \Modules\Catalog\Domain\Contracts\ProductRepositoryInterface::class,
        \Modules\Catalog\Infrastructure\Repositories\EloquentProductRepository::class
    );
    $this->app->bind(
        \Modules\Catalog\Domain\Contracts\CategoryRepositoryInterface::class,
        \Modules\Catalog\Infrastructure\Repositories\EloquentCategoryRepository::class
    );
}
```

### Step 4: Verify Module Files Are Complete

Check each module has actual implementation (not just skeleton):

```bash
# Catalog
find src/Modules/Catalog/ -name "*.php" -type f | wc -l  # Should be 39
cat src/Modules/Catalog/Application/Actions/CreateProductAction.php  # Should have logic

# Customer
find src/Modules/Customer/ -name "*.php" -type f | wc -l  # Should be 22

# Order
find src/Modules/Order/ -name "*.php" -type f | wc -l  # Should be 30
```

### Files To Modify

| File | Change |
|---|---|
| `composer.json` | Verify Shared\\ and Modules\\ autoload |
| `app/Providers/AppServiceProvider.php` | Register module providers |
| Module ServiceProviders | Bind contracts to implementations |

---

## TASK 2: Architecture Fitness Tests (Phase 48)

### Goal

Automated tests that enforce module boundaries and prevent architectural regression.

### Step 1: Create Architecture Test

```php
// tests/Unit/ArchitectureFitnessTest.php
namespace Tests\Unit;

use Tests\TestCase;
use ReflectionClass;

class ArchitectureFitnessTest extends TestCase
{
    /** @test */
    public function controllers_do_not_contain_eloquent_queries()
    {
        $controllers = glob(app_path('Http/Controllers/**/*.php'));

        foreach ($controllers as $controller) {
            $content = file_get_contents($controller);

            // Allow FormRequest validation, but not direct Model queries
            $this->assertStringNotContainsString(
                '::where(',
                $content,
                "Controller {$controller} contains direct Eloquent query"
            );
        }
    }

    /** @test */
    public function models_do_not_make_http_calls()
    {
        $models = glob(app_path('Models/*.php'));

        foreach ($models as $model) {
            $content = file_get_contents($model);

            $this->assertStringNotContainsString(
                'Http::',
                $content,
                "Model {$model} makes HTTP calls"
            );
            $this->assertStringNotContainsString(
                'curl_',
                $content,
                "Model {$model} uses curl"
            );
        }
    }

    /** @test */
    public function all_models_use_fillable_not_guarded()
    {
        $models = glob(app_path('Models/*.php'));

        foreach ($models as $model) {
            $content = file_get_contents($model);

            $this->assertStringNotContainsString(
                '$guarded',
                $content,
                "Model {$model} uses \$guarded instead of \$fillable"
            );
        }
    }

    /** @test */
    public function no_blade_files_contain_raw_user_content()
    {
        $blades = glob(resource_path('views/**/*.blade.php'));

        foreach ($blades as $blade) {
            $content = file_get_contents($blade);

            // Skip vendor files
            if (str_contains($blade, 'vendor/')) continue;

            // Check for {!! !!} without clean()
            if (preg_match('/\{!!\s*\$(?!\()/') && !str_contains($content, 'clean(')) {
                $this->fail("Blade {$blade} contains unescaped user content");
            }
        }
    }

    /** @test */
    public function shared_namespace_does_not_depend_on_modules()
    {
        $sharedFiles = glob(base_path('src/Shared/**/*.php'));

        foreach ($sharedFiles as $file) {
            $content = file_get_contents($file);

            $this->assertStringNotContainsString(
                'use Modules\\',
                $content,
                "Shared file {$file} depends on Modules namespace"
            );
        }
    }

    /** @test */
    public function module_contracts_do_not_contain_eloquent()
    {
        $contracts = glob(base_path('src/Modules/*/Domain/Contracts/*.php'));

        foreach ($contracts as $contract) {
            $content = file_get_contents($contract);

            $this->assertStringNotContainsString(
                'use Illuminate\\Database\\Eloquent',
                $content,
                "Contract {$contract} depends on Eloquent"
            );
        }
    }
}
```

### Step 2: Run Tests

```bash
php artisan test --filter=ArchitectureFitnessTest
```

### Files To Create

| File | Purpose |
|---|---|
| `tests/Unit/ArchitectureFitnessTest.php` | Architecture boundary enforcement |

---

## TASK 3: Catalog Module Extraction (Phase 49-54)

### Goal

Wire existing `src/Modules/Catalog/` to `app/` so ProductController uses module Actions instead of direct Model access.

### Current Catalog Module (39 files in src/)

```
src/Modules/Catalog/
├── Application/
│   ├── Actions/ (14 files)
│   │   ├── CreateProductAction.php
│   │   ├── UpdateProductAction.php
│   │   ├── DeleteProductAction.php
│   │   ├── ToggleProductStatusAction.php
│   │   ├── BulkUpdateProductPricesAction.php
│   │   ├── CreateCategoryAction.php
│   │   ├── UpdateCategoryAction.php
│   │   ├── DeleteCategoryAction.php
│   │   ├── GetCategoryTreeAction.php
│   │   ├── GetCatalogAttributesAction.php
│   │   ├── CreateBrandAction.php
│   │   ├── CreateColorAction.php
│   │   ├── CreateSizeAction.php
│   │   └── UpdateProductStockAction.php
│   ├── DTOs/ (7 files)
│   │   ├── ProductDTO.php
│   │   ├── CategoryDTO.php
│   │   ├── SubcategoryDTO.php
│   │   ├── ChildcategoryDTO.php
│   │   ├── BrandDTO.php
│   │   ├── ColorDTO.php
│   │   └── SizeDTO.php
│   └── Services/
│       └── CatalogService.php
├── Domain/
│   ├── Contracts/ (3 files)
│   │   ├── ProductRepositoryInterface.php
│   │   ├── CategoryRepositoryInterface.php
│   │   └── AttributeRepositoryInterface.php
│   ├── Entities/ (5 files)
│   │   ├── ProductEntity.php
│   │   ├── CategoryEntity.php
│   │   ├── SubcategoryEntity.php
│   │   ├── ChildcategoryEntity.php
│   │   └── BrandEntity.php
│   └── Events/
└── Infrastructure/
    ├── Providers/
    │   └── CatalogServiceProvider.php
    └── Repositories/
        ├── EloquentProductRepository.php
        └── EloquentCategoryRepository.php
```

### Implementation Steps

#### Step 1: Verify Catalog Module Completeness

```bash
# Check if Actions have actual logic (not empty stubs)
cat src/Modules/Catalog/Application/Actions/CreateProductAction.php
cat src/Modules/Catalog/Infrastructure/Repositories/EloquentProductRepository.php
```

#### Step 2: Update ProductController to Use Module

```php
// BEFORE — app/Http/Controllers/Admin/ProductController.php
use App\Models\Product;
use App\Models\Category;
// ... direct Model usage

// AFTER
use Modules\Catalog\Application\Actions\CreateProductAction;
use Modules\Catalog\Application\Actions\UpdateProductAction;
use Modules\Catalog\Application\DTOs\ProductDTO;

class ProductController extends Controller
{
    public function store(StoreProductRequest $request, CreateProductAction $action)
    {
        $dto = ProductDTO::from($request->validated());
        $product = $action->execute($dto);
        return redirect()->route('products.index')->with('success', 'Product created.');
    }
}
```

#### Step 3: Update CategoryController to Use Module

```php
// AFTER
use Modules\Catalog\Application\Actions\CreateCategoryAction;
use Modules\Catalog\Application\Actions\UpdateCategoryAction;
```

#### Step 4: Update BrandController, ColorController, SizeController

Same pattern — use module Actions.

#### Step 5: Verify

```bash
php artisan test --filter=CatalogModuleTest
php artisan test --filter=ArchitectureFitnessTest
```

### Files To Modify

| File | Change |
|---|---|
| `app/Http/Controllers/Admin/ProductController.php` | Use CreateProductAction, UpdateProductAction |
| `app/Http/Controllers/Admin/CategoryController.php` | Use CreateCategoryAction, UpdateCategoryAction |
| `app/Http/Controllers/Admin/BrandController.php` | Use CreateBrandAction |
| `app/Http/Controllers/Admin/ColorController.php` | Use CreateColorAction |
| `app/Http/Controllers/Admin/SizeController.php` | Use CreateSizeAction |
| `app/Http/Controllers/Admin/SubcategoryController.php` | Use module actions |
| `app/Http/Controllers/Admin/ChildcategoryController.php` | Use module actions |
| `app/Providers/AppServiceProvider.php` | Register CatalogServiceProvider |

### Files Already Exist (verify completeness)

| File | Purpose |
|---|---|
| `src/Modules/Catalog/Application/Actions/*.php` | 14 action classes |
| `src/Modules/Catalog/Application/DTOs/*.php` | 7 DTO classes |
| `src/Modules/Catalog/Domain/Contracts/*.php` | 3 repository interfaces |
| `src/Modules/Catalog/Infrastructure/Repositories/*.php` | 2 Eloquent repositories |
| `src/Modules/Catalog/Infrastructure/Providers/CatalogServiceProvider.php` | Service provider |

---

## Execution Order

```
Task 1: Module Wiring (1 hr)
  ↓ Verify autoload, register providers, bind contracts
Task 2: Architecture Tests (30 min)
  ↓ Create and run ArchitectureFitnessTest
Task 3: Catalog Module (4 hr)
  ↓ Step 3.1: Verify module completeness
  ↓ Step 3.2: Update ProductController
  ↓ Step 3.3: Update CategoryController
  ↓ Step 3.4: Update Brand/Color/Size controllers
  ↓ Step 3.5: Run tests
```

---

## Verification Commands

```bash
# After Task 1
composer dump-autoload
php artisan route:list | head -20  # Verify routes still work

# After Task 2
php artisan test --filter=ArchitectureFitnessTest

# After Task 3
php artisan test --filter=CatalogModuleTest
php artisan test --filter=ArchitectureFitnessTest
grep -rn 'App\\Models\\Product' app/Http/Controllers/Admin/ProductController.php | wc -l  # Should be 0
```

---

## Definition of Done

### Task 1: Module Wiring
- [ ] composer.json has Shared\\ and Modules\\ autoload
- [ ] Module ServiceProviders registered
- [ ] Contracts bound to implementations
- [ ] Application boots without errors

### Task 2: Architecture Tests
- [ ] ArchitectureFitnessTest created
- [ ] All tests passing
- [ ] CI can run architecture checks

### Task 3: Catalog Module
- [ ] ProductController uses CreateProductAction/UpdateProductAction
- [ ] CategoryController uses module actions
- [ ] BrandController uses CreateBrandAction
- [ ] ColorController uses CreateColorAction
- [ ] SizeController uses CreateSizeAction
- [ ] No direct Model usage in catalog controllers
- [ ] All tests passing

---

## Risks

| Risk | Mitigation |
|---|---|
| Module stubs may be empty | Verify before wiring, fill gaps |
| Breaking existing functionality | Characterization tests before changes |
| Autoload conflicts | Test with `composer dump-autoload` first |
| Controller changes break views | Keep same redirect/response patterns |
