# Phase 58 — Password Reset Flow Extraction

## Objective
Extract and implement dedicated password reset and OTP verification actions in `src/Modules/Customer/Application/Actions/` (`RequestPasswordResetAction`, `VerifyPasswordResetOtpAction`, `ResetCustomerPasswordAction`, `ResendPasswordResetOtpAction`) in strict compliance with Rule 04 (Action Pattern), Rule 05 (Domain Events), and Rule 06 (Transaction Boundary Rules) of `DEVELOPMENT-RULES.md`.

## Why This Phase Exists
Extracting OTP generation, SMS dispatch, verification, and password update logic from monolithic frontend controllers decouples identity security concerns, enforces single-use OTP invalidation, and dispatches domain events (`CustomerPasswordResetRequestedEvent`) for auditability.

---

## 1. Implemented Password Reset Actions (`src/Modules/Customer/Application/Actions/`)

1. **`RequestPasswordResetAction`**:
   - Sanitizes and validates phone numbers via `PhoneNumber` Value Object.
   - Generates secure random 6-digit OTP (`111111`–`999999`).
   - Updates customer record inside `DB::transaction()`.
   - Sends OTP via `SmsServiceInterface->sendOtp()`.
   - Dispatches `CustomerPasswordResetRequestedEvent`.
   - Returns customer ID.
2. **`VerifyPasswordResetOtpAction`**:
   - Validates provided OTP against customer's stored `forgot` field.
3. **`ResetCustomerPasswordAction`**:
   - Verifies OTP validity.
   - Hashes new password using bcrypt within `DB::transaction()`.
   - Clears `forgot` field to prevent OTP reuse (One-Time Token).
   - Saves customer entity.
4. **`ResendPasswordResetOtpAction`**:
   - Generates fresh 6-digit OTP, updates database, sends via `SmsServiceInterface`, and dispatches event.

---

## 2. Test Suite & Verification Results

- **Command**: `php artisan test`
- **Total Tests**: 107 Tests (107 Passed, 0 Failed, 0 Skipped)
- **Suite**: `Tests\Unit\CustomerPasswordResetActionsTest`
  - `request password reset generates otp and dispatches event` ✅
  - `request password reset throws exception for unknown phone` ✅
  - `verify password reset otp` ✅
  - `reset password action updates password and clears otp` ✅
  - `resend password reset otp` ✅
- **Pass Rate**: 100.0%

---

## 3. Definition of Done Checklist

- [x] 4 dedicated Password Reset Actions implemented
- [x] `SmsServiceInterface` integrated for OTP delivery
- [x] `CustomerPasswordResetRequestedEvent` dispatched with event payload
- [x] Single-use OTP token invalidation enforced on password reset
- [x] Full unit test suite created with 100% assertions passing
- [x] All 107 tests across the application passing (100% success)
- [x] Ready for Phase 59 (Identity Module Capstone Integration Tests)

