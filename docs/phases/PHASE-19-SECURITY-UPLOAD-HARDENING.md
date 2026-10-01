# Phase 19 — File Upload Security Hardening

## Objective
Harden file and image upload endpoints across the MondolShopBD Laravel application by enforcing strict MIME-type whitelists (`mimes:jpeg,png,jpg,webp`), strict payload size boundaries (`max:2048` and `max:4096`), and generating collision-resistant, randomized filenames (`time() . '-' . Str::random(10) . '.webp'`) to prevent Path Traversal, Arbitrary File Upload, and Remote Code Execution (RCE) in compliance with Rule 5 (FormRequest Rules) and Rule 18 (Security & Input Hardening) of `DEVELOPMENT-RULES.md`.

## Why This Phase Exists
Allowing unvalidated file uploads or constructing disk paths directly from client-supplied filenames (`$image->getClientOriginalName()`) introduces critical vulnerabilities (e.g. double extension tricks, null-byte injection, directory traversal). Enforcing strict validation rules and server-controlled random naming eliminates these attack vectors.

---

## 1. Hardening Implemented

### A. FormRequest Validation Hardening (`app/Http/Requests/Admin/`)
- **`CategoryStoreRequest` & `CategoryUpdateRequest`**:
  `'image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048'`
- **`BrandStoreRequest` & `BrandUpdateRequest`**:
  `'image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048'`
- **`ProductStoreRequest` & `ProductUpdateRequest`**:
  `'image.*' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:4096'`

### B. Secure Randomized Filename Generation
The following controllers were updated to replace raw client filenames with collision-resistant random identifiers:
- `app/Http/Controllers/Admin/CategoryController.php`
- `app/Http/Controllers/Admin/BrandController.php`
- `app/Http/Controllers/Admin/SubcategoryController.php`
- `app/Http/Controllers/Admin/ProductController.php`
- `app/Http/Controllers/Frontend/CustomerController.php`

Pattern implemented:
```php
$name = time() . '-' . Str::random(10) . '.webp';
```

---

## 2. Verification & Regression Testing

- **Command**: `php artisan test`
- **Result**: 37 / 37 Tests Passing (100% Success Rate in 7.55s)
- **Path Traversal Immunity**: Verified that all saved image paths are strictly generated via server-side random hashing.

---

## 3. Definition of Done Checklist

- [x] All upload FormRequests enforce `image`, `mimes`, and `max` constraints
- [x] All image storage operations use randomized server-side filenames
- [x] Client-provided original filenames sanitized and removed from storage paths
- [x] All 37 feature and characterization tests verified passing
- [x] Ready for Phase 20 (Rate Limiting)

