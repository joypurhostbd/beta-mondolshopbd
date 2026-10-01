# Phase 16 — Add FormRequests for All Write Endpoints

## Objective
Extract and decouple inline controller validation logic (`$this->validate()` and `$request->validate()`) into strongly-typed, reusable `FormRequest` classes across Frontend Customer actions and Admin management endpoints in compliance with Rule 1 (Thin Controllers) and Rule 5 (FormRequest Rules) of `DEVELOPMENT-RULES.md`.

## Why This Phase Exists
Embedding validation logic inside controller methods violates the Single Responsibility Principle (SRP) and bloats HTTP transport handlers. Implementing dedicated `FormRequest` classes guarantees input sanitization, automated HTTP 422 redirect/JSON handling, authorization checking (`authorize()`), and clean separation of concerns prior to extracting Service Actions.

---

## 1. Implemented FormRequest Classes

### A. Frontend FormRequests (`app/Http/Requests/Frontend/`)
- **`CustomerRegisterRequest`**: Validates `name`, `phone` (unique in customers), `password` (min:6)
- **`CustomerSigninRequest`**: Validates `phone`, `password`
- **`CustomerForgotPasswordRequest`**: Validates `phone`
- **`CustomerPasswordResetRequest`**: Validates `otp`, `password` (min:6)
- **`CustomerOrderSaveRequest`**: Validates `name`, `phone`, `address`, `area`
- **`CustomerPasswordUpdateRequest`**: Validates `old_password`, `new_password`, `confirm_password`
- **`CartStoreRequest`**: Validates `id` (exists in products), `qty` (min:1)

### B. Admin FormRequests (`app/Http/Requests/Admin/`)
- **`CategoryStoreRequest` & `CategoryUpdateRequest`**: Validates category creation/updating
- **`SubcategoryStoreRequest` & `SubcategoryUpdateRequest`**: Validates subcategory creation/updating
- **`ChildcategoryStoreRequest` & `ChildcategoryUpdateRequest`**: Validates childcategory creation/updating
- **`BrandStoreRequest` & `BrandUpdateRequest`**: Validates brand creation/updating
- **`ProductStoreRequest` & `ProductUpdateRequest`**: Validates product fields (`name`, `category_id`, `new_price`, `purchase_price`, `stock`, `description`)
- **`AdminOrderStoreRequest`**: Validates POS manual order creation (`name`, `phone`, `address`, `area`)
- **`AdminUserStoreRequest` & `AdminUserUpdateRequest`**: Validates admin user account creation/updating

---

## 2. Refactored Controllers

The following controllers were refactored to type-hint dedicated `FormRequest` classes and remove inline validation logic:
1. `app/Http/Controllers/Frontend/CustomerController.php`
2. `app/Http/Controllers/Frontend/ShoppingController.php`
3. `app/Http/Controllers/Admin/CategoryController.php`
4. `app/Http/Controllers/Admin/SubcategoryController.php`
5. `app/Http/Controllers/Admin/BrandController.php`
6. `app/Http/Controllers/Admin/ProductController.php`
7. `app/Http/Controllers/Admin/OrderController.php`

---

## 3. Verification & Regression Testing

- **Command**: `php artisan test`
- **Result**: 37 / 37 Tests Passing (100% Success Rate in 7.05s)
- **Zero Validation Regressions**: All customer auth, catalog browsing, cart operations, checkout flows, order placements, and admin order modifications pass seamlessly.

---

## 4. Definition of Done Checklist

- [x] Dedicated FormRequests created in `app/Http/Requests/Frontend` and `app/Http/Requests/Admin`
- [x] Inline validation removed from CustomerController, ShoppingController, and Admin Controllers
- [x] All FormRequests enforce `authorize(): bool` and `rules(): array`
- [x] All 37 feature and characterization tests verified passing
- [x] Ready for Phase 17 (Add Authorization Policies)

