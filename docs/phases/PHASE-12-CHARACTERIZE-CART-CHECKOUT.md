# Phase 12 — Characterize Cart & Checkout Flow

## Objective
Implement automated regression-preventing characterization tests for the Shopping Cart and Checkout lifecycle (Add to Cart, Buy Now Quick Checkout, Quantity Increment/Decrement, Cart Item Removal, Cart Item Count, Checkout Page with Dynamic Shipping & Gateways, Incomplete Order Abandoned Lead Capture), locking in existing behavior before decomposing cart and checkout into dedicated Cart & Order Modules.

## Why This Phase Exists
Cart and Checkout are the direct revenue drivers of MondolShopBD. Having automated characterization tests in place ensures that subsequent refactoring (e.g. migrating Cart state management, FormRequests, ViewModels, and Order Placement Transactions in Track 3–5) guarantees zero breaking changes or lost revenue for checkout operations.

---

## 1. Implemented Characterization Test Suite (`CartCheckoutCharacterizationTest.php`)

Located at `tests/Feature/CartCheckoutCharacterizationTest.php`:

```php
namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\Productimage;
use App\Models\ShippingCharge;
use App\Models\PaymentGateway;
use App\Models\IncompleteOrder;
use Gloudemans\Shoppingcart\Facades\Cart;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CartCheckoutCharacterizationTest extends TestCase
{
    use RefreshDatabase;

    // Helper methods: createProduct, createShippingAndGateways

    // 1. test_product_can_be_added_to_cart_via_post
    // 2. test_product_can_be_added_to_cart_and_redirect_to_checkout
    // 3. test_cart_can_increment_and_decrement_item_quantity
    // 4. test_cart_item_can_be_removed
    // 5. test_cart_count_returns_view_with_total_items
    // 6. test_checkout_page_renders_with_shipping_and_gateways
    // 7. test_incomplete_order_saves_lead_data_during_checkout
}
```

---

## 2. Test Execution & Coverage Status

- **Command**: `php artisan test --filter=CartCheckoutCharacterizationTest`
- **Tests Executed**: 7
- **Status**: 7 Passed (100% Success Rate)
- **Key Assertions Verified**:
  - `POST /cart/store` with `order_now = "কার্টে যোগ করুন"` adds item to cart and redirects back
  - `POST /cart/store` with `order_now = "অর্ডার করুন"` adds item to cart and redirects immediately to checkout (`customer.checkout`)
  - `GET /cart/increment` and `GET /cart/decrement` accurately updates item quantity
  - `GET /cart/remove` removes item from cart and recalculates total
  - `GET /cart/count` returns rendered cart count badge HTML
  - `GET /checkout` loads shipping charges and payment gateways, sets default shipping in session
  - `POST /incomplete_order` persists customer lead info and JSON cart content into `incomplete_orders`

---

## 3. Definition of Done Checklist

- [x] Characterization test class `CartCheckoutCharacterizationTest.php` created
- [x] All 7 test cases covering Cart Store, Quantity Updates, Cart Count, Checkout, and Incomplete Order passing
- [x] Tested against in-memory SQLite database (`RefreshDatabase`)
- [x] Existing shopping cart and checkout behavior completely locked
- [x] Ready for Phase 13 (Characterize Order & Payment Processing)

