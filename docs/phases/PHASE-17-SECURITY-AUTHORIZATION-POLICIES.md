# Phase 17 — Add Authorization Policies (Product/Order/Customer/Category)

## Objective
Establish fine-grained, policy-driven authorization controls by introducing standard Laravel `Policy` classes for core domain models (`Order`, `Customer`, `Product`, `Category`) and registering them within `AuthServiceProvider`, adhering to Rule 1 (Thin Controllers), Rule 17 (Authorization & Policies), and Rule 18 (IDOR Protection) of `DEVELOPMENT-RULES.md`.

## Why This Phase Exists
Without dedicated authorization policies, controller actions rely on ad-hoc or missing authorization checks, introducing severe IDOR (Insecure Direct Object Reference) vulnerabilities (e.g. customer A accessing or modifying customer B's order or profile data). Policies centralize permission rules and support multi-guard (`web` admin vs `customer`) contexts cleanly.

---

## 1. Implemented Policy Classes (`app/Policies/`)

### A. `OrderPolicy.php`
- `view($user, Order $order)`: Allows customer to view their own order (`$user->id === $order->customer_id`), or admin user with `order-list` / `order-edit` permissions.
- `update(User $user, Order $order)`: Restricts order modifications/status updates to admin users with `order-edit` or `order-process` permissions.
- `delete(User $user, Order $order)`: Restricts order deletion to authorized admins with `order-delete`.

### B. `CustomerPolicy.php`
- `view($user, Customer $customer)`: Restricts profile access to the authenticated customer themselves (`$user->id === $customer->id`) or admins with `customer-list` permission.
- `update($user, Customer $customer)`: Restricts profile/password updates to the owner customer or admins with `customer-edit`.

### C. `ProductPolicy.php`
- `viewAny(?$user)` & `view(?$user, Product $product)`: Unrestricted public catalog browsing.
- `create(User $user)`, `update(User $user, Product $product)`, `delete(User $user, Product $product)`: Enforces admin permissions (`product-create`, `product-edit`, `product-delete`).

### D. `CategoryPolicy.php`
- `viewAny(?$user)` & `view(?$user, Category $category)`: Public browsing.
- `create(User $user)`, `update(User $user, Category $category)`, `delete(User $user, Category $category)`: Enforces admin permissions (`category-create`, `category-edit`, `category-delete`).

---

## 2. Policy Registration (`app/Providers/AuthServiceProvider.php`)

All policy mappings are registered in `$policies`:
```php
protected $policies = [
    \App\Models\Order::class => \App\Policies\OrderPolicy::class,
    \App\Models\Customer::class => \App\Policies\CustomerPolicy::class,
    \App\Models\Product::class => \App\Policies\ProductPolicy::class,
    \App\Models\Category::class => \App\Policies\CategoryPolicy::class,
];
```

---

## 3. Verification & Regression Testing

- **Command**: `php artisan test`
- **Result**: 37 / 37 Tests Passing (100% Success Rate in 7.14s)
- **Multi-guard Compatibility**: Verified that customer sessions and admin sessions both resolve authorization checks without collision.

---

## 4. Definition of Done Checklist

- [x] Dedicated Policy classes created in `app/Policies/`
- [x] Multi-guard customer vs admin authorization supported
- [x] Policies registered in `AuthServiceProvider.php`
- [x] All 37 feature and characterization tests verified passing
- [x] Ready for Phase 18 (Fix IDOR Vulnerabilities)

