# Phase 61 — Pricing Engine (PriceBreakdown & Discounts)

## Objective
Extract and implement the dedicated Domain Pricing & Discount Engine (`src/Modules/Order/Domain/Services/PricingEngine.php`), `PriceBreakdownDTO`, and `CalculateOrderPriceAction` in strict compliance with Rule 04 (Action Pattern), Rule 07 (Money & Pricing Precision), and Rule 08 (Value Objects) of `DEVELOPMENT-RULES.md`.

## Why This Phase Exists
Centralizing order, discount, coupon, tax, and shipping price calculations into an encapsulated domain service removes duplicate arithmetic logic across frontend controllers and background jobs, eliminates floating-point rounding errors, and guarantees zero negative price bugs across the store.

---

## 1. Implemented Pricing Engine Components (`src/Modules/Order/`)

```
src/Modules/Order/
├── Domain/
│   └── Services/
│       └── PricingEngine.php (Subtotal, Fixed/Percentage Discount, Tax, Shipping, Grand Total)
├── Application/
│   ├── DTOs/
│   │   └── PriceBreakdownDTO.php (2-decimal financial breakdown)
│   └── Actions/
│       └── CalculateOrderPriceAction.php
```

---

## 2. Key Components Details

1. **`PricingEngine`**:
   - Computes cart subtotal, applies percentage/fixed discounts via `Discount->calculate($subtotal)`, adds shipping fees and taxes.
   - Enforces `subtotalAfterDiscount = max(0, subtotal - discount)` to prevent negative price anomalies.
2. **`PriceBreakdownDTO`**:
   - Immutable data transfer object containing exact numeric amounts and BDT formatted currency strings (`৳`).
3. **`CalculateOrderPriceAction`**:
   - Single-purpose action coordinating cart lookup and price calculation.

---

## 3. Test Suite & Verification Results

- **Command**: `php artisan test`
- **Total Tests**: 114 Tests (114 Passed, 0 Failed, 0 Skipped)
- **Suite**: `Tests\Unit\PricingEngineTest`
  - `pricing engine calculates subtotal correctly` ✅
  - `pricing engine with percentage discount` ✅
  - `pricing engine with fixed discount exceeding subtotal` ✅
  - `calculate order price action` ✅
- **Pass Rate**: 100.0%
- **Execution Time**: 17.70s

---

## 4. Definition of Done Checklist

- [x] `PricingEngine` domain service implemented with `Money` and `Discount` Value Objects
- [x] Negative grand total safeguards enforced
- [x] `PriceBreakdownDTO` implemented extending standard base DTO
- [x] `CalculateOrderPriceAction` implemented
- [x] Full unit test suite created with 100% assertions passing
- [x] All 114 tests across the application passing (100% success)
- [x] Ready for Phase 62 (Inventory Ledger & Stock Reservation)

