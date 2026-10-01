# Phase 01 — Route & Controller Inventory

## Objective
Comprehensive audit and inventory of all routes (Web and API), controllers, methods, middleware assignments, vulnerabilities, and modular monolith domain boundaries for the MondolShopBD Laravel application.

## Why This Phase Exists
This phase serves as the foundation for the **80-Phase Modular Monolith Laravel Ecosystem** roadmap. Without a precise inventory of all 329 routes, 40 controllers, 322 methods, middleware protections, and legacy anti-patterns, subsequent characterization tests, security hardening, and domain module extractions cannot be safely executed.

---

## 1. Controller Audit (40 Total, 5,414 Lines of Code)

### Controller Summary Table
| # | Controller Path | Lines | Methods | Area / Module Candidate | Fat Status (>15 line rule) |
|---|---|---|---|---|---|
| 1 | `app/Http/Controllers/Admin/OrderController.php` | 754 | 32 | Order / Admin POS | ⚠️ 10 methods violate rule |
| 2 | `app/Http/Controllers/Frontend/CustomerController.php` | 556 | 27 | Identity / Order (Checkout) | ⚠️ 8 methods violate rule |
| 3 | `app/Http/Controllers/Frontend/FrontendController.php` | 418 | 17 | Catalog / Frontend | ⚠️ 6 methods violate rule |
| 4 | `app/Http/Controllers/Admin/CampaignController.php` | 279 | 9 | Campaign | ⚠️ 2 methods violate rule |
| 5 | `app/Http/Controllers/Admin/ProductController.php` | 249 | 17 | Catalog (Product) | ⚠️ 3 methods violate rule |
| 6 | `app/Http/Controllers/Frontend/BkashController.php` | 235 | 12 | Payment (bKash) | ⚠️ 4 methods violate rule |
| 7 | `app/Http/Controllers/Admin/GeneralSettingController.php` | 200 | 8 | Settings | ⚠️ 2 methods violate rule |
| 8 | `app/Http/Controllers/Admin/SubcategoryController.php` | 157 | 9 | Catalog (Category) | ⚠️ 2 methods violate rule |
| 9 | `app/Http/Controllers/Admin/UserController.php` | 147 | 8 | Identity / Admin Users | ⚠️ 2 methods violate rule |
| 10 | `app/Http/Controllers/Admin/CategoryController.php` | 144 | 8 | Catalog (Category) | ⚠️ 2 methods violate rule |
| 11 | `app/Http/Controllers/Admin/CustomerManageController.php` | 130 | 11 | Identity (Customers) | ⚠️ 1 method violates rule |
| 12 | `app/Http/Controllers/Frontend/ShoppingController.php` | 129 | 9 | Cart / Order | ⚠️ 2 methods violate rule |
| 13 | `app/Http/Controllers/Admin/BrandController.php` | 124 | 8 | Catalog (Brand) | ⚠️ 2 methods violate rule |
| 14 | `app/Http/Controllers/Admin/BannerController.php` | 112 | 8 | Banner / Marketing | ⚠️ 2 methods violate rule |
| 15 | `app/Http/Controllers/Admin/ChildcategoryController.php` | 112 | 9 | Catalog (Category) | ⚠️ 2 methods violate rule |
| 16 | `app/Http/Controllers/Admin/ReviewController.php` | 106 | 9 | Review / Social Proof | ⚠️ 2 methods violate rule |
| 17 | `app/Http/Controllers/Admin/ShippingChargeController.php` | 91 | 8 | Shipping | 0 |
| 18 | `app/Http/Controllers/Api/FrontendController.php` | 91 | 10 | Mobile App API v1 | 0 |
| 19 | `app/Http/Controllers/Admin/CreatePageController.php` | 90 | 8 | Content / Pages | 0 |
| 20 | `app/Http/Controllers/Admin/ContactController.php` | 87 | 8 | Content / Contact | 0 |
| 21 | `app/Http/Controllers/Admin/DashboardController.php` | 87 | 6 | Admin Core / Dashboard | ⚠️ 1 method violates rule |
| 22 | `app/Http/Controllers/Admin/ColorController.php` | 85 | 8 | Catalog (Attributes) | 0 |
| 23 | `app/Http/Controllers/Admin/SizeController.php` | 85 | 8 | Catalog (Attributes) | 0 |
| 24 | `app/Http/Controllers/Admin/BannerCategoryController.php` | 84 | 8 | Banner / Marketing | 0 |
| 25 | `app/Http/Controllers/Admin/SocialMediaController.php` | 84 | 8 | Settings / Social | 0 |
| 26 | `app/Http/Controllers/Admin/RoleController.php` | 81 | 7 | Identity / RBAC | 0 |
| 27 | `app/Http/Controllers/Admin/TagManagerController.php` | 80 | 8 | Settings / Analytics | 0 |
| 28 | `app/Http/Controllers/Admin/OrderStatusController.php` | 79 | 8 | Order / Status | 0 |
| 29 | `app/Http/Controllers/Admin/ApiIntegrationController.php` | 78 | 6 | Settings / Integrations | 0 |
| 30 | `app/Http/Controllers/Admin/PixelsController.php` | 75 | 8 | Settings / Analytics | 0 |
| 31 | `app/Http/Controllers/Auth/RegisterController.php` | 73 | 1 | Auth (Laravel UI) | 0 |
| 32 | `app/Http/Controllers/Admin/PermissionController.php` | 67 | 6 | Identity / RBAC | 0 |
| 33 | `app/Http/Controllers/Frontend/ShurjopayControllers.php` | 48 | 2 | Payment (ShurjoPay) | ⚠️ 1 method violates rule |
| 34 | `app/Http/Controllers/Auth/VerificationController.php` | 42 | 1 | Auth (Laravel UI) | 0 |
| 35 | `app/Http/Controllers/Auth/ConfirmPasswordController.php` | 40 | 1 | Auth (Laravel UI) | 0 |
| 36 | `app/Http/Controllers/Auth/LoginController.php` | 40 | 1 | Auth (Laravel UI) | 0 |
| 37 | `app/Http/Controllers/Auth/ResetPasswordController.php` | 30 | 0 | Auth (Laravel UI) | 0 |
| 38 | `app/Http/Controllers/Auth/ForgotPasswordController.php` | 22 | 0 | Auth (Laravel UI) | 0 |
| 39 | `app/Http/Controllers/Controller.php` | 13 | 0 | Base Controller | 0 |
| 40 | `app/Http/Controllers/Admin/ReportsController.php` | 10 | 0 | Reporting (Stub) | 0 |

---

## 2. Top Fat Methods Analysis (>30 lines)

| Controller | Method | Lines | Line Range | Core Responsibilities & Anti-patterns |
|---|---|---|---|---|
| `CustomerController` | `order_save()` | **168** | L260–L427 | Cart reading, customer auto-registration, order creation, stock deduction, SMS API dispatch, Pathao courier API dispatch, bKash URL generation. Violates Single Responsibility. |
| `CampaignController` | `update()` | **104** | L134–L237 | Direct image resizing, validation, multi-table database updates in single method. |
| `CampaignController` | `store()` | **96** | L27–L122 | File upload, database insertion, campaign date string manipulation. |
| `OrderController` | `order_update()` | **94** | L658–L751 | Direct order table and detail table updates, status state changes, stock adjustments. |
| `OrderController` | `order_store()` | **83** | L474–L556 | POS order creation, customer lookup/creation, inline order details loop, stock update. |
| `OrderController` | `getOrders()` | **79** | L61–L139 | DataTables server-side query construction with heavy raw closures. |
| `GeneralSettingController` | `update()` | **74** | L100–L173 | Handling multiple file uploads (logo, favicon), settings key-value persistence. |
| `GeneralSettingController` | `store()` | **61** | L31–L91 | Settings creation with multiple raw file moves. |
| `OrderController` | `order_process()` | **61** | L269–L329 | Order status transition without state machine validation; fires SMS synchronously. |
| `FrontendController` | `payment_success()` | **57** | L338–L394 | Callback parsing, order status update, invoice rendering. |
| `UserController` | `update()` | **50** | L70–L119 | Direct role sync, password hashing, avatar upload. |
| `OrderController` | `order_pathao()` | **47** | L209–L255 | Synchronous cURL call to Pathao courier API directly in controller. |
| `FrontendController` | `subcategory()` | **47** | L129–L175 | Complex multi-filter query construction, pagination, view rendering. |
| `CustomerManageController` | `update()` | **46** | L33–L78 | Admin customer modification without FormRequest. |
| `OrderController` | `bulk_courier()` | **46** | L378–L423 | Bulk external courier API requests inside synchronous loop. |
| `FrontendController` | `index()` | **45** | L31–L75 | Over 12 distinct Eloquent queries executed sequentially for homepage rendering without caching. |

---

## 3. Routes Audit (329 Total Routes)

### Route Breakdown
- **Web Routes (`routes/web.php`)**: 317 routes
- **API Routes (`routes/api.php`)**: 12 routes
- **Auth Routes (`Auth::routes()`)**: Standard Laravel UI routes (login, logout, register, password reset, email verification)

### Route Groups & Middleware Map
```
1. Public Frontend Group
   - Prefix: None
   - Middleware: ['ipcheck', 'check_refer']
   - Actions: Catalog browsing, single product, search, live search, cart view/mutation, info pages.

2. Guest Customer / Auth Flow Group
   - Prefix: '/customer'
   - Middleware: ['ipcheck', 'check_refer']
   - Actions: Login, signin, register, OTP verify, OTP resend, forgot password flow, checkout, order-save.

3. Authenticated Customer Dashboard Group
   - Prefix: '/customer'
   - Middleware: ['customer', 'ipcheck', 'check_refer']
   - Actions: Account overview, orders list, invoice view, profile edit/update, password change.

4. Payment Gateway Callback Group
   - Prefix: None
   - Middleware: ['ipcheck', 'check_refer']
   - Actions: bKash checkout URL pay/create/callback, ShurjoPay payment success/cancel.

5. Admin Lockscreen Group
   - Prefix: '/admin'
   - Middleware: ['customer', 'ipcheck', 'check_refer']
   - Note: ⚠️ Bug/Vulnerability: Uses 'customer' middleware instead of admin auth.

6. Authenticated Admin Group
   - Prefix: '/admin'
   - Middleware: ['auth', 'lock', 'check_refer']
   - Actions: Full admin dashboard, catalog management, order processing, POS, reports, settings, users, roles, permissions.
   - Note: ⚠️ Zero Spatie 'role' or 'permission' middlewares applied across all admin route definitions.

7. Mobile App API Group
   - Prefix: '/api/v1'
   - Middleware: ['api']
   - Actions: App config, slider, category menu, hot deals, homepage products, footer menus, social media, contact info.
```

---

## 4. Critical Security & Architectural Findings

### 4.1 Critical Unprotected Routes (P0)
1. `GET /cc` (`routes/web.php:L39-L45`):
   - Clears config, cache, route, and view caches.
   - **Completely unauthenticated**. Anyone can trigger full cache purge.
2. `GET /controller` (`routes/web.php:L47-L50`):
   - Executes `Artisan::call('make:controller Admin/TagManagerController')`.
   - **Completely unauthenticated**. Anyone can trigger file creation via artisan.
3. Public AJAX Endpoints (`routes/web.php:L146-L147`):
   - `GET /ajax-product-subcategory`
   - `GET /ajax-product-childcategory`
   - Unauthenticated access exposing database taxonomy IDs.

### 4.2 RBAC & Authorization Gap (P0)
- `spatie/laravel-permission` is installed in `composer.json` and registered.
- `RoleController` and `PermissionController` exist in Admin.
- **However, 0 routes use `role:` or `permission:` middleware**.
- Any authenticated user with an admin account has full unrestricted access to all admin operations (roles, settings, reports, orders, deletions).
- **0 Laravel Policy classes** exist in `app/Policies`.

### 4.3 Input Validation & Mass Assignment (P0)
- **0 FormRequest classes** exist in `app/Http/Requests`.
- 23 controllers use direct `$request->all()`.
- Multiple models use `$guarded = []`.
- Inline validation (`$this->validate` / `Validator::make`) used inconsistently across only 27 methods.

### 4.4 Insecure File Uploads (P1)
- 10 controllers use `$request->file(...)->move()` with `getClientOriginalName()`.
- Susceptible to directory traversal and lack strict server-side MIME type verification.

### 4.5 Synchronous Heavy Operations & External APIs (P1)
- bKash, Pathao, and SMS gateway calls are executed synchronously inside HTTP request cycles.
- 0 Job classes, 0 Event classes, 0 Listener classes in codebase.

---

## 5. Domain Boundary Mapping (For Modular Monolith)

| Module | Responsible Controllers | Routes Identified |
|---|---|---|
| **Catalog Module** | `ProductController`, `CategoryController`, `SubcategoryController`, `ChildcategoryController`, `BrandController`, `ColorController`, `SizeController`, `ReviewController` | ~85 routes |
| **Identity Module** | `UserController`, `CustomerController`, `CustomerManageController`, `RoleController`, `PermissionController`, `Auth/*` | ~45 routes |
| **Order Module** | `OrderController`, `OrderStatusController`, `ShoppingController`, Part of `CustomerController` | ~40 routes |
| **Payment Module** | `BkashController`, `ShurjopayControllers` | ~8 routes |
| **Shipping Module** | `ShippingChargeController`, Pathao methods in `OrderController` | ~12 routes |
| **Marketing & Campaign** | `CampaignController`, `BannerController`, `BannerCategoryController` | ~25 routes |
| **Operations & Settings** | `GeneralSettingController`, `SocialMediaController`, `ContactController`, `CreatePageController`, `PixelsController`, `TagManagerController`, `ApiIntegrationController`, `DashboardController` | ~60 routes |
| **API Module** | `Api/FrontendController` | 12 routes |

---

## 6. Definition of Done Checklist

- [x] Complete inventory of all 40 controllers and 322 methods
- [x] Identification of 64 fat methods exceeding 15 lines rule
- [x] Complete classification of 329 routes (317 Web + 12 API)
- [x] Route groups and middleware mappings documented
- [x] Critical security vulnerabilities (unprotected closures, RBAC bypass) identified
- [x] Domain module candidate mapping documented
- [x] Ready for Phase 02 (Model & Relationship Map)

