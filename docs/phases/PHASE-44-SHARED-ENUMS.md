# Phase 44 — Create Domain Enums (OrderStatus/PaymentStatus/ProductStatus)

## Objective
Establish PHP 8.1+ strongly typed Backed Enums in `src/Shared/Domain/Enums/` for all finite domain states (`OrderStatusEnum`, `PaymentStatusEnum`, `PaymentMethodEnum`, `ProductStatusEnum`, `CustomerStatusEnum`, `AuditActionEnum`, `OutboxStatusEnum`) in strict compliance with Rule 08 (Enums for State Management) of `DEVELOPMENT-RULES.md`.

## Why This Phase Exists
Using raw string literals or magic integers across controllers, queries, and business logic causes typo-induced bugs, invalid status transitions, and difficult maintenance. Strongly-typed backed enums guarantee compile-time and runtime type safety while providing helper methods for UI badges, labels, and transition logic.

---

## 1. Implemented Domain Enums

### A. Order State Management (`src/Shared/Domain/Enums/OrderStatusEnum.php`)
- **Values**: `PENDING = 1`, `PROCESSING = 2`, `ON_HOLD = 3`, `COMPLETED = 4`, `CANCELLED = 5`, `RETURNED = 6`, `DELIVERED = 7`
- **Helper Methods**: `label(): string`, `badgeColor(): string`, `isCancellable(): bool`

### B. Payment State Management (`src/Shared/Domain/Enums/PaymentStatusEnum.php`)
- **Values**: `PENDING = 'pending'`, `PAID = 'paid'`, `FAILED = 'failed'`, `REFUNDED = 'refunded'`, `CANCELLED = 'cancelled'`
- **Helper Methods**: `label(): string`, `badgeColor(): string`, `isSuccessful(): bool`

### C. Payment Methods (`src/Shared/Domain/Enums/PaymentMethodEnum.php`)
- **Values**: `COD = 'cod'`, `BKASH = 'bkash'`, `SHURJOPAY = 'shurjopay'`, `NAGAD = 'nagad'`, `ROCKET = 'rocket'`
- **Helper Methods**: `label(): string`, `isOnline(): bool`

### D. Catalog & Customer States
- **`ProductStatusEnum.php`**: `ACTIVE = 1`, `INACTIVE = 0`, `DRAFT = 2`
- **`CustomerStatusEnum.php`**: `ACTIVE = 'active'`, `INACTIVE = 'inactive'`, `BANNED = 'banned'`

### E. Infrastructure & Observability States
- **`AuditActionEnum.php`**: `CREATE = 'create'`, `UPDATE = 'update'`, `DELETE = 'delete'`, `LOGIN = 'login'`, `LOGOUT = 'logout'`, `STATUS_CHANGE = 'status_change'`
- **`OutboxStatusEnum.php`**: `PENDING = 'pending'`, `PROCESSED = 'processed'`, `FAILED = 'failed'`

---

## 2. Verification & Test Results

- **Command**: `php artisan test`
- **Total Tests**: 52 Tests (52 Passed, 0 Failed, 0 Skipped)
- **Suite**: `Tests\Unit\DomainEnumsTest`
  - `test_order_status_enum_values_and_labels` ✅
  - `test_payment_status_enum` ✅
  - `test_payment_method_enum` ✅
  - `test_product_and_customer_status_enums` ✅
  - `test_audit_action_and_outbox_status_enums` ✅

---

## 3. Definition of Done Checklist

- [x] 7 Backed Enums created in `src/Shared/Domain/Enums/`
- [x] Helper methods for `label()`, `badgeColor()`, and boolean checks implemented
- [x] Unit test suite created with 100% assertions passing
- [x] All 52 tests verified passing (100% success)
- [x] Ready for Phase 45 (Create Core Value Objects)

