# Phase 57 — SMS Service Port & Adapter

## Objective
Establish SMS notification and OTP delivery infrastructure via dedicated Port (`SmsServiceInterface`) and Adapters (`LogSmsAdapter`, `GreenwebSmsAdapter`, `AlphaSmsAdapter`, `SmsGatewayManager`) in `src/Modules/Customer/` in strict compliance with Rule 02 (Service Provider Registration), Rule 03 (Module Isolation & Ports & Adapters Architecture), and Rule 08 (Value Objects) of `DEVELOPMENT-RULES.md`.

## Why This Phase Exists
Extracting external SMS gateway communication behind a domain port isolates third-party API dependencies (HTTP endpoints, API tokens, dynamic credentials in database) from application controllers and domain logic, ensuring zero network coupling during testing and seamless failover between providers.

---

## 1. Implemented SMS Port & Adapters Architecture (`src/Modules/Customer/`)

```
src/Modules/Customer/
├── Domain/
│   └── Contracts/
│       └── SmsServiceInterface.php
└── Infrastructure/
    ├── Adapters/
    │   └── Sms/
    │       ├── LogSmsAdapter.php
    │       ├── GreenwebSmsAdapter.php
    │       ├── AlphaSmsAdapter.php
    │       └── SmsGatewayManager.php
    └── Providers/
        └── CustomerServiceProvider.php
```

---

## 2. Key Components Details

1. **`SmsServiceInterface` (Domain Port)**:
   - `send(string $recipientPhone, string $message): bool`
   - `sendOtp(string $recipientPhone, int $otp): bool`
2. **`LogSmsAdapter`**:
   - In-memory message recording for local development and test environments (`getSentMessages()`).
3. **`GreenwebSmsAdapter`**:
   - Form-encoded HTTP integration with Greenweb Bangladesh SMS API (`http://api.greenweb.com.bd/api.php`).
4. **`AlphaSmsAdapter`**:
   - Form-encoded HTTP integration with Alpha Net SMS API (`https://api.sms.net.bd/sendsms`).
5. **`SmsGatewayManager`**:
   - Dynamic gateway resolver that queries active gateway from `sms_gateways` database table and routes messages to the matched provider adapter or falls back safely to `LogSmsAdapter`.
6. **`CustomerServiceProvider`**:
   - Binds `SmsServiceInterface` in Laravel service container.

---

## 3. Test Suite & Verification Results

- **Command**: `php artisan test`
- **Total Tests**: 102 Tests (102 Passed, 0 Failed, 0 Skipped)
- **Suite**: `Tests\Unit\SmsServiceAdapterTest`
  - `log sms adapter records messages` ✅
  - `sms gateway adapters implement contract` ✅
  - `sms gateway manager resolves and dispatches` ✅
- **Pass Rate**: 100.0%

---

## 4. Definition of Done Checklist

- [x] SMS Domain Port (`SmsServiceInterface`) established
- [x] 3 Concrete Adapters (`LogSmsAdapter`, `GreenwebSmsAdapter`, `AlphaSmsAdapter`) implemented
- [x] Dynamic Gateway Manager (`SmsGatewayManager`) implemented with zero recursion
- [x] Service provider bindings registered in `CustomerServiceProvider`
- [x] Full unit test suite created with 100% assertions passing
- [x] All 102 tests across the application passing (100% success)
- [x] Ready for Phase 58 (Password Reset Extraction)

