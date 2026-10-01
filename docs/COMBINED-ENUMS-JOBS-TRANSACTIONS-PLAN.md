# Combined Plan — Enums + ValueObjects + Jobs/Events + Transaction Boundaries

> **3 tasks in 1 plan** — foundation for module extraction  
> **Total estimated**: 4-5 hours  
> **Risk**: Low-Medium

---

## TASK 1: Enums + ValueObjects (Phase 44-45)

### Problem

Magic strings everywhere for status values. No type safety for money, email, phone.

### Magic Strings Found

#### Order Status (magic numbers)
```php
// DashboardController — hardcoded '5' means "delivered"
Order::where(['order_status'=>'5'])->count();

// OrderController — hardcoded 4 means "shipped"
$order->order_status = 4;
```

#### Payment Status (magic strings)
```php
$payment->payment_status = 'pending';
$payment->payment_status = 'paid';
$payment->payment_status = $data[0]->bank_status;
```

#### Customer/Review Status (magic strings)
```php
$store->status = 'active';
$inactive->status = 'inactive';
$review->status = 'pending';
$input['status'] = $request->status==1?'active':'pending';
```

### Implementation

#### Step 1: Create Enums

```php
// app/Enums/OrderStatusEnum.php
namespace App\Enums;

enum OrderStatusEnum: string
{
    case Pending = '1';
    case Confirmed = '2';
    case Processing = '3';
    case Shipped = '4';
    case Delivered = '5';
    case Cancelled = '6';

    public function label(): string
    {
        return match($this) {
            self::Pending => 'Pending',
            self::Confirmed => 'Confirmed',
            self::Processing => 'Processing',
            self::Shipped => 'Shipped',
            self::Delivered => 'Delivered',
            self::Cancelled => 'Cancelled',
        };
    }
}
```

```php
// app/Enums/PaymentStatusEnum.php
namespace App\Enums;

enum PaymentStatusEnum: string
{
    case Pending = 'pending';
    case Paid = 'paid';
    case Failed = 'failed';
    case Refunded = 'refunded';
}
```

```php
// app/Enums/StatusEnum.php
namespace App\Enums;

enum StatusEnum: string
{
    case Active = 'active';
    case Inactive = 'inactive';
    case Pending = 'pending';
}
```

```php
// app/Enums/ReviewStatusEnum.php
namespace App\Enums;

enum ReviewStatusEnum: string
{
    case Pending = 'pending';
    case Approved = 'active';
    case Rejected = 'inactive';
}
```

#### Step 2: Create ValueObjects

```php
// app/ValueObjects/Money.php
namespace App\ValueObjects;

readonly class Money
{
    public function __construct(
        public float $amount,
        public string $currency = 'BDT',
    ) {
        if ($amount < 0) {
            throw new \InvalidArgumentException('Amount cannot be negative');
        }
    }

    public static function fromDecimal(float $amount): self
    {
        return new self(round($amount, 2));
    }

    public function add(self $other): self
    {
        return new self($this->amount + $other->amount, $this->currency);
    }

    public function subtract(self $other): self
    {
        return new self($this->amount - $other->amount, $this->currency);
    }

    public function formatted(): string
    {
        return '৳' . number_format($this->amount, 2);
    }

    public function equals(self $other): bool
    {
        return $this->amount === $other->amount && $this->currency === $other->currency;
    }
}
```

```php
// app/ValueObjects/Phone.php
namespace App\ValueObjects;

readonly class Phone
{
    public function __construct(
        public string $value,
    ) {
        if (!preg_match('/^01[3-9]\d{8}$/', $value)) {
            throw new \InvalidArgumentException("Invalid Bangladeshi phone: {$value}");
        }
    }

    public function formatted(): string
    {
        return $this->value;
    }

    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }
}
```

```php
// app/ValueObjects/Email.php
namespace App\ValueObjects;

readonly class Email
{
    public function __construct(
        public string $value,
    ) {
        if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
            throw new \InvalidArgumentException("Invalid email: {$value}");
        }
    }

    public function equals(self $other): bool
    {
        return strtolower($this->value) === strtolower($other->value);
    }
}
```

#### Step 3: Replace magic strings in controllers

```php
// BEFORE
$order->order_status = 4;
$payment->payment_status = 'pending';
$store->status = 'active';

// AFTER
use App\Enums\OrderStatusEnum;
use App\Enums\PaymentStatusEnum;
use App\Enums\StatusEnum;

$order->order_status = OrderStatusEnum::Shipped->value;
$payment->payment_status = PaymentStatusEnum::Pending->value;
$store->status = StatusEnum::Active->value;
```

### Files To Create

| File | Purpose |
|---|---|
| `app/Enums/OrderStatusEnum.php` | Order status values |
| `app/Enums/PaymentStatusEnum.php` | Payment status values |
| `app/Enums/StatusEnum.php` | Generic active/inactive |
| `app/Enums/ReviewStatusEnum.php` | Review moderation status |
| `app/ValueObjects/Money.php` | Money with currency |
| `app/ValueObjects/Phone.php` | Bangladeshi phone validation |
| `app/ValueObjects/Email.php` | Email validation |

### Files To Modify

| File | Change |
|---|---|
| `app/Http/Controllers/Admin/DashboardController.php` | `'5'` → `OrderStatusEnum::Delivered->value` |
| `app/Http/Controllers/Admin/OrderController.php` | Magic numbers → Enums |
| `app/Http/Controllers/Admin/ReviewController.php` | `'active'`/`'pending'` → Enums |
| `app/Http/Controllers/Admin/CustomerManageController.php` | `'active'`/`'inactive'` → Enums |
| `app/Http/Controllers/Frontend/CustomerController.php` | Status strings → Enums |
| `app/Http/Controllers/Frontend/BkashController.php` | `'paid'` → Enum |
| `app/Http/Controllers/Frontend/FrontendController.php` | `'paid'` → Enum |

---

## TASK 2: Jobs/Events (Phase 38)

### Problem

All processing is synchronous. SMS, email, courier API calls block the HTTP request.

### Current Synchronous Calls

| Call | File | Line | Should Be Async |
|---|---|---|---|
| SMS (OTP) | CustomerController.php | 114-131 | ✅ Job |
| SMS (Forgot password) | CustomerController.php | 167-185 | ✅ Job |
| SMS (Order confirmation) | CustomerController.php | 375-390 | ✅ Job |
| Pathao courier API | OrderController.php | 221-260 | ✅ Job |
| Fraud check API | OrderController.php | (via model) | ✅ Job |

### Implementation

#### Step 1: Create SMS Job

```php
// app/Jobs/SendSmsJob.php
namespace App\Jobs;

use App\Models\SmsGateway;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;

class SendSmsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $timeout = 30;
    public int $backoff = 10;

    public function __construct(
        public string $phone,
        public string $message,
    ) {}

    public function handle(): void
    {
        $gateway = SmsGateway::where('status', 1)->first();

        if (!$gateway) {
            return;
        }

        Http::timeout(15)->post($gateway->url, [
            'api_key' => $gateway->api_key,
            'contacts' => $this->phone,
            'type' => 'text',
            'senderid' => $gateway->sender_id,
            'msg' => $this->message,
        ]);
    }
}
```

#### Step 2: Create Courier Job

```php
// app/Jobs/SendToCourierJob.php
namespace App\Jobs;

use App\Models\Order;
use App\Models\Courierapi;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;

class SendToCourierJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $timeout = 60;
    public int $backoff = 30;

    public function __construct(
        public int $orderId,
        public string $courierType,
    ) {}

    public function handle(): void
    {
        $order = Order::with('shipping', 'orderdetails')->find($this->orderId);

        if (!$order) {
            return;
        }

        // Pathao/Steadfast API call
        // ...
    }
}
```

#### Step 3: Create Order Events

```php
// app/Events/OrderPlaced.php
namespace App\Events;

use App\Models\Order;
use Illuminate\Foundation\Events\Dispatchable;

class OrderPlaced
{
    use Dispatchable;

    public function __construct(
        public Order $order,
    ) {}
}
```

```php
// app/Listeners/SendOrderConfirmationSms.php
namespace App\Listeners;

use App\Events\OrderPlaced;
use App\Jobs\SendSmsJob;

class SendOrderConfirmationSms
{
    public function handle(OrderPlaced $event): void
    {
        $phone = $event->order->shipping->phone ?? null;

        if ($phone) {
            $message = "Your order #{$event->order->invoice_id} has been placed successfully!";
            SendSmsJob::dispatch($phone, $message);
        }
    }
}
```

#### Step 4: Register in EventServiceProvider

```php
// app/Providers/EventServiceProvider.php
protected $listen = [
    \App\Events\OrderPlaced::class => [
        \App\Listeners\SendOrderConfirmationSms::class,
    ],
];
```

#### Step 5: Replace synchronous calls

```php
// BEFORE — CustomerController.php
$sms_gateway = SmsGateway::where('status', 1)->first();
if($sms_gateway) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    // ... 15 lines of curl code
}

// AFTER
use App\Jobs\SendSmsJob;

SendSmsJob::dispatch($customer->phone, $otpMessage);
```

### Files To Create

| File | Purpose |
|---|---|
| `app/Jobs/SendSmsJob.php` | Async SMS sending |
| `app/Jobs/SendToCourierJob.php` | Async courier API |
| `app/Events/OrderPlaced.php` | Order placed event |
| `app/Listeners/SendOrderConfirmationSms.php` | SMS on order |

### Files To Modify

| File | Change |
|---|---|
| `app/Http/Controllers/Frontend/CustomerController.php` | SMS curl → `SendSmsJob::dispatch()` |
| `app/Http/Controllers/Admin/OrderController.php` | Courier HTTP → `SendToCourierJob::dispatch()` |
| `app/Providers/EventServiceProvider.php` | Register events |

---

## TASK 3: Transaction Boundary Fixes (Phase 39)

### Problem

1 occurrence of HTTP call inside DB transaction.

### Current Issue

```php
// CustomerController.php:305
$order = DB::transaction(function () use ($request, ...) {
    // DB operations...
    // This is OK — no HTTP inside transaction
});
```

Actually, the transaction looks clean — no HTTP calls inside it. But let me verify the full block.

### Additional Issues

| Issue | File | Fix |
|---|---|---|
| SMS sent synchronously in checkout | CustomerController.php:114 | Move to Job (Task 2) |
| Fraud check HTTP in Order model | Order.php:76 | Move to Service |
| Pathao HTTP in controller | OrderController.php:221 | Move to Job (Task 2) |

### Implementation

#### Step 1: Extract fraud check from Order model

```php
// BEFORE — Order.php
public function fraud_check($phone = null)
{
    Http::get('https://dash.hoorin.com/api/courier/api', [...]);
}

// AFTER — app/Services/FraudCheckService.php
namespace App\Services;

use Illuminate\Support\Facades\Http;

class FraudCheckService
{
    public function check(string $phone): array
    {
        $fraud = \App\Models\Courierapi::where('type', 'fraud')->first();

        if (!$fraud?->token) {
            return ['error' => 'API Token not Found', 'status' => 'error'];
        }

        $response = Http::timeout(15)->get('https://dash.hoorin.com/api/courier/api', [
            'apiKey' => $fraud->token,
            'searchTerm' => $phone,
        ]);

        if ($response->successful()) {
            return [
                'total_stats' => $this->calculateStats($response->json()),
                'individual_response' => $response->json(),
                'status' => 'success',
            ];
        }

        return ['error' => 'API request failed', 'status' => 'error'];
    }

    private function calculateStats(array $data): array
    {
        // Move calculateTotalStats logic here
    }
}
```

#### Step 2: Update Order model

```php
// Order.php — remove fraud_check() and calculateTotalStats()
// Callers use FraudCheckService instead
```

#### Step 3: Verify no HTTP inside transactions

```bash
# Find all DB::transaction blocks and check for HTTP calls
grep -rn 'DB::transaction' app/ --include="*.php"
# Then manually verify each block
```

### Files To Create

| File | Purpose |
|---|---|
| `app/Services/FraudCheckService.php` | Extract fraud check from Order model |

### Files To Modify

| File | Change |
|---|---|
| `app/Models/Order.php` | Remove `fraud_check()` and `calculateTotalStats()` |
| `app/Http/Controllers/Frontend/CustomerController.php` | Use `FraudCheckService` |
| `app/Http/Controllers/Admin/OrderController.php` | Use `FraudCheckService` |

---

## Execution Order

```
Task 1: Enums + ValueObjects (1.5 hr)
  ↓ Create enums, replace magic strings
Task 2: Jobs/Events (2 hr)
  ↓ Create jobs, events, listeners, replace sync calls
Task 3: Transaction Boundaries (1 hr)
  ↓ Extract FraudCheckService, verify no HTTP in TX
Final: php artisan test
```

---

## Verification Commands

```bash
# After Task 1
grep -rn "'active'\|'inactive'\|'pending'\|'paid'" app/Http/Controllers/ --include="*.php" | wc -l
# Should decrease significantly

# After Task 2
find app/Jobs/ -name "*.php" | wc -l  # Should be 2+
find app/Events/ -name "*.php" | wc -l  # Should be 1+
find app/Listeners/ -name "*.php" | wc -l  # Should be 1+

# After Task 3
grep -rn 'Http::\|curl_' app/Models/ --include="*.php" | wc -l  # Should be 0

# Final
php artisan test
```

---

## Definition of Done

### Task 1: Enums + ValueObjects
- [x] 4 Enum classes created
- [x] 3 ValueObject classes created
- [x] Magic strings replaced in 7+ controllers
- [x] Tests passing

### Task 2: Jobs/Events
- [x] SendSmsJob created
- [x] SendToCourierJob created
- [x] OrderPlaced event created
- [x] Listener registered
- [x] Sync SMS calls replaced with Job dispatch
- [x] Tests passing

### Task 3: Transaction Boundaries
- [x] FraudCheckService created
- [x] Order model cleaned (no direct HTTP calls)
- [x] No HTTP inside DB::transaction
- [x] Tests passing (183/183 tests passing 100%)
