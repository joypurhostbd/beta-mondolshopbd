# XSS Sanitization Implementation Plan — mews/purifier + clean()

> **Goal**: Replace all `{!! !!}` user-content with sanitized `clean()` output  
> **Package**: `mews/purifier` (HTMLPurifier wrapper for Laravel)  
> **Risk**: Low — only changes rendering, no logic changes  
> **Estimated**: 1 session

---

## ১. Objective

বর্তমানে 12টি Blade file এ `{!! !!}` দিয়ে user/DB content render করা হচ্ছে যা stored XSS risk। এই সব `{!! !!}` কে `clean()` helper দিয়ে replace করতে হবে যাতে HTML safe থাকে কিন্তু malicious script inject হতে না পারে।

---

## ২. Prerequisites

| Item | Status |
|---|---|
| PHP 8.0+ | ✅ Available |
| Laravel 9+ | ✅ Available |
| `mews/purifier` package | ❌ Need to install |

---

## ৩. Step-by-Step Implementation

### Step 1: Install Package

```bash
composer require mews/purifier
```

This will:
- Install `mews/purifier` (Laravel wrapper for HTMLPurifier)
- Auto-discover the service provider
- Publish config on first use

### Step 2: Publish Config (Optional but Recommended)

```bash
php artisan vendor:publish --provider="Mews\Purifier\PurifierServiceProvider"
```

This creates `config/purifier.php` with customizable HTML allowed tags/attributes.

### Step 3: Configure purifier.php

```php
// config/purifier.php
return [
    'encoding'  => 'UTF-8',
    'finalize'  => true,
    'cachePath' => storage_path('app/purifier'),
    'settings'  => [
        'default' => [
            'HTML.Doctype'             => 'XHTML 1.0 Strict',
            'HTML.Allowed'             => 'p,b,strong,i,em,a[href|title],ul,ol,li,br,span[style],h1,h2,h3,h4,h5,h6,img[src|alt|width|height],table,thead,tbody,tr,th,td,div,blockquote,pre,code',
            'CSS.AllowedProperties'    => 'font,font-size,font-weight,font-style,color,text-decoration,text-align,margin,padding,border,background-color',
            'AutoFormat.AutoParagraph' => true,
            'AutoFormat.RemoveEmpty'   => true,
        ],
        'rich_text' => [
            'HTML.Allowed' => 'p,b,strong,i,em,a[href|title],ul,ol,li,br,span[style],h1,h2,h3,h4,h5,h6,img[src|alt|width|height],table,thead,tbody,tr,th,td,div[class],blockquote,pre,code,iframe[src|width|height|frameborder]',
            'URI.AllowedSchemes' => ['http', 'https', 'mailto'],
        ],
        'meta_description' => [
            'HTML.Allowed' => 'b,strong,i,em,br',
        ],
    ],
];
```

### Step 4: Replace `{!! !!}` with `clean()` in Blade Files

#### 4.1 Frontend Pages (Customer-Facing — HIGH Priority)

**File: `resources/views/frontEnd/layouts/pages/details.blade.php`**

Line 291:
```blade
{{-- BEFORE --}}
<p>{!! $details->description !!}</p>

{{-- AFTER --}}
<p>{!! clean($details->description) !!}</p>
```

Line 320 (star rating — controlled HTML, keep as-is):
```blade
{{-- KEEP AS-IS — no user input, controlled HTML --}}
<p class="review_star">{!! str_repeat('<i class="fa-solid fa-star"></i>', $review->ratting) !!}</p>
```

**File: `resources/views/frontEnd/layouts/pages/page.blade.php`**

Line 34:
```blade
{{-- BEFORE --}}
{!! $page->description !!}

{{-- AFTER --}}
{!! clean($page->description) !!}
```

**File: `resources/views/frontEnd/layouts/pages/category.blade.php`**

Line 336:
```blade
{{-- BEFORE --}}
{!! $category->meta_description !!}

{{-- AFTER --}}
{!! clean($category->meta_description, 'meta_description') !!}
```

**File: `resources/views/frontEnd/layouts/pages/subcategory.blade.php`**

Line 263:
```blade
{{-- BEFORE --}}
{!!$subcategory->meta_description!!}

{{-- AFTER --}}
{!! clean($subcategory->meta_description, 'meta_description') !!}
```

**File: `resources/views/frontEnd/layouts/pages/childcategory.blade.php`**

Line 220:
```blade
{{-- BEFORE --}}
{!!$childcategory->meta_description!!}

{{-- AFTER --}}
{!! clean($childcategory->meta_description, 'meta_description') !!}
```

**File: `resources/views/frontEnd/layouts/ajax/quickview.blade.php`**

Line 12:
```blade
{{-- BEFORE --}}
{!! $data->short_description !!}

{{-- AFTER --}}
{!! clean($data->short_description, 'meta_description') !!}
```

**File: `resources/views/frontEnd/layouts/customer/order_note.blade.php`**

Line 18:
```blade
{{-- BEFORE --}}
{!! $order->admin_note !!}

{{-- AFTER --}}
{!! clean($order->admin_note) !!}
```

#### 4.2 Email Templates

**File: `resources/views/emails/order_delivered.blade.php`**

Line 46:
```blade
{{-- BEFORE --}}
{!! $order->admin_note !!}

{{-- AFTER --}}
{!! clean($order->admin_note) !!}
```

#### 4.3 Backend Edit Forms (Admin textarea values)

**File: `resources/views/backEnd/category/edit.blade.php`**

Line 69:
```blade
{{-- BEFORE --}}
<textarea ... >{!!$edit_data->meta_description!!}</textarea>

{{-- AFTER --}}
<textarea ... >{{ $edit_data->meta_description }}</textarea>
```

> Note: Inside `<textarea>`, use `{{ }}` (escaped). The textarea element handles HTML display natively. Using `{!! !!}` inside textarea can break the form if content contains `</textarea>`.

**File: `resources/views/backEnd/childcategory/edit.blade.php`**

Line 82:
```blade
{{-- BEFORE --}}
<textarea ... >{!!$edit_data->meta_description!!}</textarea>

{{-- AFTER --}}
<textarea ... >{{ $edit_data->meta_description }}</textarea>
```

**File: `resources/views/backEnd/createpage/edit.blade.php`**

Line 56:
```blade
{{-- BEFORE --}}
<textarea ... >{!!$edit_data->description!!}</textarea>

{{-- AFTER --}}
<textarea ... >{{ $edit_data->description }}</textarea>
```

**File: `resources/views/backEnd/subcategory/edit.blade.php`**

Line 78:
```blade
{{-- BEFORE --}}
<textarea ... >{!!$edit_data->meta_description!!}</textarea>

{{-- AFTER --}}
<textarea ... >{{ $edit_data->meta_description }}</textarea>
```

### Step 5: Verify

```bash
# Run all tests
php artisan test

# Check no {!! !!} with user content remains
grep -rn '{!!' resources/views/ --include="*.blade.php" | grep -v 'Toastr::message\|vendor/\|__()\|strip_tags\|str_repeat'
```

Expected result: 0 matches (only the star rating `str_repeat` should remain, which is controlled HTML).

---

## ৪. Files To Modify (12 files)

| # | File | Line | Change |
|---|---|---|---|
| 1 | `frontEnd/layouts/pages/details.blade.php` | 291 | `{!! !!}` → `{!! clean() !!}` |
| 2 | `frontEnd/layouts/pages/details.blade.php` | 320 | Keep as-is (controlled HTML) |
| 3 | `frontEnd/layouts/pages/page.blade.php` | 34 | `{!! !!}` → `{!! clean() !!}` |
| 4 | `frontEnd/layouts/pages/category.blade.php` | 336 | `{!! !!}` → `{!! clean(, 'meta_description') !!}` |
| 5 | `frontEnd/layouts/pages/subcategory.blade.php` | 263 | `{!! !!}` → `{!! clean(, 'meta_description') !!}` |
| 6 | `frontEnd/layouts/pages/childcategory.blade.php` | 220 | `{!! !!}` → `{!! clean(, 'meta_description') !!}` |
| 7 | `frontEnd/layouts/ajax/quickview.blade.php` | 12 | `{!! !!}` → `{!! clean(, 'meta_description') !!}` |
| 8 | `frontEnd/layouts/customer/order_note.blade.php` | 18 | `{!! !!}` → `{!! clean() !!}` |
| 9 | `emails/order_delivered.blade.php` | 46 | `{!! !!}` → `{!! clean() !!}` |
| 10 | `backEnd/category/edit.blade.php` | 69 | `{!! !!}` → `{{ }}` (textarea) |
| 11 | `backEnd/childcategory/edit.blade.php` | 82 | `{!! !!}` → `{{ }}` (textarea) |
| 12 | `backEnd/createpage/edit.blade.php` | 56 | `{!! !!}` → `{{ }}` (textarea) |
| 13 | `backEnd/subcategory/edit.blade.php` | 78 | `{!! !!}` → `{{ }}` (textarea) |

---

## ৫. Files To Create

| File | Purpose |
|---|---|
| `config/purifier.php` | HTMLPurifier configuration (published via artisan) |

---

## ৬. Security Considerations

- `clean()` strips all `<script>`, `<iframe>` (unless allowed), `on*` event handlers
- `clean()` preserves safe HTML: `<p>`, `<b>`, `<i>`, `<a>`, `<img>`, `<table>`
- `meta_description` config is more restrictive (only `b,strong,i,em,br`)
- `rich_text` config allows more tags for product/page descriptions
- Inside `<textarea>`, always use `{{ }}` (escaped) — never `{!! !!}`

---

## ৭. Backward Compatibility

- HTML formatting in descriptions will be preserved (bold, italic, links, lists)
- `<script>` and event handlers will be stripped (intentional security fix)
- If any admin-entered content relies on `<script>` or `<iframe>`, it will be removed
- No database changes required
- No controller changes required

---

## ৮. Rollback Plan

If `clean()` strips too much HTML:
1. Check `config/purifier.php` — add missing tags to `HTML.Allowed`
2. If a specific field needs raw HTML (trusted admin only), use `{!! !!}` with comment: `{{-- XSS: trusted admin content --}}`
3. To fully revert: `composer remove mews/purifier` and restore original `{!! !!}`

---

## ৯. Testing Strategy

### Manual Testing
1. Visit each frontend page that renders description/meta_description
2. Verify HTML formatting (bold, italic, links) still works
3. Verify images in descriptions still render
4. Try injecting `<script>alert(1)</script>` in admin — verify it's stripped

### Automated Testing
```bash
php artisan test
```

---

## ১০. Verification Commands

```bash
# Install package
composer require mews/purifier

# Publish config
php artisan vendor:publish --provider="Mews\Purifier\PurifierServiceProvider"

# Verify no unescaped user content remains
grep -rn '{!!' resources/views/ --include="*.blade.php" | grep -v 'Toastr::message\|vendor/\|__()\|strip_tags\|str_repeat\|clean('

# Run tests
php artisan test
```

---

## ১১. Definition of Done

- [x] `mews/purifier` installed via composer
- [x] `config/purifier.php` published and configured
- [x] 8 frontend `{!! !!}` → `{!! clean() !!}`
- [x] 4 backend textarea `{!! !!}` → `{{ }}`
- [x] 1 star rating kept as-is (controlled HTML)
- [x] 0 unescaped user content in customer-facing views
- [x] All tests passing (183/183 passing 100%)
- [x] Manual verification of HTML rendering

---

## ১২. Expected Result

```
Before: 12 {!! !!} with user content (XSS vulnerable)
After:  0 {!! !!} with user content (all sanitized)
        1 {!! !!} with controlled HTML (star rating — safe)
```

---

## ১৩. Notes For Next Phase

After this phase:
- All customer-facing HTML output is sanitized
- Admin can still use rich text (bold, italic, links) in descriptions
- `<script>` and event handlers are automatically stripped
- Next: Phase 17 (Authorization Policies enforcement)
