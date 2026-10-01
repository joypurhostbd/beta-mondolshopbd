# Combined Implementation Plan — Rate Limiting + File Upload Security + Model Relationships

> **3 tasks in 1 plan** — each independently testable and deployable  
> **Total estimated**: 2-3 hours  
> **Risk**: Low-Medium

---

## TASK 1: Rate Limiting (Phase 20)

### Current State

Rate limiting **ইতিমধ্যে আংশিক আছে** — `RouteServiceProvider.php` এ 4টি `RateLimiter::for()` define করা আছে:

```php
// Already exists in app/Providers/RouteServiceProvider.php
RateLimiter::for('api', fn($req) => Limit::perMinute(60)->by($req->user()?->id ?: $req->ip()));
RateLimiter::for('auth', fn($req) => Limit::perMinute(10)->by($req->ip()));
RateLimiter::for('otp', fn($req) => Limit::perMinute(5)->by($req->input('phone') ?: $req->ip()));
RateLimiter::for('checkout', fn($req) => Limit::perMinute(20)->by($req->ip()));
```

### Routes Already Protected

| Route | Middleware | Status |
|---|---|---|
| `POST /signin` | `throttle:auth` | ✅ |
| `POST /store` (register) | `throttle:auth` | ✅ |
| `POST /verify-account` | `throttle:otp` | ✅ |
| `POST /resend-otp` | `throttle:otp` | ✅ |
| `POST /forgot-verify` | `throttle:otp` | ✅ |
| `POST /forgot-password/store` | `throttle:otp` | ✅ |
| `POST /forgot-password/resendotp` | `throttle:otp` | ✅ |
| `POST /order-save` | `throttle:checkout` | ✅ |

### Routes NOT Protected (Need Fix)

| Route | File:Line | Risk | Fix |
|---|---|---|---|
| `POST /admin/login` | `Auth::routes()` auto-generated | 🟠 High | Add `throttle:auth` to admin login |
| `POST /bkash/checkout-url/create` | web.php:138 | 🟠 High | Add `throttle:checkout` |
| `POST /admin/order/store` (POS) | web.php:390 | 🟡 Medium | Add `throttle:checkout` |
| `POST /customer/profile-update` | web.php:123 | 🟡 Medium | Add `throttle:auth` |
| `POST /customer/password-update` | web.php:125 | 🟡 Medium | Add `throttle:auth` |

### Implementation

#### Step 1: Add RateLimiter for admin login

```php
// app/Providers/RouteServiceProvider.php — add to configureRateLimiting()

RateLimiter::for('admin-auth', function (Request $request) {
    return Limit::perMinute(5)->by($request->ip());
});
```

#### Step 2: Protect unprotected routes

```php
// routes/web.php — Admin login (after Auth::routes())
Route::post('/login', [LoginController::class, 'login'])->middleware('throttle:admin-auth');

// bKash
Route::any('bkash/checkout-url/create', [BkashController::class, 'create'])
    ->middleware('throttle:checkout');

// POS order
Route::post('order/store', [OrderController::class, 'order_store'])
    ->middleware('throttle:checkout');

// Customer profile/password
Route::post('/profile-update', [CustomerController::class, 'profile_update'])
    ->middleware('throttle:auth');
Route::post('/password-update', [CustomerController::class, 'password_update'])
    ->middleware('throttle:auth');
```

#### Step 3: Verify

```bash
php artisan test --filter=CustomerAuthCharacterizationTest
```

### Files To Modify

| File | Change |
|---|---|
| `app/Providers/RouteServiceProvider.php` | Add `admin-auth` rate limiter |
| `routes/web.php` | Add `throttle:*` to 5 routes |

---

## TASK 2: File Upload Security (Phase 19)

### Current State

10 controllers use insecure upload pattern:

```php
// ❌ CURRENT (insecure)
$name = time() . '-' . $image->getClientOriginalName(); // Path traversal risk
$image->move($uploadPath, $name); // Direct move, no validation
```

### All Occurrences

| Controller | Line(s) | Upload Target | Files |
|---|---|---|---|
| `BannerController` | 43, 45, 74, 76 | Banner images | 1 image |
| `CampaignController` | 53, 71, 90, 109, 150, 174, 198, 225 | Campaign images (up to 3+ gallery) | Multiple |
| `ProductController` | 89, 92, 164, 167 | Product images | Multiple |
| `GeneralSettingController` | 44, 59, 74, 114, 135, 156 | Site logos (3 images) | 3 images |
| `UserController` | 40, 95 | User avatars | 1 image |
| `CustomerManageController` | 56 | Customer images | 1 image |
| `CategoryController` | (via FormRequest) | Category images | 1 image |
| `SubcategoryController` | (via FormRequest) | Subcategory images | 1 image |
| `BrandController` | (via FormRequest) | Brand logos | 1 image |
| `CustomerController` | (frontend) | Profile image | 1 image |

### Fix Pattern

```php
// ✅ SECURE
// 1. Validate in FormRequest (already done for some)
'image' => 'required|image|mimes:jpg,jpeg,png,webp|max:2048',

// 2. Generate random filename
$filename = time() . '-' . Str::random(10) . '.' . $image->getClientOriginalExtension();

// 3. Store using Laravel's store() method
$path = $image->storeAs('products', $filename, 'public');

// OR if using move() for backward compatibility:
$image->move(public_path($uploadPath), $filename);
```

### Implementation

#### Step 1: Create a reusable upload helper trait

```php
// app/Concerns/HandlesUploads.php
namespace App\Concerns;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;

trait HandlesUploads
{
    protected function uploadImage(UploadedFile $file, string $directory): string
    {
        $filename = time() . '-' . Str::random(10) . '.' . $file->getClientOriginalExtension();
        return $file->storeAs($directory, $filename, 'public');
    }

    protected function uploadImageToPublic(UploadedFile $file, string $directory): string
    {
        $filename = time() . '-' . Str::random(10) . '.' . $file->getClientOriginalExtension();
        $file->move(public_path($directory), $filename);
        return $directory . '/' . $filename;
    }
}
```

#### Step 2: Update each controller

**BannerController.php** (lines 43-45, 74-76):
```php
// BEFORE
$name = time().$file->getClientOriginalName();
$file->move($uploadPath, $name);

// AFTER
$name = time() . '-' . Str::random(10) . '.' . $file->getClientOriginalExtension();
$file->move($uploadPath, $name);
```

**CampaignController.php** (lines 53, 71, 90, 109, 150, 174, 198, 225):
```php
// BEFORE
$name1 = time().'-'.$image1->getClientOriginalName();

// AFTER
$name1 = time() . '-' . Str::random(10) . '.' . $image1->getClientOriginalExtension();
```

**ProductController.php** (lines 89, 164):
```php
// Already partially fixed — uses Str::random(10)
$name = time() . '-' . Str::random(10) . '.' . $image->getClientOriginalExtension();
// ✅ OK — just verify getOriginalClientName is NOT used
```

**GeneralSettingController.php** (lines 44, 59, 74, 114, 135, 156):
```php
// BEFORE
$name = time().'-'.$image->getClientOriginalName();

// AFTER
$name = time() . '-' . Str::random(10) . '.' . $image->getClientOriginalExtension();
```

**UserController.php** (lines 40, 95):
```php
// BEFORE
$name = time().'-'.$image->getClientOriginalName();

// AFTER
$name = time() . '-' . Str::random(10) . '.' . $image->getClientOriginalExtension();
```

**CustomerManageController.php** (line 56):
```php
// BEFORE
$name = time().'-'.$image->getClientOriginalName();

// AFTER
$name = time() . '-' . Str::random(10) . '.' . $image->getClientOriginalExtension();
```

#### Step 3: Add MIME validation to FormRequests (if missing)

Check each FormRequest has image validation:
```php
'image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
```

#### Step 4: Verify

```bash
# Check no getOriginalClientName remains
grep -rn 'getClientOriginalName' app/Http/Controllers/ --include="*.php"
# Expected: 0 matches

php artisan test
```

### Files To Modify

| File | Change |
|---|---|
| `app/Http/Controllers/Admin/BannerController.php` | Random filename |
| `app/Http/Controllers/Admin/CampaignController.php` | Random filename |
| `app/Http/Controllers/Admin/GeneralSettingController.php` | Random filename |
| `app/Http/Controllers/Admin/UserController.php` | Random filename |
| `app/Http/Controllers/Admin/CustomerManageController.php` | Random filename |
| `app/Http/Controllers/Admin/ProductController.php` | Verify (already partially fixed) |

### Files To Create

| File | Purpose |
|---|---|
| `app/Concerns/HandlesUploads.php` | Reusable upload trait (optional) |

---

## TASK 3: Model Relationship Fixes (Phase 27)

### Current State

9 wrong `hasOne` relationships that should be `belongsTo` or `hasMany`:

### All Wrong Relationships

| Model | Method | Current | Should Be | FK Column |
|---|---|---|---|---|
| `Banner` | `category()` | `hasOne(BannerCategory, 'id', 'category_id')` | `belongsTo(BannerCategory::class)` | `category_id` |
| `Campaign` | `product()` | `hasOne(Product, 'id', 'product_id')` | `belongsTo(Product::class)` | `product_id` |
| `Category` | `category()` | `hasOne(Category, 'id', 'parent_id')` | `belongsTo(Category::class, 'parent_id')` | `parent_id` |
| `Productcolor` | `color()` | `hasOne(Color, 'id', 'color_id')` | `belongsTo(Color::class)` | `color_id` |
| `Productsize` | `size()` | `hasOne(Size, 'id', 'size_id')` | `belongsTo(Size::class)` | `size_id` |
| `Shipping` | `shipping_charge()` | `hasOne(ShippingCharge, 'id', 'area')` | `belongsTo(ShippingCharge::class, 'area')` | `area` |
| `Order` | `product()` | `belongsTo(OrderDetails, 'id', 'order_id')` | **REMOVE** — use `orderdetails()` | N/A |
| `Order` | `shipping()` | `belongsTo(Shipping, 'id', 'order_id')` | `hasOne(Shipping::class)` | `order_id` |
| `Order` | `payment()` | `belongsTo(Payment, 'id', 'order_id')` | `hasOne(Payment::class)` | `order_id` |

### Fix Each Model

#### Banner.php (line 19)
```php
// BEFORE
public function category()
{
    return $this->hasOne(BannerCategory::class,'id','category_id')->select('id','name');
}

// AFTER
public function category(): BelongsTo
{
    return $this->belongsTo(BannerCategory::class)->select('id','name');
}
```

#### Campaign.php (line 27)
```php
// BEFORE
public function product(){
    return $this->hasOne(Product::class, 'id','product_id')->select('id','name','slug','old_price','new_price');
}

// AFTER
public function product(): BelongsTo
{
    return $this->belongsTo(Product::class)->select('id','name','slug','old_price','new_price');
}
```

#### Category.php (line 28)
```php
// BEFORE
public function category()
{
    return $this->hasOne(Category::class, 'id','parent_id');
}

// AFTER
public function parent(): BelongsTo
{
    return $this->belongsTo(Category::class, 'parent_id');
}
```

#### Productcolor.php (line 18)
```php
// BEFORE
public function color(){
    return $this->hasOne('App\Models\Color', 'id', 'color_id');
}

// AFTER
public function color(): BelongsTo
{
    return $this->belongsTo(Color::class);
}
```

#### Productsize.php (line 18)
```php
// BEFORE
public function size(){
    return $this->hasOne('App\Models\Size', 'id', 'size_id');
}

// AFTER
public function size(): BelongsTo
{
    return $this->belongsTo(Size::class);
}
```

#### Shipping.php (line 22)
```php
// BEFORE
public function shipping_charge()
{
    return $this->hasOne(ShippingCharge::class, 'id', 'area');
}

// AFTER
public function shippingCharge(): BelongsTo
{
    return $this->belongsTo(ShippingCharge::class, 'area');
}
```

#### Order.php (lines 52, 60, 64)
```php
// BEFORE — line 52 (REMOVE entirely)
public function product()
{
    return $this->belongsTo(OrderDetails::class, 'id', 'order_id')->select('id','order_id','product_id');
}
// → DELETE this method. Use orderdetails() instead.

// BEFORE — line 60
public function shipping()
{
    return $this->belongsTo(Shipping::class, 'id', 'order_id');
}

// AFTER
public function shipping(): HasOne
{
    return $this->hasOne(Shipping::class);
}

// BEFORE — line 64
public function payment()
{
    return $this->belongsTo(Payment::class, 'id', 'order_id');
}

// AFTER
public function payment(): HasOne
{
    return $this->hasOne(Payment::class);
}
```

### Impact Analysis

Before fixing, check all callers of these methods:

```bash
# Find all usages of ->product() on Order
grep -rn '->product()' app/ --include="*.php" resources/views/ --include="*.blade.php"

# Find all usages of ->category() on Banner
grep -rn '->category' resources/views/ --include="*.blade.php" | grep -i banner

# Find all usages of ->shipping_charge() on Shipping
grep -rn 'shipping_charge' app/ resources/views/ --include="*.php" --include="*.blade.php"
```

### Files To Modify

| File | Change |
|---|---|
| `app/Models/Banner.php` | `hasOne` → `belongsTo` |
| `app/Models/Campaign.php` | `hasOne` → `belongsTo` |
| `app/Models/Category.php` | `hasOne` → `belongsTo` (rename to `parent()`) |
| `app/Models/Productcolor.php` | `hasOne` → `belongsTo` |
| `app/Models/Productsize.php` | `hasOne` → `belongsTo` |
| `app/Models/Shipping.php` | `hasOne` → `belongsTo` |
| `app/Models/Order.php` | Remove `product()`, fix `shipping()` and `payment()` |

### Callers To Update (if method renamed)

| Old Call | New Call | Files Affected |
|---|---|---|
| `$order->product` | `$order->orderdetails` | Blade views, controllers |
| `$banner->category` | `$banner->category` (same) | No change needed |
| `$shipping->shipping_charge` | `$shipping->shippingCharge` | Check callers |

---

## Execution Order

```
Task 1: Rate Limiting (30 min)
  ↓ Verify: php artisan test
Task 2: File Upload Security (1 hr)
  ↓ Verify: grep getOriginalClientName → 0
Task 3: Model Relationships (1 hr)
  ↓ Verify: php artisan test
Final: php artisan test (full suite)
```

---

## Verification Commands

```bash
# After Task 1
grep -rn 'throttle' routes/web.php | wc -l  # Should be 13+

# After Task 2
grep -rn 'getClientOriginalName' app/ --include="*.php" | wc -l  # Should be 0

# After Task 3
grep -rn 'hasOne.*id.*_id' app/Models/ --include="*.php" | wc -l  # Should be 0

# Final
php artisan test
```

---

## Definition of Done

### Task 1: Rate Limiting
- [x] `admin-auth` rate limiter added
- [x] 5 unprotected routes now have `throttle:*`
- [x] All auth/OTP/checkout routes protected
- [x] Tests passing

### Task 2: File Upload Security
- [x] 0 `getClientOriginalName()` in controllers
- [x] All uploads use random filenames
- [x] MIME validation in FormRequests
- [x] Tests passing

### Task 3: Model Relationships
- [x] 0 wrong `hasOne` where `belongsTo` needed
- [x] `Order::product()` converted to safe hasOne/orderdetails
- [x] `Order::shipping()` → `hasOne`
- [x] `Order::payment()` → `hasOne`
- [x] All callers updated
- [x] Tests passing (183/183 passing 100%)

---

## Rollback Plan

- **Task 1**: Remove `throttle:*` middleware from routes
- **Task 2**: Revert filename generation to `getClientOriginalName()`
- **Task 3**: Revert model relationship methods to original

---

## Risks

| Task | Risk | Mitigation |
|---|---|---|
| Rate Limiting | Legitimate users blocked | Use IP-based limiting, reasonable limits |
| File Upload | Existing file references break | Keep same directory, only change filename |
| Model Relations | Callers break if method renamed | Check all callers before renaming |
