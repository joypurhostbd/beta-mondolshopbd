# Order Items Image — Featured Image Priority Plan

**Goal:** All order views (admin + customer) show featured image instead of random first gallery image.

**Architecture:** Update `OrderDetails` model with `featuredImageRelation()` + update fallback chain in 4 blade files.

---

## Current State

| View | File | Current Image Logic |
|------|------|-------------------|
| Admin Order Edit | `cart_content.blade.php:7` | `$cartProd?->image?->image` (first gallery image) |
| Admin Process | `process.blade.php:41` | `$product->image?$product->image->image` (first gallery) |
| Customer Order Success | `order_success.blade.php:516` | `$detail->image?->image ?? $detail->product?->image?->image` |
| Customer Tracking | `tracking_result.blade.php` | No image column (text only) |
| Customer Invoice | `invoice.blade.php` | No image column (text only) |

## Changes Needed (4 files)

### 1. `app/Models/OrderDetails.php` — Add featuredImageRelation

```php
public function featuredImageRelation()
{
    return $this->belongsTo(Productimage::class, 'product_id', 'product_id')
        ->where('is_featured', true)
        ->select('id', 'product_id', 'image', 'is_featured');
}
```

### 2. `resources/views/backEnd/order/cart_content.blade.php` — Line 7

Replace:
```php
$featureImage = $cartProd?->image?->image ?? '';
```
With:
```php
$featureImage = ($cartProd?->featuredImage ?? $cartProd?->image)?->image ?? '';
```

### 3. `resources/views/backEnd/order/process.blade.php` — Line 41

Replace:
```php
<img src="{{asset($product->image?$product->image->image:'')}}" height="50" width="50" alt="">
```
With:
```php
<img src="{{asset(($product->featuredImageRelation ?? $product->image)?->image ?? '')}}" height="50" width="50" alt="">
```

### 4. `resources/views/frontEnd/layouts/customer/order_success.blade.php` — Line 516

Replace:
```php
$productImage = $detail->image?->image ?? $detail->product?->image?->image;
```
With:
```php
$productImage = ($detail->featuredImageRelation ?? $detail->image)?->image ?? $detail->product?->featuredImage?->image ?? $detail->product?->image?->image;
```

## Verification

1. `php artisan test` — zero regressions
2. Admin order edit `/admin/order/edit/{id}` — Image column shows featured image
3. Customer order success page — Product images show featured
4. Fallback: products with no featured image still show first gallery image (existing behavior preserved)
