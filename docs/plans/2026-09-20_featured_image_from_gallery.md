# Featured Image Selection from Gallery — Implementation Plan

> **For Hermes:** Use subagent-driven-development skill to implement this plan task-by-task.

**Goal:** Admin can pick a gallery image as "featured image" per product. Frontend shows that image as the primary/hero image.

**Architecture:** Add `is_featured` column to `productimages` table. Admin selects featured image via radio button in gallery section (create + edit). `Product` model gets `featuredImage()` relation. Frontend prefers featured image, falls back to first gallery image.

**Tech Stack:** Laravel 13, MySQL, Blade, jQuery

---

## Current State Analysis

| Component | Current Behavior |
|-----------|-----------------|
| `productimages` table | `id`, `image`, `product_id`, `timestamps` — no featured flag |
| `Product::image()` | `hasOne(Productimage)` — returns first gallery image (arbitrary) |
| `Product::images()` | `hasMany(Productimage)` — all gallery images |
| Frontend product card | `$value->image->image` (first image, no control) |
| Frontend details page | Carousel of all `$details->images` |
| Admin create form | Multi-file upload, no selection after upload |
| Admin edit form | Shows existing gallery with delete buttons, no featured selection |

---

## Task 1: Migration — Add `is_featured` to `productimages`

**Objective:** Add `is_featured` boolean column to `productimages` table.

**Files:**
- Create: `database/migrations/2026_09_20_120000_add_is_featured_to_productimages_table.php`

**Step 1: Create migration**

```bash
php artisan make:migration add_is_featured_to_productimages_table
```

**Step 2: Write migration content**

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('productimages', function (Blueprint $table) {
            $table->boolean('is_featured')->default(false)->after('product_id');
        });
    }

    public function down(): void
    {
        Schema::table('productimages', function (Blueprint $table) {
            $table->dropColumn('is_featured');
        });
    }
};
```

**Step 3: Run migration**

```bash
php artisan migrate
```

**Step 4: Commit**

```bash
git add database/migrations/*_add_is_featured_to_productimages_table.php
git commit -m "feat(product): add is_featured column to productimages table"
```

---

## Task 2: Update Productimage Model

**Objective:** Add `is_featured` to fillable and casts.

**Files:**
- Modify: `app/Models/Productimage.php`

**Step 1: Update model**

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Productimage extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'image',
        'is_featured',
    ];

    protected $casts = [
        'is_featured' => 'boolean',
    ];
}
```

**Step 2: Commit**

```bash
git add app/Models/Productimage.php
git commit -m "feat(product): add is_featured to Productimage fillable and casts"
```

---

## Task 3: Update Product Model — Add `featuredImage()` Relation

**Objective:** Add relation that returns the featured image, with fallback logic.

**Files:**
- Modify: `app/Models/Product.php`

**Step 1: Add `featuredImage()` relation after existing `image()` method (line ~51)**

```php
public function featuredImage()
{
    return $this->hasOne(Productimage::class, 'product_id')
        ->where('is_featured', true)
        ->select('id', 'image', 'product_id', 'is_featured');
}
```

**Step 2: Add helper accessor for safe fallback**

```php
public function getFeaturedImageAttribute()
{
    return $this->featuredImageRelation ?? $this->image;
}
```

Wait — this conflicts with the relation name. Better approach: keep just the relation, handle fallback in Blade with null coalescing.

Actually, simplest: rename the relation and use it cleanly.

```php
// Replace the featuredImage() relation with:
public function featuredImage()
{
    return $this->hasOne(Productimage::class, 'product_id')
        ->where('is_featured', true)
        ->select('id', 'image', 'product_id', 'is_featured');
}
```

Frontend will use: `$product->featuredImage ?? $product->image` (falls back to first gallery image if no featured set).

**Step 3: Commit**

```bash
git add app/Models/Product.php
git commit -m "feat(product): add featuredImage relation to Product model"
```

---

## Task 4: Admin Create Form — Featured Image Selection

**Objective:** After uploading gallery images, admin can select one as featured via radio button.

**Files:**
- Modify: `resources/views/backEnd/product/create.blade.php`

**Problem:** On create form, images are uploaded via `image[]` file input — they don't exist in DB yet, so we can't show radio buttons before submission.

**Solution:** Add a `featured_image_index` hidden input. Admin uploads multiple files, the first file is used as featured by default. Or simpler: add a separate "Featured Image" single-file upload field that is independent of the gallery.

**Simplest approach (DRY, no JS complexity):** Add a separate `featured_image` file input field. On store, save it to `productimages` with `is_featured = true`. Gallery images saved with `is_featured = false`.

**Step 1: Add featured image field in create.blade.php after the gallery upload section (after line 181)**

```blade
<div class="col-sm-4 mb-3">
    <label for="featured_image">Featured Image</label>
    <div class="input-group">
        <input type="file" name="featured_image" class="form-control @error('featured_image') is-invalid @enderror" accept="image/*" />
    </div>
    <small class="text-muted">This image will be used as the primary/hero image in product listings.</small>
    @error('featured_image')
    <span class="invalid-feedback d-block" role="alert">
        <strong>{{ $message }}</strong>
    </span>
    @enderror
</div>
```

**Step 2: Commit**

```bash
git add resources/views/backEnd/product/create.blade.php
git commit -m "feat(product): add featured image upload field to create form"
```

---

## Task 5: Admin Edit Form — Featured Image Selection from Gallery

**Objective:** In edit form, admin can click a radio button on any existing gallery image to set it as featured. Also show current featured image with a badge.

**Files:**
- Modify: `resources/views/backEnd/product/edit.blade.php`

**Step 1: Replace the existing gallery display section (lines 193-198) with radio-button selection**

Replace this block:
```blade
<div class="product_img">
    @foreach($edit_data->images as $image)
    <img src="{{asset($image->image)}}" class="edit-image border" alt="" />
    <a href="{{route('products.image.destroy',['id'=>$image->id])}}" class="btn btn-xs btn-danger waves-effect waves-light"><i class="mdi mdi-close"></i></a>
    @endforeach
</div>
```

With:
```blade
<div class="product_img d-flex flex-wrap gap-3">
    @foreach($edit_data->images as $image)
    <div class="position-relative text-center" style="width: 110px;">
        <img src="{{asset($image->image)}}" class="edit-image border rounded {{ $image->is_featured ? 'border-success border-3' : '' }}" alt="" style="width:100px;height:100px;object-fit:cover;" />
        <div class="mt-1">
            <label class="form-check-label small">
                <input type="radio" name="featured_image_id" value="{{ $image->id }}" {{ $image->is_featured ? 'checked' : '' }} class="form-check-input" />
                Featured
            </label>
        </div>
        <a href="{{route('products.image.destroy',['id'=>$image->id])}}" class="btn btn-xs btn-danger waves-effect waves-light position-absolute top-0 end-0"><i class="mdi mdi-close"></i></a>
        @if($image->is_featured)
        <span class="badge bg-success position-absolute top-0 start-0">★</span>
        @endif
    </div>
    @endforeach
</div>
```

**Step 2: Add separate featured image upload for adding new featured image (after gallery section)**

```blade
<div class="col-sm-4 mb-3 mt-3">
    <label for="featured_image">Or Upload New Featured Image</label>
    <div class="input-group">
        <input type="file" name="featured_image" class="form-control" accept="image/*" />
    </div>
    <small class="text-muted">Upload a new image as featured (overrides selection above).</small>
</div>
```

**Step 3: Commit**

```bash
git add resources/views/backEnd/product/edit.blade.php
git commit -m "feat(product): add featured image selection radio buttons to edit form"
```

---

## Task 6: Update ProductStoreRequest Validation

**Objective:** Add validation for `featured_image` field.

**Files:**
- Modify: `app/Http/Requests/Admin/ProductStoreRequest.php`

**Step 1: Add validation rule for `featured_image`**

```php
'featured_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
```

**Step 2: Commit**

```bash
git add app/Http/Requests/Admin/ProductStoreRequest.php
git commit -m "feat(product): add featured_image validation rule"
```

---

## Task 7: Update ProductController — Store Action

**Objective:** Handle `featured_image` upload in store, save with `is_featured = true`.

**Files:**
- Modify: `app/Http/Controllers/Admin/ProductController.php` (store method, ~line 102-131)

**Step 1: After existing image upload loop (after line 127), add featured image handling**

```php
// Featured image upload
$featuredImage = $request->file('featured_image');
if ($featuredImage) {
    $name = time() . '-featured-' . Str::random(10) . '.' . $featuredImage->getClientOriginalExtension();
    $uploadPath = 'uploads/product/';
    $featuredImage->move($uploadPath, $name);
    Productimage::create([
        'product_id' => $product->id,
        'image' => $uploadPath . $name,
        'is_featured' => true,
    ]);
}
```

**Step 2: Commit**

```bash
git add app/Http/Controllers/Admin/ProductController.php
git commit -m "feat(product): handle featured_image upload in store action"
```

---

## Task 8: Update ProductController — Update Action

**Objective:** Handle `featured_image_id` radio selection and new `featured_image` upload in update.

**Files:**
- Modify: `app/Http/Controllers/Admin/ProductController.php` (update method, ~line 220-253)

**Step 1: After existing image upload logic (after line 248), add featured image handling**

```php
// Handle featured image selection from existing gallery
if ($request->filled('featured_image_id')) {
    // Reset all featured for this product
    Productimage::where('product_id', $update_data->id)->update(['is_featured' => false]);
    // Set selected as featured
    Productimage::where('id', $request->featured_image_id)
        ->where('product_id', $update_data->id)
        ->update(['is_featured' => true]);
}

// Handle new featured image upload (overrides selection)
$newFeatured = $request->file('featured_image');
if ($newFeatured) {
    // Reset existing featured
    Productimage::where('product_id', $update_data->id)->update(['is_featured' => false]);

    $name = time() . '-featured-' . Str::random(10) . '.' . $newFeatured->getClientOriginalExtension();
    $name = strtolower(preg_replace('/\s+/', '-', $name));
    $uploadPath = 'uploads/product/';
    $newFeatured->move($uploadPath, $name);
    Productimage::create([
        'product_id' => $update_data->id,
        'image' => $uploadPath . $name,
        'is_featured' => true,
    ]);
}
```

**Step 2: Commit**

```bash
git add app/Http/Controllers/Admin/ProductController.php
git commit -m "feat(product): handle featured image selection and upload in update action"
```

---

## Task 9: Update ProductUpdateRequest Validation

**Objective:** Add validation for `featured_image_id` and `featured_image` fields.

**Files:**
- Modify: `app/Http/Requests/Admin/ProductUpdateRequest.php`

**Step 1: Add validation rules**

```php
'featured_image_id' => 'nullable|integer|exists:productimages,id',
'featured_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
```

**Step 2: Commit**

```bash
git add app/Http/Requests/Admin/ProductUpdateRequest.php
git commit -m "feat(product): add featured image validation to update request"
```

---

## Task 10: Update Frontend — Product Card (Listings)

**Objective:** Show featured image in product cards, fallback to first gallery image.

**Files:**
- Modify: `resources/views/frontEnd/layouts/partials/product_card.blade.php` (line 17)

**Step 1: Change image source**

Replace:
```blade
<img src="{{ asset($value->image ? $value->image->image : '') }}"
```

With:
```blade
<img src="{{ asset(($value->featuredImage ?? $value->image)?->image ?? '') }}"
```

**Step 2: Commit**

```bash
git add resources/views/frontEnd/layouts/partials/product_card.blade.php
git commit -m "feat(product): use featured image in product card with fallback"
```

---

## Task 11: Update Frontend — Product Details Page

**Objective:** Show featured image first in the carousel, with visual indicator.

**Files:**
- Modify: `resources/views/frontEnd/layouts/pages/details.blade.php` (~line 108-122)

**Step 1: Sort images so featured comes first**

Replace:
```blade
@foreach ($details->images as $value)
    <div class="dimage_item">
        <img src="{{ asset($value->image) }}" class="block__pic" />
    </div>
@endforeach
```

With:
```blade
@php
    $sortedImages = $details->images->sortByDesc('is_featured')->values();
@endphp
@foreach ($sortedImages as $value)
    <div class="dimage_item">
        <img src="{{ asset($value->image) }}" class="block__pic" />
    </div>
@endforeach
```

And same for the thumbnail indicator section (lines 117-121):
```blade
@foreach ($sortedImages as $key => $image)
    <div class="indicator-item {{ $image->is_featured ? 'active' : '' }}" data-id="{{ $key }}">
        <img src="{{ asset($image->image) }}" />
    </div>
@endforeach
```

**Step 2: Update meta tags (og:image, twitter:image) to prefer featured**

Replace (line ~16, 22):
```blade
<meta name="twitter:image" content="{{ $details->image ? asset($details->image->image) : '' }}" />
<meta property="og:image" content="{{ $details->image ? asset($details->image->image) : '' }}" />
```

With:
```blade
<meta name="twitter:image" content="{{ asset(($details->featuredImage ?? $details->image)?->image ?? '') }}" />
<meta property="og:image" content="{{ asset(($details->featuredImage ?? $details->image)?->image ?? '') }}" />
```

**Step 3: Commit**

```bash
git add resources/views/frontEnd/layouts/pages/details.blade.php
git commit -m "feat(product): prioritize featured image in details page and meta tags"
```

---

## Task 12: Update Other Frontend References

**Objective:** Update search results, quickview, and any other places using `$product->image`.

**Files:**
- Modify: `resources/views/frontEnd/layouts/ajax/search.blade.php` (line 8)
- Modify: `resources/views/frontEnd/layouts/ajax/quickview.blade.php` (line 17)
- Modify: `app/Http/Controllers/Frontend/ShoppingController.php` (line 71, 146)
- Modify: `app/Http/Controllers/Frontend/FrontendController.php` (line 373)

**Step 1: Update search.blade.php**

Replace:
```blade
<img src="{{ asset($value->image ? $value->image->image : '') }}"
```
With:
```blade
<img src="{{ asset(($value->featuredImage ?? $value->image)?->image ?? '') }}"
```

**Step 2: Update quickview.blade.php**

Replace:
```blade
<img src="{{ asset($product->image ? $product->image->image : '') }}"
```
With:
```blade
<img src="{{ asset(($product->featuredImage ?? $product->image)?->image ?? '') }}"
```

**Step 3: Update ShoppingController.php — add `featuredImage` to eager loads**

Line 71 and 146: add `featuredImage` to the `with()` calls.

**Step 4: Update FrontendController.php — add `featuredImage` to eager loads**

Line 373: update to use `featuredImage` fallback.

**Step 5: Commit**

```bash
git add resources/views/frontEnd/layouts/ajax/ app/Http/Controllers/Frontend/
git commit -m "feat(product): use featured image in all frontend views and controllers"
```

---

## Task 13: Handle Featured Image Deletion

**Objective:** When a featured image is deleted from gallery, auto-promote next image.

**Files:**
- Modify: `app/Http/Controllers/Admin/ProductController.php` (imgdestroy method, ~line 278)

**Step 1: After deleting the image, check if it was featured and promote next**

```php
public function imgdestroy(Request $request)
{
    $delete_data = Productimage::find($request->id);
    if (!$delete_data) {
        Toastr::error('Error', 'Image not found');
        return redirect()->back();
    }

    $productId = $delete_data->product_id;
    $wasFeatured = $delete_data->is_featured;

    File::delete($delete_data->image);
    $delete_data->delete();

    // If deleted image was featured, promote the next available image
    if ($wasFeatured) {
        $nextImage = Productimage::where('product_id', $productId)->first();
        if ($nextImage) {
            $nextImage->update(['is_featured' => true]);
        }
    }

    Toastr::success('Success', 'Image deleted successfully');
    return redirect()->back();
}
```

**Step 2: Commit**

```bash
git add app/Http/Controllers/Admin/ProductController.php
git commit -m "feat(product): auto-promote next image when featured image is deleted"
```

---

## Task 14: Update Product Index — Show Featured Badge

**Objective:** In admin product listing, show a star badge on products that have a featured image set.

**Files:**
- Modify: `resources/views/backEnd/product/index.blade.php`

**Step 1: Find the image column in the product table and add featured indicator**

In the product table row where images are shown, add a small star icon if the product has a featured image:

```blade
@if($value->image)
    <img src="{{ asset($value->featuredImage?->image ?? $value->image->image) }}" width="40" class="rounded" />
    @if($value->featuredImage)
        <span class="badge bg-success" style="font-size:10px;">★</span>
    @endif
@endif
```

**Step 2: Commit**

```bash
git add resources/views/backEnd/product/index.blade.php
git commit -m "feat(product): show featured image badge in admin product listing"
```

---

## Task 15: Eager Load Optimization

**Objective:** Add `featuredImage` to eager loads in ProductController index and other queries to avoid N+1.

**Files:**
- Modify: `app/Http/Controllers/Admin/ProductController.php` (index method, line 57)

**Step 1: Add `featuredImage` to the `with()` call**

```php
$query = Product::orderBy('id', 'DESC')->with('image', 'featuredImage', 'category');
```

**Step 2: Commit**

```bash
git add app/Http/Controllers/Admin/ProductController.php
git commit -m "perf(product): eager load featuredImage to avoid N+1 queries"
```

---

## Task 16: Run Full Test Suite & Manual Verification

**Objective:** Verify no regressions and feature works end-to-end.

**Step 1: Run tests**

```bash
php artisan test
```

Expected: All existing tests pass (zero regressions).

**Step 2: Manual verification checklist**

1. Create new product → upload gallery images → upload featured image → save
2. Edit product → see gallery with radio buttons → select different featured → save → verify change
3. Frontend product card → shows featured image
4. Frontend product details → featured image appears first in carousel
5. Delete featured image from gallery → next image auto-promoted
6. Product with no featured image → falls back to first gallery image (existing behavior preserved)

**Step 3: Commit any fixes**

---

## Summary of Files Changed

| File | Change Type |
|------|-------------|
| `database/migrations/*_add_is_featured_to_productimages_table.php` | CREATE |
| `app/Models/Productimage.php` | MODIFY (fillable, casts) |
| `app/Models/Product.php` | MODIFY (add featuredImage relation) |
| `app/Http/Controllers/Admin/ProductController.php` | MODIFY (store, update, imgdestroy, index) |
| `app/Http/Requests/Admin/ProductStoreRequest.php` | MODIFY (validation) |
| `app/Http/Requests/Admin/ProductUpdateRequest.php` | MODIFY (validation) |
| `resources/views/backEnd/product/create.blade.php` | MODIFY (featured image upload) |
| `resources/views/backEnd/product/edit.blade.php` | MODIFY (radio selection + upload) |
| `resources/views/backEnd/product/index.blade.php` | MODIFY (featured badge) |
| `resources/views/frontEnd/layouts/partials/product_card.blade.php` | MODIFY (featured image) |
| `resources/views/frontEnd/layouts/pages/details.blade.php` | MODIFY (sort + meta) |
| `resources/views/frontEnd/layouts/ajax/search.blade.php` | MODIFY (featured image) |
| `resources/views/frontEnd/layouts/ajax/quickview.blade.php` | MODIFY (featured image) |
| `app/Http/Controllers/Frontend/ShoppingController.php` | MODIFY (eager load) |
| `app/Http/Controllers/Frontend/FrontendController.php` | MODIFY (eager load) |

## Risks & Mitigations

| Risk | Mitigation |
|------|------------|
| Existing products have no featured image | Fallback to `$product->image` (first gallery image) preserves current behavior |
| Admin deletes featured image | Auto-promote next gallery image (Task 13) |
| N+1 queries from new relation | Eager load `featuredImage` everywhere (Task 15) |
| Large image uploads | Validation limits to 2MB, standard image types only |
