# Phase 60 — Replace Session Cart with Redis Cart

## Objective
Establish the Order Domain module (`src/Modules/Order/`) and implement high-performance Redis-backed Cart components (`CartEntity`, `CartItemEntity`, `CartRepositoryInterface`, `RedisCartRepository`, `CartDTO`, `CartItemDTO`, and 5 Cart Actions) in strict compliance with Rule 01 (Directory Layout & Namespace Standard), Rule 02 (Service Provider Registration), Rule 04 (Action Pattern), Rule 07 (Money & Pricing Precision), and Rule 08 (Value Objects) of `DEVELOPMENT-RULES.md`.

## Why This Phase Exists
Extracting shopping cart state from PHP session cookies and relational database storage into a dedicated Redis cache repository enables high-throughput concurrent cart modifications, persistent in-memory cart retention across sessions, and cleanly decouples frontend cart interactions from database write locks.

---

## 1. Implemented Order & Cart Architecture (`src/Modules/Order/`)

```
src/Modules/Order/
├── Domain/
│   ├── Entities/
│   │   ├── CartItemEntity.php (Money price, Quantity qty, subtotal arithmetic)
│   │   └── CartEntity.php (Aggregate root, item merge logic, total calculations, releaseEvents)
│   └── Contracts/
│       └── CartRepositoryInterface.php (Pure Key-Value repository contract)
├── Application/
│   ├── DTOs/
│   │   ├── CartItemDTO.php
│   │   └── CartDTO.php
│   └── Actions/
│       └── Cart/
│           ├── AddToCartAction.php
│           ├── UpdateCartItemQuantityAction.php
│           ├── RemoveFromCartAction.php
│           ├── ClearCartAction.php
│           └── GetCartAction.php
└── Infrastructure/
    ├── Repositories/
    │   └── RedisCartRepository.php (JSON serialization with configurable TTL)
    └── Providers/
        └── OrderServiceProvider.php (Registered in config/app.php)
```

---

## 2. Key Components Details

1. **`CartItemEntity`**:
   - Encapsulates product details, unit price (`Money`), quantity (`Quantity`), and variant attributes (`size`, `color`).
   - Implements automated unique key generation (`item_{productId}_{size}_{color}`) and subtotal precision arithmetic.
2. **`CartEntity`**:
   - Aggregate root for items collection. Automatically merges quantities when identical product variants are added.
   - Calculates total monetary sum and aggregate quantity count.
3. **`RedisCartRepository`**:
   - Implements `CartRepositoryInterface` with cache/Redis backend and 30-day default TTL (`2592000` seconds).
4. **Cart Actions**:
   - `AddToCartAction`, `UpdateCartItemQuantityAction`, `RemoveFromCartAction`, `ClearCartAction`, `GetCartAction`.
5. **`OrderServiceProvider`**:
   - Registers singleton binding for `CartRepositoryInterface` -> `RedisCartRepository`.

---

## 3. Test Suite & Verification Results

- **Command**: `php artisan test`
- **Total Tests**: 110 Tests (110 Passed, 0 Failed, 0 Skipped)
- **Suite**: `Tests\Unit\RedisCartTest`
  - `cart item and cart entity calculations` ✅
  - `redis cart actions full lifecycle` ✅
- **Pass Rate**: 100.0%
- **Execution Time**: 18.10s

---

## 4. Definition of Done Checklist

- [x] Order Module directory structure initialized (`src/Modules/Order/`)
- [x] `CartEntity` and `CartItemEntity` domain models implemented with `Money` and `Quantity` Value Objects
- [x] `CartRepositoryInterface` and `RedisCartRepository` implemented
- [x] 5 single-purpose Cart Actions implemented
- [x] `OrderServiceProvider` registered in `config/app.php`
- [x] Full unit test suite created with 100% assertions passing
- [x] All 110 tests across the application passing (100% success)
- [x] Ready for Phase 61 (Pricing Engine Extraction)

