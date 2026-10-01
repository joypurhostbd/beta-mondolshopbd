# Phase 73 — Fraud Check Service (Internal History & Hoorin API)

## Objective
Establish the Fraud Check Domain Architecture (`FraudCheckService`) within the Shipping module (`src/Modules/Shipping/`) featuring `FraudCheckerInterface`, `FraudScoreDTO`, `InternalFraudChecker` (verifying customer delivery/cancellation history and blacklisted phone/IP numbers), `HoorinFraudChecker` (integrating external courier intelligence / Hoorin API), and service provider registration in strict compliance with Rule 01 (Directory Layout), Rule 03 (Module Isolation), Rule 07 (Money & Decimal Precision), and Rule 08 (Domain Enums) of `DEVELOPMENT-RULES.md`.

## Why This Phase Exists
In Bangladeshi e-commerce, Cash-on-Delivery (COD) return fraud is a primary cause of operational loss. Previously, fraud checking was scattered inside Eloquent model helpers with unmockable HTTP calls. Extracting a modular Fraud Check Service enables multi-source risk assessment (internal history + external courier APIs), precise risk categorisation (`low`, `medium`, `high`), and automated blocking of fraud spam.

---

## 1. Implemented Fraud Check Architecture

```
src/Modules/Shipping/
├── Domain/
│   └── Contracts/
│       └── FraudCheckerInterface.php
├── Application/
│   ├── DTOs/
│   │   └── FraudScoreDTO.php
│   └── Services/
│       └── FraudCheckService.php
└── Infrastructure/
    ├── Fraud/
    │   ├── InternalFraudChecker.php
    │   └── HoorinFraudChecker.php
    └── Providers/
        └── ShippingServiceProvider.php
```

---

## 2. Key Components Details

1. **`FraudCheckerInterface` Port**:
   - `checkCustomer(PhoneNumber $phoneNumber): FraudScoreDTO`
2. **`FraudScoreDTO`**:
   - Encapsulates `totalOrders`, `successfulOrders`, `cancelledOrders`, `successRatio`, `riskLevel` (`low`, `medium`, `high`), `isBlacklisted`, and audit `details`.
3. **`InternalFraudChecker`**:
   - Analyzes local customer order records (matched against `orders`, `customers`, and `shippings` tables) and checks `IpBlock` blacklist.
4. **`HoorinFraudChecker`**:
   - Queries external courier database (Steadfast/Pathao/RedX delivery statistics across Bangladesh).
5. **`FraudCheckService`**:
   - Aggregates multi-source scores and returns final risk verdict.

---

## 3. Test Suite & Verification Results

- **Command**: `php artisan test`
- **Total Tests**: 155 Tests (155 Passed, 0 Failed, 0 Skipped)
- **Suite**: `Tests\Unit\FraudCheckServiceTest`
  - `clean customer returns low risk score` ✅
  - `blocked customer returns high risk` ✅
  - `cancelled order history elevates risk` ✅
- **Pass Rate**: 100.0%
- **Execution Time**: 25.90s

---

## 4. Definition of Done Checklist

- [x] `FraudCheckerInterface` defined
- [x] `FraudScoreDTO` implemented
- [x] `InternalFraudChecker` implemented and tested with local DB
- [x] `HoorinFraudChecker` implemented with sandbox fallback
- [x] `FraudCheckService` registered in `ShippingServiceProvider`
- [x] Unit test suite created and passing
- [x] All 155 tests across the application passing (100% success)
- [x] Ready for Phase 74 (Shipping Module Capstone Integration Tests)

