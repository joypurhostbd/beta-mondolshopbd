# Combined Plan — Identity + Order + Payment Module Extraction

> **3 modules in 1 plan** — connect existing src/ modules to app/ controllers  
> **Total estimated**: 10-12 hours  
> **Risk**: High (Order + Payment are business-critical)

---

## Current State

### ✅ Already Exists in src/ (with real logic)

| Module | Files | Actions | Lines | Status |
|---|---|---|---|---|
| **Customer** | 22 | 9 actions (Register, Auth, OTP, Password, Profile) | 290 | ✅ Ready |
| **Order** | 30 | 10 actions (PlaceOrder, Cancel, POS, Cart, Status, Pricing) | 557 | ✅ Ready |
| **Payment** | 17 | 1 action (ProcessWebhook) + 4 gateways (Bkash, ShurjoPay, COD, Mock) | 74 | ✅ Ready |

### ❌ Not Connected

| Module | Controllers Still Using app/Models | Files |
|---|---|---|
| **Customer** | CustomerController (565 lines) | 1 controller |
| **Order** | OrderController (748 lines), ShoppingController (130 lines) | 2 controllers |
| **Payment** | BkashController (235 lines), ShurjopayControllers (47 lines), FrontendController (418 lines) | 3 controllers |

---

## TASK 1: Identity/Customer Module (Phase 55-59)

### Module Files (22 in src/)

```
src/Modules/Customer/
├── Application/
│   ├── Actions/
│   │   ├── AuthenticateCustomerAction.php (45 lines)
│   │   ├── RegisterCustomerAction.php (50 lines)
│   │   ├── RequestPasswordResetAction.php (39 lines)
│   │   ├── VerifyPasswordResetOtpAction.php (17 lines)
│   │   ├── ResetCustomerPasswordAction.php (29 lines)
│   │   ├── ResendPasswordResetOtpAction.php (36 lines)
│   │   ├── ChangeCustomerPasswordAction.php (25 lines)
│   │   ├── UpdateCustomerProfileAction.php (37 lines)
│   │   └── LogoutCustomerAction.php (12 lines)
│   ├── DTOs/
│   │   └── CustomerDTO.php
│   └── Services/
│       └── CustomerService.php
├── Domain/
│   ├── Contracts/
│   │   ├── CustomerRepositoryInterface.php
│   │   └── SmsServiceInterface.php
│   ├── Entities/
│   │   └── CustomerEntity.php
│   └── Events/
│       ├── CustomerRegisteredEvent.php
│       └── CustomerPasswordResetRequestedEvent.php
└── Infrastructure/
    ├── Adapters/Sms/
    │   ├── AlphaSmsAdapter.php
    │   ├── GreenwebSmsAdapter.php
    │   ├── LogSmsAdapter.php
    │   └── SmsGatewayManager.php
    ├── Providers/
    │   └── CustomerServiceProvider.php
    └── Repositories/
        └── EloquentCustomerRepository.php
```

### Implementation

#### Step 1: Update CustomerController to use module actions

```php
// BEFORE — CustomerController.php (565 lines, fat controller)
public function store(Request $request) {
    // 20+ lines of validation, creation, SMS sending
}

// AFTER — Thin controller
use Modules\Customer\Application\Actions\RegisterCustomerAction;
use Modules\Customer\Application\Actions\AuthenticateCustomerAction;
use Modules\Customer\Application\Actions\RequestPasswordResetAction;

public function store(CustomerRegisterRequest $request, RegisterCustomerAction $action)
{
    $customer = $action->execute($request->validated());
    return redirect()->route('customer.verify')->with('phone', $customer->phone);
}

public function signin(CustomerSigninRequest $request, AuthenticateCustomerAction $action)
{
    $result = $action->execute($request->validated());
    if ($result->success) {
        return redirect()->route('home');
    }
    return back()->withErrors(['phone' => $result->error]);
}
```

#### Step 2: Replace SMS curl with SmsGatewayManager

```php
// BEFORE — inline curl in CustomerController
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
// ... 10 lines of curl

// AFTER — use module SMS adapter
use Modules\Customer\Infrastructure\Adapters\Sms\SmsGatewayManager;

$smsManager->send($phone, $message);
```

### Files To Modify

| File | Change |
|---|---|
| `app/Http/Controllers/Frontend/CustomerController.php` | Use RegisterCustomerAction, AuthenticateCustomerAction, etc. |

### Files Already Exist (verify completeness)

| File | Purpose |
|---|---|
| `src/Modules/Customer/Application/Actions/*.php` | 9 action classes |
| `src/Modules/Customer/Infrastructure/Adapters/Sms/*.php` | SMS adapters |
| `src/Modules/Customer/Infrastructure/Providers/CustomerServiceProvider.php` | Provider |

---

## TASK 2: Order Module (Phase 60-66)

### Module Files (30 in src/)

```
src/Modules/Order/
├── Application/
│   ├── Actions/
│   │   ├── PlaceOrderAction.php (135 lines)
│   │   ├── CancelOrderAction.php (24 lines)
│   │   ├── CreateAdminPosOrderAction.php (158 lines)
│   │   ├── ChangeOrderStatusAction.php (71 lines)
│   │   ├── CalculateOrderPriceAction.php (43 lines)
│   │   └── Cart/
│   │       ├── AddToCartAction.php (43 lines)
│   │       ├── RemoveFromCartAction.php (21 lines)
│   │       ├── UpdateCartItemQuantityAction.php (28 lines)
│   │       ├── GetCartAction.php (18 lines)
│   │       └── ClearCartAction.php (16 lines)
│   ├── DTOs/
│   │   ├── PlaceOrderInputDTO.php
│   │   ├── AdminPosOrderInputDTO.php
│   │   ├── OrderDTO.php
│   │   ├── CartDTO.php
│   │   ├── CartItemDTO.php
│   │   └── PriceBreakdownDTO.php
│   └── Services/
│       └── OrderService.php
├── Domain/
│   ├── Contracts/
│   │   ├── OrderRepositoryInterface.php
│   │   └── CartRepositoryInterface.php
│   ├── Entities/
│   │   ├── OrderEntity.php
│   │   ├── OrderItemEntity.php
│   │   ├── CartEntity.php
│   │   └── CartItemEntity.php
│   ├── Events/
│   │   ├── OrderPlacedEvent.php
│   │   └── OrderStatusChangedEvent.php
│   └── Services/
│       ├── OrderStateMachine.php
│       └── PricingEngine.php
└── Infrastructure/
    ├── Providers/
    │   └── OrderServiceProvider.php
    └── Repositories/
        ├── EloquentOrderRepository.php
        └── RedisCartRepository.php
```

### Implementation

#### Step 1: Update ShoppingController to use Cart actions

```php
// BEFORE — ShoppingController.php (130 lines)
Cart::instance('shopping')->add([...]);

// AFTER
use Modules\Order\Application\Actions\Cart\AddToCartAction;

public function cart_store(CartStoreRequest $request, AddToCartAction $action)
{
    $action->execute($request->validated());
    return redirect()->back()->with('success', 'Added to cart');
}
```

#### Step 2: Update CustomerController checkout to use PlaceOrderAction

```php
// BEFORE — CustomerController::order_save() (170+ lines)
// Validation, cart processing, order creation, payment, SMS, courier

// AFTER
use Modules\Order\Application\Actions\PlaceOrderAction;

public function order_save(CustomerOrderSaveRequest $request, PlaceOrderAction $action)
{
    $result = $action->execute($request->toDTO());
    return redirect()->route('customer.order_success', $result->orderId);
}
```

#### Step 3: Update OrderController POS to use CreateAdminPosOrderAction

```php
// BEFORE — OrderController::order_store() (84 lines)

// AFTER
use Modules\Order\Application\Actions\CreateAdminPosOrderAction;

public function order_store(Request $request, CreateAdminPosOrderAction $action)
{
    $result = $action->execute($request->validated());
    return redirect()->route('admin.orders', 'all');
}
```

#### Step 4: Update OrderController status changes

```php
// AFTER
use Modules\Order\Application\Actions\ChangeOrderStatusAction;

public function updateStatus(Request $request, ChangeOrderStatusAction $action)
{
    $action->execute($request->order_id, $request->status_id);
    return response()->json(['status' => 'success']);
}
```

### Files To Modify

| File | Change |
|---|---|
| `app/Http/Controllers/Frontend/ShoppingController.php` | Use Cart actions |
| `app/Http/Controllers/Frontend/CustomerController.php` | Use PlaceOrderAction |
| `app/Http/Controllers/Admin/OrderController.php` | Use CreateAdminPosOrderAction, ChangeOrderStatusAction |

### Files Already Exist

| File | Purpose |
|---|---|
| `src/Modules/Order/Application/Actions/*.php` | 10 action classes |
| `src/Modules/Order/Domain/Services/OrderStateMachine.php` | State transitions |
| `src/Modules/Order/Domain/Services/PricingEngine.php` | Price calculation |
| `src/Modules/Order/Infrastructure/Repositories/RedisCartRepository.php` | Redis cart |

---

## TASK 3: Payment Module (Phase 67-71)

### Module Files (17 in src/)

```
src/Modules/Payment/
├── Application/
│   ├── Actions/
│   │   └── ProcessPaymentWebhookAction.php (74 lines)
│   ├── DTOs/
│   │   ├── PaymentInitiationDTO.php
│   │   ├── PaymentRedirectDTO.php
│   │   ├── PaymentResultDTO.php
│   │   └── PaymentVerificationDTO.php
│   └── Services/
│       ├── PaymentGatewayManager.php
│       └── PaymentService.php
├── Domain/
│   ├── Contracts/
│   │   ├── PaymentGatewayInterface.php
│   │   └── PaymentRepositoryInterface.php
│   ├── Entities/
│   │   └── PaymentEntity.php
│   └── Events/
│       └── PaymentProcessedEvent.php
└── Infrastructure/
    ├── Gateways/
    │   ├── BkashPaymentGateway.php
    │   ├── ShurjoPayPaymentGateway.php
    │   ├── CodPaymentGateway.php
    │   └── MockPaymentGateway.php
    ├── Providers/
    │   └── PaymentServiceProvider.php
    └── Repositories/
        └── EloquentPaymentRepository.php
```

### Implementation

#### Step 1: Update BkashController to use PaymentGatewayManager

```php
// BEFORE — BkashController.php (235 lines, raw curl)
$curl = curl_init($this->base_url.$url);
curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
// ... 20 lines of curl

// AFTER
use Modules\Payment\Application\Services\PaymentGatewayManager;

public function create(Request $request, PaymentGatewayManager $gatewayManager)
{
    $gateway = $gatewayManager->driver('bkash');
    $result = $gateway->createPaymentIntent($request->amount, $request->order_id);
    return redirect($result->redirectUrl);
}
```

#### Step 2: Update ShurjopayControllers to use PaymentGatewayManager

```php
// BEFORE
$shurjopay_service = new ShurjoPayService();
$json = $shurjopay_service->verify($order_id);

// AFTER
use Modules\Payment\Application\Services\PaymentGatewayManager;

public function payment_success(Request $request, PaymentGatewayManager $gatewayManager)
{
    $gateway = $gatewayManager->driver('shurjopay');
    $result = $gateway->verifyPayment($request->order_id);
    // ...
}
```

#### Step 3: Update FrontendController payment callbacks

```php
// AFTER
use Modules\Payment\Application\Actions\ProcessPaymentWebhookAction;

public function payment_success(Request $request, ProcessPaymentWebhookAction $action)
{
    $action->execute('shurjopay', $request->all());
    return redirect()->route('customer.order_success', $orderId);
}
```

### Files To Modify

| File | Change |
|---|---|
| `app/Http/Controllers/Frontend/BkashController.php` | Use PaymentGatewayManager |
| `app/Http/Controllers/Frontend/ShurjopayControllers.php` | Use PaymentGatewayManager |
| `app/Http/Controllers/Frontend/FrontendController.php` | Use ProcessPaymentWebhookAction |
| `app/Http/Controllers/Frontend/CustomerController.php` | Use PaymentGatewayManager for checkout |

### Files Already Exist

| File | Purpose |
|---|---|
| `src/Modules/Payment/Infrastructure/Gateways/BkashPaymentGateway.php` | bKash adapter |
| `src/Modules/Payment/Infrastructure/Gateways/ShurjoPayPaymentGateway.php` | ShurjoPay adapter |
| `src/Modules/Payment/Infrastructure/Gateways/CodPaymentGateway.php` | Cash on Delivery |
| `src/Modules/Payment/Application/Services/PaymentGatewayManager.php` | Gateway manager |
| `src/Modules/Payment/Application/Actions/ProcessPaymentWebhookAction.php` | Webhook processor |

---

## Execution Order

```
Task 1: Identity/Customer Module (3 hr)
  ↓ Step 1.1: Verify Customer module completeness
  ↓ Step 1.2: Update CustomerController (register, login, OTP, password)
  ↓ Step 1.3: Replace SMS curl with SmsGatewayManager
  ↓ Step 1.4: Test

Task 2: Order Module (5 hr)
  ↓ Step 2.1: Verify Order module completeness
  ↓ Step 2.2: Update ShoppingController (cart actions)
  ↓ Step 2.3: Update CustomerController (PlaceOrderAction)
  ↓ Step 2.4: Update OrderController (POS, status changes)
  ↓ Step 2.5: Test

Task 3: Payment Module (4 hr)
  ↓ Step 3.1: Verify Payment module completeness
  ↓ Step 3.2: Update BkashController (PaymentGatewayManager)
  ↓ Step 3.3: Update ShurjopayControllers (PaymentGatewayManager)
  ↓ Step 3.4: Update FrontendController (ProcessPaymentWebhookAction)
  ↓ Step 3.5: Test
```

---

## Verification Commands

```bash
# After Task 1
grep -rn 'curl_\|Http::' app/Http/Controllers/Frontend/CustomerController.php | wc -l  # Should be 0
grep -rn 'Modules\\Customer' app/Http/Controllers/Frontend/CustomerController.php | wc -l  # Should be 5+

# After Task 2
grep -rn 'Cart::instance\|CartService::instance' app/Http/Controllers/Frontend/ShoppingController.php | wc -l  # Should be 0
grep -rn 'Modules\\Order' app/Http/Controllers/ --include="*.php" | wc -l  # Should be 5+

# After Task 3
grep -rn 'curl_\|Http::' app/Http/Controllers/Frontend/BkashController.php | wc -l  # Should be 0
grep -rn 'Modules\\Payment' app/Http/Controllers/ --include="*.php" | wc -l  # Should be 3+

# Final
php artisan test --filter=ArchitectureFitnessTest
```

---

## Definition of Done

### Task 1: Identity Module
- [ ] CustomerController uses RegisterCustomerAction
- [ ] CustomerController uses AuthenticateCustomerAction
- [ ] SMS uses SmsGatewayManager (no curl)
- [ ] Tests passing

### Task 2: Order Module
- [ ] ShoppingController uses Cart actions
- [ ] CustomerController uses PlaceOrderAction
- [ ] OrderController uses CreateAdminPosOrderAction
- [ ] OrderController uses ChangeOrderStatusAction
- [ ] Tests passing

### Task 3: Payment Module
- [ ] BkashController uses PaymentGatewayManager
- [ ] ShurjopayControllers uses PaymentGatewayManager
- [ ] FrontendController uses ProcessPaymentWebhookAction
- [ ] No raw curl in payment controllers
- [ ] Tests passing

---

## Risks

| Risk | Mitigation |
|---|---|
| Module stubs may be incomplete | Verify before wiring, fill gaps |
| Order flow breaks | Characterization tests before changes |
| Payment callbacks fail | Test with sandbox/test mode first |
| Cart migration | Keep same session-based approach initially |
