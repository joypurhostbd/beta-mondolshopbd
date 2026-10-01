# DEVELOPMENT-RULES.md — MondolShopBD

> এই ফাইলটি প্রতিটি ডেভেলপার এবং AI এজেন্টকে কঠোরভাবে অনুসরণ করতে হবে।

---

## ০. Mandatory Implementation Plan & Approval Workflow (Hard Rule)

> [!CAUTION]
> **কঠোর নির্দেশিকা (Hard Rule)**: কোনো অবস্থাতেই `Implementation Plan` ছাড়া কোনো প্রকার কোড পরিবর্তন (modify), নতুন কোড তৈরি/যোগ (add/create), বা ডিলিট (delete) করা যাবে না।

### কঠোর কাজের নিয়মাবলী (Approval & Engineering Hard Rules):
1. **সর্বদা প্রথমে বিশ্লেষণ ও রিসার্চ**: যেকোনো রিকোয়েস্ট পাওয়ার পর প্রথমে কোডবেজ এবং ডকস পুঙ্খানুপুঙ্খ রিসার্চ করতে হবে (Read-only Research)।
2. **বাধ্যতামূলক Implementation Plan**: কোড পরিবর্তনের পূর্বে সম্পূর্ণ এবং বিস্তারিত `implementation_plan.md` প্রস্তুত করতে হবে।
3. **ব্যবহারকারীর অনুমোদন গ্রহণ**: প্ল্যান তৈরির পর ব্যবহারকারীর সুস্পষ্ট অনুমোদনের (Explicit Approval) জন্য অপেক্ষা করতে হবে।
4. **অনুমোদনের পর বাস্তবায়ন**: ব্যবহারকারী অনুমোদন দিলেই কেবল কোডবেজে পরিবর্তন বা কোড যুক্ত করার কাজ শুরু করা যাবে।
5. **জিরো ডিরেক্ট এডিট**: অনুমোদন ছাড়া সরাসরি কোড এডিট করা সম্পূর্ণ নিষিদ্ধ।
6. **ইনক্রিমেন্টাল আপডেট (Incremental Evolution)**: কোড সর্বদা ধাপে ধাপে ইনক্রিমেন্টাল পদ্ধতিতে আপডেট করতে হবে। কোনো বড় বিগ-ব্যাং পরিবর্তন করা যাবে না।
7. **জিরো ডুপ্লিকেট কোড (DRY Principle)**: কোডবেজে কোনো প্রকার ডুপ্লিকেট বা রিডানড্যান্ট কোড লেখা যাবে না। পুনঃব্যবহারযোগ্য লজিক শেয়ার্ড সার্ভিস, ট্রেইট বা হেল্পারে রাখতে হবে।
8. **কোডে বাধ্যতামূলক ইংরেজি ভাষা (English in Code)**: কোডবেজের অভ্যন্তরে (Variables, Functions, Classes, Methods, Comments, Docblocks, Logs এবং Git Commit Messages) সর্বদা বাধ্যতামূলকভাবে স্ট্যান্ডার্ড ইংরেজি (English) ভাষা ব্যবহার করতে হবে।

---

## ১. Controller Rules

```php
// ✅ CORRECT: Thin controller — only orchestrate
class ProductController extends Controller
{
    public function store(StoreProductRequest $request, CreateProductAction $action): RedirectResponse
    {
        $action->execute($request->toDTO());
        return redirect()->route('products.index')->with('success', 'Product created.');
    }
}

// ❌ WRONG: Fat controller with business logic
class ProductController extends Controller
{
    public function store(Request $request)
    {
        $this->validate($request, [...]); // NO
        $slug = Str::slug($request->name); // NO
        $product = Product::create($request->all()); // NO
        // image upload logic... // NO
    }
}
```

**Rules:**
- MAX 15 lines per method
- Only: receive FormRequest → call Service/Action → return response
- NO validation (use FormRequest)
- NO queries (use Repository via Service)
- NO business logic (use Service/Action)
- NO `compact()` (use ViewModel)

---

## ২. Service Rules

```php
// ✅ CORRECT: Service orchestrates business logic
class ProductService
{
    public function __construct(
        private ProductRepository $repository,
        private ImageService $imageService,
    ) {}

    public function createProduct(ProductData $data): Product
    {
        $product = $this->repository->create($data->toArray());
        $this->imageService->uploadMultiple($product, $data->images);
        return $product;
    }
}
```

**Rules:**
- Orchestrates business logic across repositories/services
- NO direct Eloquent queries — use Repository
- NO Facades — inject dependencies via constructor
- Returns domain objects (Models, DTOs), never raw arrays

---

## ৩. Repository Rules

```php
// ✅ CORRECT: Repository handles all data access
class ProductRepository extends BaseRepository
{
    public function __construct(Product $model)
    {
        parent::__construct($model);
    }

    public function getActiveWithImages(): Collection
    {
        return $this->model->where('status', 1)
            ->with('image')
            ->select('id', 'name', 'slug', 'new_price', 'old_price')
            ->get();
    }
}
```

**Rules:**
- ALL Eloquent queries go here
- NO business logic — only data access
- Always use `select()` to limit columns
- Always eager load relations (no N+1)
- Use query scopes for reusable filters

---

## ৪. DTO Rules

```php
// ✅ CORRECT: Typed DTO with spatie/laravel-data
class ProductData extends Data
{
    public function __construct(
        public readonly string $name,
        public readonly int $categoryId,
        public readonly float $newPrice,
        public readonly float $purchasePrice,
        public readonly int $stock,
        public readonly string $description,
        public readonly ?string $slug = null,
        public readonly bool $status = true,
        public readonly array $images = [],
        public readonly array $sizes = [],
        public readonly array $colors = [],
    ) {}
}
```

**Rules:**
- All properties typed and readonly
- Created from FormRequest via `::from($request)`
- Immutable — no setters
- Used for data transfer between layers

---

## ৫. FormRequest Rules

```php
// ✅ CORRECT: Dedicated FormRequest
class StoreProductRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'new_price' => 'required|numeric|min:0',
            'purchase_price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'description' => 'required|string',
            'image.*' => 'image|mimes:jpg,png,webp|max:2048',
        ];
    }

    public function toDTO(): ProductData
    {
        return ProductData::from($this->validated());
    }
}
```

**Rules:**
- EVERY controller method that accepts input has a FormRequest
- Include `toDTO()` method for DTO creation
- NO validation in controllers

---

## ৬. ViewModel Rules

```php
// ✅ CORRECT: ViewModel prepares view data
class ProductIndexViewModel extends ViewModel
{
    public function __construct(
        private ProductService $service,
    ) {}

    public function products(): LengthAwarePaginator
    {
        return $this->service->getPaginated();
    }

    public function categories(): Collection
    {
        return $this->service->getCategories();
    }
}
```

**Rules:**
- Replaces `compact()` in controllers
- Type-safe view data
- Can cache data internally
- NO queries in Blade files

---

## ৭. Model Rules

```php
// ✅ CORRECT: Clean model
class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'slug', 'category_id', 'new_price',
        'old_price', 'purchase_price', 'stock', 'status',
    ];

    protected $casts = [
        'new_price' => 'decimal:2',
        'status' => 'boolean',
    ];

    // Relations only
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    // Scopes only
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 1);
    }
}
```

**Rules:**
- `$fillable` — NEVER `$guarded = []`
- `$casts` for all typed columns
- Relations, Scopes, Accessors ONLY
- NO business logic (no `fraud_check()` in Order model)
- NO HTTP calls

---

## ৮. Enum Rules

```php
// ✅ CORRECT: PHP 8.1+ Enum
enum OrderStatus: string
{
    case Pending = '1';
    case Confirmed = '2';
    case Processing = '3';
    case Shipped = '4';
    case Delivered = '5';
    case Cancelled = '6';
}
```

**Rules:**
- ALL status/type constants as Enums
- NO magic strings or numbers in code
- Use `OrderStatus::Pending->value` for DB storage

---

## ৯. Route Rules

```php
// ✅ CORRECT: Module routes with middleware group
Route::middleware(['auth', 'verified'])->prefix('admin')->group(function () {
    Route::resource('products', ProductController::class);
});

// ❌ WRONG: Individual route definitions for each CRUD action
Route::get('products/manage', ...);
Route::get('products/create', ...);
Route::post('products/save', ...);
```

**Rules:**
- Use `Route::resource()` where possible
- Group by middleware + prefix
- Module-specific routes in module's `routes/web.php`

---

## ১০. General Rules

| Rule | Description |
|---|---|
| **No Facades in Services** | Inject dependencies |
| **No queries in Blade** | Use ViewModel |
| **No raw arrays** | Use DTOs or Collections |
| **No magic numbers** | Use Enums or Constants |
| **No `$request->all()`** | Use `$request->validated()` or DTO |
| **No `compact()`** | Use ViewModel |
| **No inline validation** | Use FormRequest |
| **No business logic in Model** | Use Service/Action |
| **No business logic in Controller** | Use Service/Action |
| **Always type-hint** | Return types, parameter types |
| **Always use `select()`** | Never `SELECT *` |
| **Always eager load** | No N+1 queries |

---

## ১১. Authorization Rules

```php
// ✅ CORRECT: Policy-based authorization
class ProductController extends Controller
{
    public function update(UpdateProductRequest $request, Product $product): RedirectResponse
    {
        $this->authorize('update', $product);
        // ...
    }
}

// ❌ WRONG: No authorization check
class ProductController extends Controller
{
    public function update(Request $request, $id)
    {
        $product = Product::find($id); // Any authenticated user can update any product
        $product->update($request->all());
    }
}
```

**Rules:**
- EVERY sensitive resource has a Policy class
- Use `$this->authorize()` or `Gate::authorize()` in controllers
- Object-level ownership checks (not just "is authenticated")
- Spatie `permission` middleware on route groups — NOT just in controller constructor
- Never trust `?id=10` from client without ownership verification (IDOR prevention)

**Actual Problem:** 0 Policy classes exist. Spatie RBAC installed but 0 routes use `role`/`permission` middleware.

---

## ১২. Security Rules

### ১২.১ XSS Prevention
```php
// ✅ CORRECT: Blade escaping (default)
{{ $product->description }}

// ❌ WRONG: Raw HTML from user input
{!! $product->description !!}  // Stored XSS risk
```

**Rules:**
- NEVER use `{!! !!}` with user-controlled or DB-stored content
- If rich text is needed, sanitize with a whitelist library (e.g., HTMLPurifier)
- Admin-entered content (`description`, `admin_note`, `meta_description`) is user-controlled

**Actual Problem:** 6 templates render raw DB content via `{!! !!}` — stored XSS risk.

### ১২.২ File Upload Security
```php
// ✅ CORRECT: Secure upload
$path = $request->file('image')->store('products', 'public');
// Laravel generates random filename, validates MIME

// ❌ WRONG: Raw move with original filename
$name = time() . '-' . $image->getClientOriginalName(); // Path traversal risk
$image->move($uploadPath, $name);
```

**Rules:**
- Use Laravel's `store()` or `putFile()` — never raw `move()`
- Validate MIME type: `'image|mimes:jpg,png,webp|max:2048'`
- Never trust `getClientOriginalName()` for storage path
- Store outside `public/` or use signed URLs for private files

**Actual Problem:** 10 controllers use raw `getClientOriginalName()` + `move()`.

### ১২.৩ Rate Limiting
```php
// ✅ CORRECT: Throttle middleware
Route::post('/login', [...])->middleware('throttle:5,1');
Route::post('/otp/resend', [...])->middleware('throttle:3,1');
```

**Rules:**
- Rate limit: login, OTP, password reset, checkout, payment callbacks
- Use `throttle` middleware or `RateLimiter::for()`

### ১২.৪ Secrets & Debug Code
**Rules:**
- NEVER commit `.env` files
- NEVER leave `dd()` / `dump()` in production code (even commented out)
- NEVER use `env()` outside `config/` files
- NEVER log passwords, OTP values, API secrets, or payment credentials

**Actual Problem:** 4 commented-out `dd()` calls found in production code.

---

## ১৩. Database Rules

### ১৩.১ Foreign Key Constraints
```php
// ✅ CORRECT: FK constraint
Schema::table('products', function (Blueprint $table) {
    $table->foreignId('category_id')->constrained()->cascadeOnDelete();
});

// ❌ WRONG: Integer column with no FK
Schema::table('products', function (Blueprint $table) {
    $table->integer('category_id'); // No referential integrity
});
```

**Rules:**
- EVERY relational column must have a FK constraint
- Use `foreignId()->constrained()` or `$table->foreign()`
- Define `onDelete` behavior: `cascade`, `set null`, or `restrict`
- Clean orphan records before adding FK to existing tables

**Actual Problem:** 0 FK constraints across 19 tables with integer FK columns.

### ১৩.২ Column Types
**Rules:**
- Money: `decimal(12,2)` — NEVER `integer` or `float`
- Status: `tinyInteger` with default — NEVER `string(55)`
- Foreign keys: `unsignedBigInteger` — NEVER plain `integer`
- Dates: `date` / `timestamp` — NEVER `string`
- Boolean flags: `tinyInteger(1)` or `boolean` — NEVER `string`

**Actual Problem:** Money stored as `integer` in 5 tables. `customers.balance` is `float`. Status is `string(55)` in 6 tables.

### ১৩.৩ Indexes
**Rules:**
- Add indexes based on actual query patterns (use `EXPLAIN`)
- Composite indexes for multi-column WHERE clauses
- Unique indexes for slugs, emails, phones, invoice IDs
- Do NOT blindly add indexes — each needs a reason

### ১৩.৪ Migrations
**Rules:**
- Backward-compatible migrations only (expand/contract pattern)
- Always implement `down()` method
- Never combine destructive schema changes with code changes
- Sequence: add new → write both → read new → remove old

---

## ১৪. Relationship Rules

```php
// ✅ CORRECT: Product belongs to Category (Product has category_id FK)
public function category(): BelongsTo
{
    return $this->belongsTo(Category::class);
}

// ❌ WRONG: hasOne with inverted FK
public function category(): HasOne
{
    return $this->hasOne(Category::class, 'id', 'category_id');
}
```

**Rules:**
- If the model has the FK column → `belongsTo`
- If the related model has the FK column → `hasOne` / `hasMany`
- Pivot tables → `belongsToMany`
- Never use `hasOne` where `belongsTo` is semantically correct

**Actual Problem:** 15 models use `hasOne` where `belongsTo` is needed.

---

## ১৫. Financial Rules

```php
// ✅ CORRECT: Decimal money
$product->new_price; // decimal(12,2) in DB, cast to string/float

// ❌ WRONG: Integer money (loses fractional amounts)
$product->new_price; // integer — ৳150.50 stored as 150 or 151

// ❌ WRONG: Float money (rounding errors)
$customer->balance; // float — ৳100.01 + ৳0.01 = ৳100.020000000000003
```

**Rules:**
- All money columns: `decimal(12,2)` minimum
- Never use `float` for financial data
- Never use `integer` for money unless using minor units (paisa) consistently
- Payment commands must be idempotent (unique operation key)
- Stock mutations must use `lockForUpdate()` or atomic SQL
- No external HTTP calls inside DB transactions
- All financial state transitions must be explicit and logged

**Actual Problem:** `products.purchase_price`, `orders.amount`, `payments.amount` are `integer`. `customers.balance` is `float`.

---

## ১৬. State Machine Rules

```php
// ✅ CORRECT: Explicit state transitions
enum OrderStatus: string
{
    case Pending = 'pending';
    case Confirmed = 'confirmed';
    case Shipped = 'shipped';
    case Delivered = 'delivered';
    case Cancelled = 'cancelled';

    public function canTransitionTo(self $next): bool
    {
        return match($this) {
            self::Pending => in_array($next, [self::Confirmed, self::Cancelled]),
            self::Confirmed => in_array($next, [self::Shipped, self::Cancelled]),
            self::Shipped => in_array($next, [self::Delivered]),
            default => false,
        };
    }
}

// ❌ WRONG: Direct status assignment
$order->order_status = $request->status; // Any status → any status
```

**Rules:**
- All status fields use Enums
- State transitions are validated before applying
- Invalid transitions throw domain exceptions
- Status history is logged (audit trail)

---

## ১৭. External Integration Rules

```php
// ✅ CORRECT: Port/Adapter pattern
interface PaymentGateway
{
    public function createIntent(Amount $amount): PaymentIntent;
    public function capture(string $intentId): PaymentResult;
    public function verifyWebhook(Request $request): WebhookEvent;
}

class BkashGateway implements PaymentGateway { /* ... */ }
class ShurjoPayGateway implements PaymentGateway { /* ... */ }

// ❌ WRONG: Direct HTTP in controller/model
Http::post('https://bkash.com/api/pay', [...]); // In controller
```

**Rules:**
- All external APIs behind application-defined interfaces (ports)
- Vendor-specific implementations are adapters
- Vendor payloads never leak into domain code
- Define: timeout, retry policy, error normalization
- Webhook handlers: signature verification + idempotent processing
- Never blindly retry non-idempotent financial operations

**Actual Problem:** bKash API called directly in `BkashController`. Pathao API called directly in `OrderController`. Hoorin API called inside `Order` model.

---

## ১৮. Async / Queue Rules

```php
// ✅ CORRECT: Job with retry + timeout
class ProcessOrderFulfillment implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $timeout = 60;
    public int $backoff = 30;

    public function handle(): void { /* ... */ }
}

// ❌ WRONG: Synchronous heavy processing in controller
public function store(Request $request)
{
    $order = Order::create([...]);
    Http::post('courier-api', [...]); // Blocks request
    Sms::send($phone, 'Order placed'); // Blocks request
    Mail::send(new OrderConfirmation($order)); // Blocks request
}
```

**Rules:**
- Heavy operations dispatched to queues: SMS, email, image processing, courier API
- Jobs define `$tries`, `$timeout`, `$backoff`
- Idempotent job handlers (safe to retry)
- Never put secrets or huge ORM graphs in queue payloads
- Use `afterCommit` for jobs that depend on DB state

**Actual Problem:** 0 Job classes, 0 Event classes, 0 Listener classes. All processing is synchronous.

---

## ১৯. Testing Rules

**Rules:**
- Characterization tests BEFORE refactoring any business-critical code
- Every new Action/Service has unit tests
- Every modified endpoint has feature tests
- Financial workflows have concurrency tests
- Security-sensitive changes have regression tests
- Run `php artisan test` before every commit

**Actual Problem:** 0 custom tests. Only default Laravel stubs exist.

---

## ২০. Module Boundary Rules

```
Module A → Module B: ONLY through contracts/interfaces
Module A → Module B Model: ❌ FORBIDDEN
Module A → Module B Repository: ❌ FORBIDDEN
Module A → Module B Service: ❌ FORBIDDEN (use contract)
```

**Rules:**
- Modules communicate through contracts (interfaces), DTOs, or domain events
- No module reaches another module's Eloquent models directly
- No cross-module database joins (except approved read/reporting)
- Shared kernel stays small (ValueObjects, Enums, base classes)
- Cyclic dependencies prohibited
- Architecture tests enforce boundaries in CI

---

## ২১. Frontend / Blade Rules

**Rules:**
- NO database queries in Blade files (use ViewModel)
- NO `{!! !!}` with user/DB content (XSS)
- ALL forms have `@csrf`
- Inline JavaScript minimized — prefer modular JS files
- AJAX endpoints return JSON via API Resources, not raw arrays
- Blade components for repeated UI patterns

**Actual Problem:** 15 direct `Model::` calls in Blade. 321 inline `<script>` tags. ~16 forms missing `@csrf`.

---

## ২২. Observability Rules

**Rules:**
- Structured JSON logs for production
- Log significant state changes (order status, payment, stock)
- NEVER log: passwords, OTP, API secrets, payment credentials
- Every critical external integration has error logging
- Request ID / trace ID for debugging

---

## ২৩. Deployment Rules

**Rules:**
- Backward-compatible migrations only
- Queue workers safely restarted during deployment
- Health/readiness endpoints for load balancers
- Immutable build artifacts (never `composer update` on production)
- Rollback plan for every risky change
