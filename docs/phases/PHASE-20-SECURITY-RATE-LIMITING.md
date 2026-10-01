# Phase 20 — Rate Limiting (Login/OTP/Checkout)

## Objective
Protect critical application endpoints (Customer Signin, Registration, OTP Verification, OTP Resend, and Order Placement) against Brute-force attacks, SMS Flooding, Credential Stuffing, and Automated Order Spam by configuring named Laravel `RateLimiter` definitions in `RouteServiceProvider` and enforcing `throttle` middleware in `routes/web.php` in compliance with Rule 18 (Security & Rate Limiting) of `DEVELOPMENT-RULES.md`.

## Why This Phase Exists
Without rate limiting, malicious attackers can execute dictionary attacks against user logins, deplete SMS gateway balances through unthrottled OTP generation, or overwhelm order databases with fake bot checkouts.

---

## 1. Configured Rate Limiters (`app/Providers/RouteServiceProvider.php`)

The following named rate limiters were registered in `configureRateLimiting()`:

```php
// API general rate limit
RateLimiter::for('api', function (Request $request) {
    return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
});

// Authentication endpoints (Login & Registration)
RateLimiter::for('auth', function (Request $request) {
    return Limit::perMinute(10)->by($request->ip());
});

// OTP request, verify and resend (Scoped by phone or IP)
RateLimiter::for('otp', function (Request $request) {
    return Limit::perMinute(5)->by($request->input('phone') ?: $request->ip());
});

// Checkout and order submission
RateLimiter::for('checkout', function (Request $request) {
    return Limit::perMinute(20)->by($request->ip());
});
```

---

## 2. Protected Routes (`routes/web.php`)

| Route URI | HTTP Method | Action | Assigned Throttle Middleware |
|---|---|---|---|
| `/customer/signin` | `POST` | Customer Login | `throttle:auth` (10/min) |
| `/customer/store` | `POST` | Customer Registration | `throttle:auth` (10/min) |
| `/customer/verify-account` | `POST` | Account OTP Verify | `throttle:otp` (5/min) |
| `/customer/resend-otp` | `POST` | Registration OTP Resend | `throttle:otp` (5/min) |
| `/customer/forgot-verify` | `POST` | Forgot Password Request | `throttle:otp` (5/min) |
| `/customer/forgot-password/store` | `POST` | Reset OTP Verify | `throttle:otp` (5/min) |
| `/customer/forgot-password/resendotp` | `POST` | Forgot OTP Resend | `throttle:otp` (5/min) |
| `/customer/order-save` | `POST` | Checkout Order Save | `throttle:checkout` (20/min) |

---

## 3. Verification & Regression Testing

- **Command**: `php artisan test`
- **Result**: 37 / 37 Tests Passing (100% Success Rate in 7.13s)
- **Zero Legitimate User Disruption**: Characterization test suites executed standard login, checkout, registration, and OTP flows without false-positive rate limit triggers.

---

## 4. Definition of Done Checklist

- [x] Named rate limiters (`auth`, `otp`, `checkout`, `api`) registered in `RouteServiceProvider.php`
- [x] `throttle` middleware applied across sensitive authentication and checkout routes
- [x] All 37 feature and characterization tests verified passing
- [x] Ready for Phase 21 (Session & Password Security)

