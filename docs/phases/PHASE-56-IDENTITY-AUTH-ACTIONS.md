# Phase 56 — Customer Auth Actions

## Objective
Implement single-purpose customer authentication, profile management, and password update actions in `src/Modules/Customer/Application/Actions/` (`RegisterCustomerAction`, `AuthenticateCustomerAction`, `UpdateCustomerProfileAction`, `ChangeCustomerPasswordAction`, `LogoutCustomerAction`) in strict compliance with Rule 04 (Thin Controllers & Action Pattern), Rule 05 (Domain Events), and Rule 06 (Transaction Boundary Rules) of `DEVELOPMENT-RULES.md`.

## Why This Phase Exists
Extracting registration, credential verification, and profile management from monolithic frontend controllers into dedicated actions enables consistent behavior across web sessions, REST APIs, and background OTP processes while guaranteeing audit trail events and input safety.

---

## 1. Implemented Customer Actions (`src/Modules/Customer/Application/Actions/`)

1. **`RegisterCustomerAction`**:
   - Sanitizes and validates phone numbers via `PhoneNumber` Value Object.
   - Prevents duplicate registration.
   - Hashes passwords using bcrypt.
   - Generates slug and defaults active status.
   - Dispatches `CustomerRegisteredEvent`.
   - Returns typed `CustomerDTO`.
2. **`AuthenticateCustomerAction`**:
   - Supports dual lookup by mobile phone number or email address.
   - Verifies hash match and active account status (`status == 1`).
   - Authenticates customer guard session (`Auth::guard('customer')->login()`).
   - Returns typed `CustomerDTO` on success or `null` on invalid credentials.
3. **`UpdateCustomerProfileAction`**:
   - Updates personal info (name, email, address, district, area, image) inside a `DB::transaction`.
   - Returns updated `CustomerDTO`.
4. **`ChangeCustomerPasswordAction`**:
   - Verifies current password before applying new bcrypt hash.
5. **`LogoutCustomerAction`**:
   - Terminates customer guard session cleanly.

---

## 2. Test Suite & Verification Results

- **Command**: `php artisan test`
- **Total Tests**: 99 Tests (99 Passed, 0 Failed, 0 Skipped)
- **Suite**: `Tests\Unit\CustomerAuthActionsTest`
  - `register customer action creates and fires event` ✅
  - `register customer fails on duplicate phone` ✅
  - `authenticate customer action logs in user` ✅
  - `update profile and change password actions` ✅
- **Pass Rate**: 100.0%

---

## 3. Definition of Done Checklist

- [x] 5 dedicated Customer Auth Actions implemented
- [x] `PhoneNumber` and `Email` Value Objects applied for input sanitization
- [x] `CustomerRegisteredEvent` dispatched on registration
- [x] Full unit test suite created with 100% assertions passing
- [x] All 99 tests across the application passing (100% success)
- [x] Ready for Phase 57 (SMS Service Port & Adapter)

