# Phase 45 — Create Core Value Objects (Money/Email/PhoneNumber/Quantity/Discount/TrackingCode)

## Objective
Establish immutable, self-validating, domain-invariant protecting Value Objects in `src/Shared/Domain/ValueObjects/` (`PhoneNumber`, `Email`, `Quantity`, `Discount`, `TrackingCode`, and `Money`) in strict adherence to Rule 05 (Domain Objects & Value Objects) of `DEVELOPMENT-RULES.md`.

## Why This Phase Exists
Primitive obsession (using raw strings and unvalidated integers for domain concepts like Bangladeshi phone numbers, emails, discounts, and item quantities) leads to repeated validation logic, invalid states, and financial rounding inaccuracies. Domain Value Objects encapsulate parsing, validation, equality, and formatting into clean immutable objects.

---

## 1. Implemented Value Objects

### A. Bangladeshi Phone Number (`src/Shared/Domain/ValueObjects/PhoneNumber.php`)
- **Validation**: Strict regex matching Bangladeshi telecom prefixes (`013`-`019`).
- **Standardization**: Accepts `+88017...`, `88017...`, `01712-345678` and normalizes to canonical `017XXXXXXXX` (11 digits).
- **Methods**: `getValue()`, `getInternational()` (`+8801XXXXXXXXX`), `getFormatted()` (`017XX-XXXXXX`), `equals()`, `__toString()`.

### B. Email Address (`src/Shared/Domain/ValueObjects/Email.php`)
- **Validation**: RFC email filter validation & lowercase sanitization.
- **Methods**: `getValue()`, `getDomain()`, `equals()`, `__toString()`.

### C. Order & Product Quantity (`src/Shared/Domain/ValueObjects/Quantity.php`)
- **Validation**: Enforces positive integers `> 0`.
- **Arithmetic**: `add(Quantity)`, `subtract(Quantity)`, `multiply(int)`, `equals()`.

### D. Discount (`src/Shared/Domain/ValueObjects/Discount.php`)
- **Flexibility**: Supports percentage-based discounts (0–100%) or fixed amounts (`Money`).
- **Calculation**: `calculate(Money $subtotal): Money` with subtotal bounding.

### E. Tracking Code (`src/Shared/Domain/ValueObjects/TrackingCode.php`)
- **Validation**: Strips spaces, capitalizes string, enforces minimum length.

---

## 2. Verification & Test Results

- **Command**: `php artisan test`
- **Total Tests**: 58 Tests (58 Passed, 0 Failed, 0 Skipped)
- **Suite**: `Tests\Unit\CoreValueObjectsTest`
  - `test_phone_number_bangladeshi_formats` ✅
  - `test_invalid_phone_number_throws_exception` ✅
  - `test_email_validation_and_normalization` ✅
  - `test_quantity_operations` ✅
  - `test_discount_calculation` ✅
  - `test_tracking_code_value_object` ✅

---

## 3. Definition of Done Checklist

- [x] 6 Core Value Objects implemented with `ValueObjectInterface`
- [x] Immutability and self-validation exceptions verified
- [x] Unit test suite created with 100% assertions passing
- [x] All 58 tests verified passing (100% success)
- [x] Ready for Phase 46 (Create Shared Domain Exceptions)

