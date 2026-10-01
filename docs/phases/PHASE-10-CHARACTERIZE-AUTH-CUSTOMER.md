# Phase 10 — Characterize Customer Auth (Register/Login/OTP)

## Objective
Implement automated regression-preventing characterization tests for the Customer Authentication lifecycle (Registration, Input Validation, Login, Logout, Plaintext OTP Generation, Forgot Password Reset), locking in existing behavior before decomposing authentication into the Identity Module.

## Why This Phase Exists
Authentication is the highest-risk domain boundary in an e-commerce platform. Having automated characterization tests in place ensures that subsequent refactoring (e.g. migrating to FormRequests, Hashed OTPs, and `RegisterCustomerAction` in Phases 55–59) guarantees zero breaking changes for real users.

---

## 1. Implemented Characterization Test Suite (`CustomerAuthCharacterizationTest.php`)

Located at `tests/Feature/CustomerAuthCharacterizationTest.php`:

```php
namespace Tests\Feature;

use App\Models\Customer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class CustomerAuthCharacterizationTest extends TestCase
{
    use RefreshDatabase;

    protected function createCustomer(array $attributes = []): Customer
    {
        $customer = new Customer();
        $customer->name = $attributes['name'] ?? 'Test Customer';
        $customer->slug = $attributes['slug'] ?? 'test-customer';
        $customer->phone = $attributes['phone'] ?? '01799999999';
        $customer->email = $attributes['email'] ?? 'test@example.com';
        $customer->password = $attributes['password'] ?? Hash::make('password123');
        $customer->verify = $attributes['verify'] ?? 1;
        $customer->status = $attributes['status'] ?? 'active';
        if (isset($attributes['forgot'])) {
            $customer->forgot = $attributes['forgot'];
        }
        $customer->save();

        return $customer;
    }

    // 1. test_customer_can_view_registration_page
    // 2. test_customer_registration_creates_account_and_redirects_to_login
    // 3. test_customer_registration_fails_on_duplicate_phone
    // 4. test_customer_can_view_login_page
    // 5. test_customer_can_login_with_valid_credentials
    // 6. test_customer_login_fails_with_invalid_credentials
    // 7. test_customer_can_logout
    // 8. test_forgot_password_generates_otp_and_session
    // 9. test_forgot_password_resets_password_with_valid_otp
}
```

---

## 2. Test Execution & Coverage Status

- **Command**: `php artisan test --filter=CustomerAuthCharacterizationTest`
- **Tests Executed**: 9
- **Status**: 9 Passed (100% Success Rate)
- **Key Assertions Verified**:
  - `GET /customer/register` status 200
  - `POST /customer/store` inserts customer with `verify=1`, `status='active'`, redirects to login
  - Duplicate phone validation failure redirects back with session errors
  - `POST /customer/signin` authenticates `Auth::guard('customer')` and redirects to account
  - `POST /customer/logout` logs out customer and redirects to login
  - `POST /customer/forgot-verify` stores random 4-digit OTP in `customers.forgot` and session `verify_phone`
  - `POST /customer/forgot-store` resets password upon correct OTP match and automatically signs in

---

## 3. Definition of Done Checklist

- [x] Characterization test class `CustomerAuthCharacterizationTest.php` created
- [x] All 9 test cases covering Registration, Login, Logout, and OTP Reset passing
- [x] Tested against in-memory SQLite database (`RefreshDatabase`)
- [x] Existing customer authentication behavior completely locked
- [x] Ready for Phase 11 (Characterize Product & Category Catalog)

