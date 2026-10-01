# MondolShopBD — Unified Modular Monolith Refactoring Plan (Merged)

> **Target**: Laravel 13 + PHP 8.5 + MySQL + Redis  
> **Architecture**: Modular Monolith + DDD-lite + CQRS-lite + Ports & Adapters  
> **Approach**: Incremental Strangler Migration (each phase independently deployable)  
> **Source**: Merged from `MODULAR-MONOLITH-REFACTORING-PLAN.md` + `MONDOLSHOPBD-ADVANCED-MODULAR-MONOLITH-ARCHITECTURE.md`

---

## ১. Executive Summary

বর্তমান Laravel 9 flat-MVC অ্যাপ্লিকেশনকে ধাপে ধাপে একটি **secure, testable, scalable, observable** modular monolith এ রূপান্তর করা।

**লক্ষ্য আর্কিটেকচার:**
```
HTTP / Console / Queue / Webhook
                │
                ▼
        Presentation Layer (Transport only)
                │
                ▼
     Application / Use Cases (Commands + Queries)
                │
        ┌───────┴────────┐
        ▼                ▼
  Domain Model      Integration Ports
   Aggregates         / Contracts
        │                │
        └───────┬────────┘
                ▼
        Infrastructure
  Eloquent / Redis / HTTP
  Payment / Courier / Files
                │
                ▼
       MySQL / Redis / APIs
```

**মূল নীতি:** Business correctness → Maintainability → Performance

---

## ২. Current State Analysis

| Metric | Current | Target |
|---|---|---|
| Laravel | 9.19 | 13.x |
| PHP | ^8.0.2 | 8.5 |
| Architecture | Flat MVC | Modular Monolith (Bounded Contexts) |
| Business Logic | In Controllers | Use Cases / Commands |
| Data Access | Direct Eloquent | Repository (where meaningful) + Query Objects |
| Validation | Inline `$this->validate()` | FormRequest |
| Data Transfer | Raw `$request->all()` | DTOs (at boundaries only) |
| View Data | `compact()` | ViewModels / Read Models |
| Cache/Session | File | Redis |
| Queue | sync | Redis (critical/normal/slow queues) |
| Tests | 0 | Unit + Integration + Feature + Contract + Concurrency |
| DB Indexes | Minimal | Query-pattern driven |
| Models | `$guarded = []` | `$fillable` + casts + invariants |
| Authorization | None | Policies + object-level checks |
| Security | Basic | IDOR defense, webhook verification, audit log |

---

## ৩. Current Problems (Why Refactor?)

### 🔴 P0 — Critical Security / Correctness
1. **No Authorization** — Any authenticated user can access any resource (IDOR)
2. **Mass Assignment Risk** — All 36 models use `$guarded = []`
3. **No Webhook Verification** — Payment/courier webhooks unprotected
4. **No Payment Idempotency** — Duplicate charges possible on retry
5. **Stock Race Conditions** — No locking on inventory mutations
6. **Secret Exposure Risk** — `.env` handling, no secret rotation

### 🟠 P1 — Reliability
7. **No Transactional Outbox** — Events lost between DB commit and external calls
8. **HTTP inside DB Transactions** — Payment/courier calls inside `DB::beginTransaction()`
9. **No Audit Trail** — Sensitive operations untracked
10. **File-based Cache/Session** — Slow, not scalable

### 🟡 P2 — Architecture
11. **Fat Controllers** — `CustomerController::order_save()` is 170+ lines
12. **No Separation of Concerns** — Validation, business logic, persistence all mixed
13. **Business Logic in Models** — `Order::fraud_check()` makes HTTP calls
14. **No Module Boundaries** — Everything imports everything

### ℹ️ P3 — Performance / DX
15. **N+1 Queries** — Frontend loads relations without eager loading
16. **No Tests** — Zero coverage
17. **Deprecated Packages** — `laravelcollective/html`, `olimortimer/laravelshoppingcart`

---

## ৪. Target Architecture

### ৪.১ Bounded Contexts / Business Modules

```
Identity          → Customer auth, OTP, roles, permissions
Catalog           → Product, Variant, Category, Brand, Media
Pricing           → Price lists, discounts, coupons, campaigns
Cart              → Guest/customer cart, Redis-backed
Inventory         → Stock ledger, reservation, availability
Order             → Order aggregate, state machine, checkout
Payment           → Payment intent, gateway abstraction, reconciliation
Shipping          → Shipment, courier abstraction, tracking
Review            → Reviews, moderation, rating aggregation
Campaign          → Campaigns, participation, campaign reviews
Content           → Banners, pages, social links, contact
Settings          → App settings, shop config, feature flags
Operations        → Notifications, audit log, jobs, health checks
```

### ৪.২ Module Internal Structure

```
Modules/Order/
├── Domain/
│   ├── Aggregates/        # Order, OrderLine
│   ├── ValueObjects/      # Money, OrderStatus
│   ├── Events/            # OrderPlaced, OrderCancelled
│   ├── Exceptions/        # InvalidOrderTransition
│   ├── Policies/          # OrderPolicy
│   └── Contracts/         # OrderRepositoryInterface
├── Application/
│   ├── Commands/          # PlaceOrder, CancelOrder
│   ├── Queries/           # GetOrderDetails, ListOrders
│   ├── DTOs/              # PlaceOrderData
│   ├── Handlers/          # PlaceOrderHandler
│   └── Services/          # OrderStateMachine
├── Infrastructure/
│   ├── Persistence/       # EloquentOrderRepository
│   ├── Jobs/              # ProcessOrderFulfillment
│   ├── Listeners/         # SendOrderConfirmation
│   └── Integrations/      # CourierAdapter
├── Presentation/
│   ├── Http/
│   │   ├── Controllers/   # OrderController (thin)
│   │   ├── Requests/      # PlaceOrderRequest
│   │   └── Resources/     # OrderResource
│   └── Webhooks/          # PaymentWebhookController
├── routes/
└── tests/
```

### ৪.৩ Repository Layout

```
app/
├── Domain/                # Pure business logic (no Laravel dependency)
│   ├── Identity/
│   ├── Catalog/
│   ├── Pricing/
│   ├── Cart/
│   ├── Inventory/
│   ├── Order/
│   ├── Payment/
│   ├── Shipping/
│   ├── Review/
│   ├── Campaign/
│   └── Content/
│
├── Modules/               # Laravel-specific implementations
│   ├── Identity/
│   ├── Catalog/
│   ├── Pricing/
│   ├── Cart/
│   ├── Inventory/
│   ├── Order/
│   ├── Payment/
│   ├── Shipping/
│   ├── Review/
│   ├── Campaign/
│   └── Content/
│
├── Shared/                # Cross-module shared code
│   ├── Bus/               # Command/Query bus
│   ├── Contracts/         # Shared interfaces
│   ├── Exceptions/
│   ├── ValueObjects/      # Money, Email, Phone
│   ├── Security/
│   ├── Observability/
│   └── Support/
│
└── Infrastructure/        # Technical implementations
    ├── Persistence/
    ├── Cache/
    ├── Queue/
    ├── Payments/          # BkashGateway, ShurjoPayGateway
    ├── Shipping/          # PathaoAdapter, SteadfastAdapter
    ├── Messaging/         # SMS, Email
    ├── Files/
    └── Http/
```

---

## ৫. Key Architecture Decisions

### ৫.১ Repository — Where It Makes Sense Only
```
Simple read/query    → Eloquent query object / dedicated Query class
Aggregate persistence → Repository / persistence port
Complex reporting    → Read model / query service
```
**NOT** every Eloquent call needs a repository wrapper.

### ৫.২ Use Cases Over Generic Services
Replace giant `ProductService`, `OrderService` with explicit use cases:
- `PlaceOrder`, `CancelOrder`, `CreateProduct`, `PublishProduct`
- `RegisterCustomer`, `CapturePayment`, `RefundPayment`

### ৫.৩ DTOs at Boundaries Only
- HTTP → Application
- Application → External integration
- Async message/event contracts
- **NOT** for every internal method call

### ৫.৪ CQRS-lite (Not Full Event Sourcing)
- Commands change state
- Queries return read-oriented data (may use direct Eloquent)
- Audit log + domain events + outbox (not full event sourcing)

### ৫.৫ Module Boundary Enforcement
- No module reaches another module's Eloquent models directly
- No cross-module writes without contracts/events
- Shared kernel stays small
- Cyclic dependencies prohibited

---

## ৬. Critical Architecture Components

### ৬.১ Transactional Outbox
```
DB Transaction
 ├─ business state change
 └─ outbox_messages insert
             ↓
          COMMIT
             ↓
     Outbox dispatcher → queue → handler
```

### ৬.২ Order State Machine
```
DRAFT → PENDING_CONFIRMATION → INVENTORY_RESERVED → PAYMENT_PENDING
  ├→ PAYMENT_FAILED
  ↓
PAID → FULFILLMENT_PENDING → SHIPPED → DELIVERED

Exceptions:
DRAFT → CANCELLED
PAID → REFUND_PENDING → REFUNDED
```

### ৬.৩ Inventory Reservation
```
BEGIN TRANSACTION
  lock inventory rows (lockForUpdate)
  verify availability
  create reservation / ledger entry
  update available quantity
COMMIT
```

### ৬.৪ Payment Architecture
```
PaymentGateway (Port)
├─ createPaymentIntent()
├─ authorize()
├─ capture()
├─ refund()
├─ queryTransaction()
└─ verifyWebhook()

Adapters: BkashGateway, ShurjoPayGateway
```

### ৬.৫ Cart Architecture
```
Redis-backed: cart:{customer-or-guest-id}
Checkout re-reads authoritative data from MySQL
Never trust Redis cart totals for payment
```

### ৬.৬ Pricing Engine
```
PriceEngine
├─ BasePriceRule
├─ CampaignRule
├─ CouponRule
├─ QuantityRule
└─ ShippingChargeRule

Returns: PriceBreakdown (subtotal, discounts, tax, grand_total)
Money: integer minor units or decimal-safe value objects
```

---

## ৭. Phased Implementation (Incremental)

### PHASE 0: Discovery & Characterization
> **Goal**: Document actual current behavior before changing anything  
> **Risk**: None | **Duration**: 1 session

| # | Task | Details |
|---|---|---|
| 0.1 | Route inventory | Map all 455 lines of routes |
| 0.2 | Model relationship map | All 36 models, relations, pivots |
| 0.3 | Database ERD | 39 migrations → schema diagram |
| 0.4 | Integration inventory | Payment, courier, SMS, fraud check APIs |
| 0.5 | Order lifecycle map | Current status flow |
| 0.6 | Payment lifecycle map | ShurjoPay + bKash flows |
| 0.7 | Stock lifecycle map | Current stock management |

---

### PHASE 1: Security Foundation (P0)
> **Goal**: Fix critical security issues on current Laravel 9  
> **Risk**: Low | **Duration**: 2-3 sessions

| # | Task | Details |
|---|---|---|
| 1.1 | Replace `$guarded=[]` → `$fillable` | All 36 models |
| 1.2 | Add authorization policies | `OrderPolicy`, `ProductPolicy`, `CustomerPolicy` |
| 1.3 | Add FormRequests | All store/update methods |
| 1.4 | Fix IDOR vulnerabilities | Object-level ownership checks |
| 1.5 | Add webhook signature verification | Payment + courier webhooks |
| 1.6 | Add payment idempotency | Unique operation keys |
| 1.7 | Add upload security | MIME validation, random names, size limits |
| 1.8 | Add rate limiting | Login, OTP, checkout endpoints |
| 1.9 | Add audit logging | Sensitive operations |
| 1.10 | Secret hygiene | Scan for leaked secrets |

---

### PHASE 2: Reliability Foundation (P1)
> **Goal**: Make the system reliable before restructuring  
> **Risk**: Low-Medium | **Duration**: 2-3 sessions

| # | Task | Details |
|---|---|---|
| 2.1 | Add Redis | `predis/predis`, update `.env` |
| 2.2 | Fix transaction boundaries | No HTTP calls inside DB transactions |
| 2.3 | Add transactional outbox | `outbox_messages` table |
| 2.4 | Add webhook event table | Deduplication + idempotent processing |
| 2.5 | Add inventory locking | `lockForUpdate()` for stock mutations |
| 2.6 | Add inventory ledger | Traceable stock accounting |
| 2.7 | Add payment reconciliation | Scheduled reconciliation jobs |
| 2.8 | Queue architecture | Split: critical/normal/slow queues |
| 2.9 | Health/readiness endpoints | `/liveness`, `/readiness` |

---

### PHASE 3: Laravel/PHP Modernization
> **Goal**: Upgrade to Laravel 13 + PHP 8.5  
> **Risk**: High | **Duration**: 3-4 sessions

| # | Task | Details |
|---|---|---|
| 3.1 | Create characterization tests | Capture current behavior |
| 3.2 | Remove deprecated packages | `laravelcollective/html`, `olimortimer/laravelshoppingcart` |
| 3.3 | Laravel 9 → 10 | PHP 8.1+, update packages |
| 3.4 | Laravel 10 → 11 | Slim skeleton, PHP 8.2+ |
| 3.5 | Laravel 11 → 12 | PHP 8.3+ |
| 3.6 | Laravel 12 → 13 | PHP 8.5, latest patterns |
| 3.7 | Update all packages | Each to Laravel 13 compatible |
| 3.8 | Green test suite | Verify at each checkpoint |

---

### PHASE 4: Domain Seams & Module Structure
> **Goal**: Introduce module boundaries without moving code yet  
> **Risk**: Medium | **Duration**: 2-3 sessions

| # | Task | Details |
|---|---|---|
| 4.1 | Create module skeleton | `app/Modules/`, `app/Domain/`, `app/Shared/` |
| 4.2 | Define module contracts | Interfaces for cross-module communication |
| 4.3 | Create shared kernel | `Money`, `Email`, `Phone` value objects |
| 4.4 | Create enums | `OrderStatus`, `PaymentStatus`, `ProductStatus` |
| 4.5 | Create base classes | `BaseRepository`, `BaseCommand`, `BaseQuery` |
| 4.6 | Setup PHPStan/Larastan | Static analysis baseline |
| 4.7 | Setup architecture tests | Dependency rule enforcement |

---

### PHASE 5: Catalog + Customer Vertical Slices
> **Goal**: Extract first complete end-to-end slices  
> **Risk**: Medium | **Duration**: 3-4 sessions

| # | Task | Details |
|---|---|---|
| 5.1 | **Catalog Module** | |
| | Extract Product domain | Aggregates, value objects, events |
| | Create `CreateProduct` command | Use case handler |
| | Create `ProductRepository` | Persistence port + Eloquent adapter |
| | Create `ProductData` DTO | Boundary data transfer |
| | Create `StoreProductRequest` | Form validation |
| | Create `ProductController` | Thin transport layer |
| | Move views | Module-specific Blade views |
| 5.2 | **Category** | Same pattern (Category, Subcategory, Childcategory) |
| 5.3 | **Brand** | Same pattern |
| 5.4 | **Customer/Identity Module** | |
| | Extract Customer domain | Auth, OTP, profile |
| | Create `RegisterCustomer` command | Registration use case |
| | Create `AuthenticateCustomer` command | Login use case |
| | Extract `SmsService` | Infrastructure port |
| | Create `CustomerController` | Thin transport |

---

### PHASE 6: Cart + Pricing + Inventory
> **Goal**: Establish authoritative pricing and stock semantics  
> **Risk**: Medium-High | **Duration**: 3-4 sessions

| # | Task | Details |
|---|---|---|
| 6.1 | **Cart Module** | |
| | Replace session cart | Redis-backed `CartPort` |
| | Guest → Customer cart merge | |
| 6.2 | **Pricing Module** | |
| | Create `PriceEngine` | Rule-based pricing |
| | Create `PriceBreakdown` | Structured result |
| | Money value object | Integer minor units |
| 6.3 | **Inventory Module** | |
| | Create inventory ledger | `inventory_items` + `inventory_ledger` |
| | Create reservation system | `lockForUpdate()` + reservation records |
| | Stock movement types | PURCHASE, SALE, RETURN, ADJUSTMENT, etc. |

---

### PHASE 7: Order + Payment (Highest Risk)
> **Goal**: Extract the most critical business flows  
> **Risk**: High | **Duration**: 4-5 sessions

| # | Task | Details |
|---|---|---|
| 7.1 | **Order Module** | |
| | Create Order aggregate | Invariants, state machine |
| | Create `PlaceOrder` command | Checkout orchestration |
| | Create `CancelOrder` command | With compensation logic |
| | Extract POS logic | Separate `PosOrder` command |
| | Create `OrderRepository` | Complex queries, DataTables |
| | Create `OrderController` | Thin transport |
| 7.2 | **Payment Module** | |
| | Create `PaymentGateway` port | Interface |
| | Create `BkashGateway` adapter | bKash integration |
| | Create `ShurjoPayGateway` adapter | ShurjoPay integration |
| | Create `CapturePayment` command | Idempotent payment capture |
| | Create `RefundPayment` command | With reconciliation |
| | Payment state machine | INITIATED → PENDING → CAPTURED → REFUNDED |
| | Webhook handlers | Signature verification + idempotent processing |

---

### PHASE 8: Shipping + External Integrations
> **Goal**: Abstract all external services behind ports/adapters  
> **Risk**: Medium | **Duration**: 2-3 sessions

| # | Task | Details |
|---|---|---|
| 8.1 | **Shipping Module** | |
| | Create `ShippingPort` | Interface |
| | Create `PathaoAdapter` | Pathao API |
| | Create `SteadfastAdapter` | Steadfast API |
| | Create `RedXAdapter` | RedX API |
| | Create `CourierManager` | Multi-courier abstraction |
| | Fraud check service | Hoorin.com API adapter |
| 8.2 | **Campaign Module** | Campaign domain + image processing |
| 8.3 | **Review Module** | Review + moderation |
| 8.4 | **Content Module** | Banners, pages, social links |
| 8.5 | **Settings Module** | App settings, feature flags |

---

### PHASE 9: Frontend / API Modernization
> **Goal**: Clean views, API resources, component-based UI  
> **Risk**: Low | **Duration**: 2-3 sessions

| # | Task | Details |
|---|---|---|
| 9.1 | Create Blade components | `x-product-card`, `x-cart-item`, `x-navbar` |
| 9.2 | Implement ViewModels | Replace `compact()` with typed ViewModels |
| 9.3 | API Resources | `OrderResource`, `ProductResource` |
| 9.4 | API versioning | `/api/v1/` with stable contracts |
| 9.5 | Extract AJAX endpoints | Dedicated API routes |
| 9.6 | Optimize assets | Vite config, code splitting |

---

### PHASE 10: Performance / Scale
> **Goal**: Redis caching, MySQL optimization, load testing  
> **Risk**: Low | **Duration**: 1-2 sessions

| # | Task | Details |
|---|---|---|
| 10.1 | Redis cache: categories | `category:tree:v{version}` |
| 10.2 | Redis cache: products | `catalog:product:{id}:v{version}` |
| 10.3 | Redis cache: settings | `settings:shop:v{version}` |
| 10.4 | MySQL index optimization | Query-pattern driven composite indexes |
| 10.5 | Query optimization | Eliminate N+1, `select()` everywhere |
| 10.6 | Queue isolation | critical/normal/slow workers |
| 10.7 | Load testing | Checkout, search, stock contention |

---

### PHASE 11: Hardening / Architecture Enforcement
> **Goal**: Prevent architectural regression  
> **Risk**: Low | **Duration**: 1-2 sessions

| # | Task | Details |
|---|---|---|
| 11.1 | Architecture fitness tests | PHPStan custom rules |
| 11.2 | CI quality gates | Static analysis, tests, security scan |
| 11.3 | Observability | Structured JSON logs, metrics, tracing |
| 11.4 | DR planning | RPO/RTO, backup strategy, restore testing |
| 11.5 | Documentation | Architecture decision records |

---

## ৮. Execution Order

```
Phase 0  (Discovery)           ← Document current state
    ↓
Phase 1  (Security P0)         ← Fix critical security on Laravel 9
    ↓
Phase 2  (Reliability P1)      ← Redis, transactions, outbox, queues
    ↓
Phase 3  (Laravel 13)          ← Framework upgrade (one major at a time)
    ↓
Phase 4  (Domain Seams)        ← Module skeleton, contracts, enums
    ↓
Phase 5  (Catalog + Customer)  ← First vertical slices
    ↓
Phase 6  (Cart + Pricing + Inventory) ← Authoritative pricing & stock
    ↓
Phase 7  (Order + Payment)     ← Highest risk extraction
    ↓
Phase 8  (Shipping + Others)   ← External integrations
    ↓
Phase 9  (Frontend / API)      ← UI modernization
    ↓
Phase 10 (Performance)         ← Redis cache, MySQL optimization
    ↓
Phase 11 (Hardening)           ← Architecture enforcement, CI, DR
```

**Each phase is independently deployable — the app works after every phase.**

---

## ৯. Package Strategy

### Remove
| Package | Reason | Replacement |
|---|---|---|
| `laravelcollective/html` | Abandoned | Blade components + raw HTML |
| `olimortimer/laravelshoppingcart` | Stale, session-based | Custom Redis-backed Cart |
| `brian2694/laravel-toastr` | Unnecessary | Flash messages + Alpine.js |

### Add
| Package | Purpose |
|---|---|
| `spatie/laravel-data` | DTOs with type safety |
| `predis/predis` | Redis client |
| `larastan/larastan` | Static analysis |
| `pestphp/pest` | Modern testing |

### Update
| Package | From | To |
|---|---|---|
| `laravel/framework` | ^9.19 | ^13.0 |
| `spatie/laravel-permission` | ^5.7 | ^6.x |
| `yajra/laravel-datatables-oracle` | ~10.0 | ~12.0 |
| `intervention/image` | ^2.7 | ^3.x |
| `laravel/sanctum` | ^3.0 | ^4.x |

### Optional
| Package | Purpose | Note |
|---|---|---|
| `nwidart/laravel-modules` | Module management | Use only if module versioning/reuse needed; native `app/Modules` may be simpler |

---

## ১০. Database Strategy

### Indexes (Query-Pattern Driven)
```sql
-- Orders
CREATE INDEX idx_orders_customer_date ON orders(customer_id, created_at);
CREATE INDEX idx_orders_status_date ON orders(order_status, created_at);
CREATE INDEX idx_orders_payment_status ON orders(payment_status, created_at);

-- Products
CREATE INDEX idx_products_status_published ON products(status, created_at);
CREATE INDEX idx_products_category_status ON products(category_id, status);
CREATE UNIQUE INDEX idx_products_slug ON products(slug);

-- Order Items
CREATE INDEX idx_order_items_order ON order_details(order_id);
CREATE INDEX idx_order_items_product ON order_details(product_id);
```

### Migration Strategy (Zero-Downtime)
```
Release N:   Add new nullable column/table
Release N+1: Write new + old, backfill
Release N+2: Read new only
Release N+3: Remove old field
```

### New Tables
```sql
-- Transactional Outbox
outbox_messages (id, aggregate_type, aggregate_id, event_type, event_version, payload_json, occurred_at, published_at, attempts, last_error)

-- Webhook Events
webhook_events (id, provider, external_event_id UNIQUE, signature_verified_at, received_at, processed_at, payload_hash, status)

-- Inventory Ledger
inventory_ledger (id, product_id, movement_type, quantity, reference_type, reference_id, before_qty, after_qty, created_by, created_at)

-- Audit Log
audit_logs (id, actor_type, actor_id, action, subject_type, subject_id, before_json, after_json, request_id, ip_address, created_at)
```

---

---

## ১১. DB Schema & Model Relationship Optimization

> **Source**: `DB-SCHEMA-OPTIMIZATION-PLAN.md` (merged into master plan)  
> **Execution**: Integrated into each phase above, backward-compatible migrations

# MondolShopBD — DB Schema & Model Relationship Optimization Plan

> Refactoring এর সময় প্রতিটি table এবং model relation optimize করার plan।  
> প্রতিটি phase এ backward-compatible migration হবে।

---

## ১. বর্তমান সমস্যা (Current Problems)

### 🔴 Critical — Foreign Key Constraints নেই
**কোনো table এ FK constraint নেই।** সবখানে `integer` column দিয়ে reference করা হয়েছে কিন্তু `foreignId()` বা `->constrained()` ব্যবহার হয়নি।

| Table | Column | References | FK? |
|---|---|---|---|
| `products` | `category_id` (integer) | `categories.id` | ❌ |
| `products` | `brand_id` (integer) | `brands.id` | ❌ |
| `productimages` | `product_id` (integer) | `products.id` | ❌ |
| `productsizes` | `product_id` (integer) | `products.id` | ❌ |
| `productsizes` | `size_id` (integer) | `sizes.id` | ❌ |
| `productcolors` | `product_id` (integer) | `products.id` | ❌ |
| `productcolors` | `color_id` (integer) | `colors.id` | ❌ |
| `orders` | `customer_id` (integer) | `customers.id` | ❌ |
| `order_details` | `order_id` (integer) | `orders.id` | ❌ |
| `order_details` | `product_id` (integer) | `products.id` | ❌ |
| `shippings` | `order_id` (integer) | `orders.id` | ❌ |
| `shippings` | `customer_id` (integer) | `customers.id` | ❌ |
| `payments` | `order_id` (integer) | `orders.id` | ❌ |
| `payments` | `customer_id` (integer) | `customers.id` | ❌ |
| `reviews` | `product_id` (integer) | `products.id` | ❌ |
| `subcategories` | `category_id` (integer) | `categories.id` | ❌ |
| `childcategories` | `subcategory_id` (integer) | `subcategories.id` | ❌ |
| `banners` | `category_id` (integer) | `banner_categories.id` | ❌ |
| `campaign_reviews` | `campaign_id` (integer) | `campaigns.id` | ❌ |

### 🔴 Critical — Column Type ভুল
| Table | Column | Current | Should Be |
|---|---|---|---|
| `products` | `category_id` | `integer` | `foreignId` (unsignedBigInteger) |
| `products` | `brand_id` | `integer` | `foreignId` |
| `products` | `purchase_price` | `integer` | `decimal(12,2)` |
| `products` | `old_price` | `integer` | `decimal(12,2)` |
| `products` | `new_price` | `integer` | `decimal(12,2)` |
| `products` | `stock` | `integer` | `unsignedInteger` |
| `products` | `campaign_id` | `tinyInteger` | `foreignId` |
| `orders` | `amount` | `integer` | `decimal(12,2)` |
| `orders` | `discount` | `integer` | `decimal(12,2)` |
| `orders` | `shipping_charge` | `integer` | `decimal(12,2)` |
| `orders` | `order_status` | `string(55)` | `foreignId` → `order_statuses.id` |
| `order_details` | `purchase_price` | `integer` | `decimal(12,2)` |
| `order_details` | `sale_price` | `integer` | `decimal(12,2)` |
| `payments` | `amount` | `integer` | `decimal(12,2)` |
| `customers` | `balance` | `float` | `decimal(12,2)` |
| `districts` | `shippingfee` | `string` | `decimal(12,2)` |
| `districts` | `partialpayment` | `string` | `decimal(12,2)` |
| `shipping_charges` | `amount` | `integer` | `decimal(12,2)` |
| `reviews` | `ratting` | `string(4)` | `tinyInteger` (1-5) |
| `reviews` | `status` | `string(55)` | `tinyInteger` (0/1) |
| `colors` | `status` | `string` | `tinyInteger` |
| `sizes` | `status` | `string` | `tinyInteger` |
| `shipping_charges` | `status` | `string` | `tinyInteger` |
| `order_statuses` | `status` | `string(55)` | `tinyInteger` |
| `customers` | `status` | `string(55)` | `tinyInteger` |
| `customers` | `verify` | `integer` | `tinyInteger` (0/1) |

### 🟠 Major — Wrong Relationship Types
| Model | Method | Current | Should Be |
|---|---|---|---|
| `Product::category()` | `hasOne` | ❌ Wrong | `belongsTo` |
| `Product::subcategory()` | `hasOne` | ❌ Wrong | `belongsTo` |
| `Product::childcategory()` | `hasOne` | ❌ Wrong | `belongsTo` |
| `Product::brand()` | `hasOne` | ❌ Wrong | `belongsTo` |
| `Order::status()` | `belongsTo` | ⚠️ Wrong key | `belongsTo(OrderStatus::class, 'order_status', 'id')` |
| `Order::shipping()` | `belongsTo` | ❌ Wrong | `hasOne` (reverse key) |
| `Order::payment()` | `belongsTo` | ❌ Wrong | `hasOne` (reverse key) |
| `Order::product()` | `belongsTo` | ❌ Wrong | Remove (use `orderdetails`) |
| `Banner::category()` | `hasOne` | ❌ Wrong | `belongsTo` |
| `Childcategory::subcategory()` | `hasOne` | ❌ Wrong | `belongsTo` |

### 🟠 Major — Missing Indexes
| Table | Missing Index | Reason |
|---|---|---|
| `products` | `(category_id, status)` | Category page filtering |
| `products` | `(status, created_at)` | Admin listing |
| `products` | `slug` UNIQUE | Product detail lookup |
| `products` | `(status, topsale)` | Hot deals query |
| `orders` | `(customer_id, created_at)` | Customer order history |
| `orders` | `(order_status, created_at)` | Status filtering |
| `order_details` | `(order_id)` | Order detail join |
| `order_details` | `(product_id)` | Product sales query |
| `shippings` | `(order_id)` | Already added |
| `payments` | `(order_id)` | Payment lookup |
| `payments` | `(customer_id)` | Customer payment history |
| `reviews` | `(product_id, status)` | Product review query |
| `productimages` | `(product_id)` | Product image join |
| `productsizes` | `(product_id)` | Product size join |
| `productcolors` | `(product_id)` | Product color join |
| `categories` | `slug` UNIQUE | Category detail lookup |
| `subcategories` | `(category_id, status)` | Category → Sub join |
| `childcategories` | `(subcategory_id, status)` | Sub → Child join |
| `customers` | `phone` UNIQUE | Login lookup |
| `customers` | `email` UNIQUE | Login lookup |

### 🟡 Minor — Naming Issues
| Current | Should Be | Reason |
|---|---|---|
| `productimages` | `product_images` | Laravel convention (snake_case) |
| `productsizes` | `product_sizes` | Laravel convention |
| `productcolors` | `product_colors` | Laravel convention |
| `childcategories` | `child_categories` | Laravel convention |
| `subcategories` | `sub_categories` | Laravel convention |
| `courierapis` | `courier_apis` | Laravel convention |
| `meta_decription` | `meta_description` | Typo in categories/subcategories |
| `ratting` | `rating` | Typo in reviews |
| `serderid` | `sender_id` | Typo in sms_gateways |

### 🟡 Minor — Missing Columns
| Table | Missing Column | Purpose |
|---|---|---|
| `products` | `subcategory_id` | Direct FK (currently nullable) |
| `products` | `childcategory_id` | Direct FK (currently nullable) |
| `orders` | `user_id` | Admin who created POS order |
| `orders` | `payment_status` | Separate from order_status |
| `orders` | `notes` | Order notes |
| `shippings` | `district` | District name |
| `shippings` | `shipping_charge_id` | FK to shipping_charges |
| `customers` | `otp` | OTP code |
| `customers` | `otp_expires_at` | OTP expiry |

---

## ২. Optimized ERD (Target Schema)

```
┌─────────────────┐     ┌──────────────────┐     ┌─────────────────┐
│   categories    │     │  sub_categories  │     │ child_categories│
├─────────────────┤     ├──────────────────┤     ├─────────────────┤
│ id (PK)         │◄────│ category_id (FK) │◄────│ subcategory_id  │
│ name            │     │ id (PK)          │     │ (FK)            │
│ slug (UNIQUE)   │     │ subcategoryName  │     │ id (PK)         │
│ parent_id (self)│     │ slug (UNIQUE)    │     │ childcategoryName│
│ image           │     │ image            │     │ slug (UNIQUE)   │
│ meta_title      │     │ meta_title       │     │ meta_title      │
│ meta_description│     │ meta_description │     │ meta_description│
│ front_view      │     │ status (TINYINT) │     │ status (TINYINT)│
│ status (TINYINT)│     │ timestamps       │     │ timestamps      │
│ timestamps      │     └──────────────────┘     └─────────────────┘
└─────────────────┘
        │
        │ category_id (FK)
        ▼
┌─────────────────┐     ┌──────────────────┐     ┌─────────────────┐
│    products     │     │  product_images  │     │product_colors   │
├─────────────────┤     ├──────────────────┤     ├─────────────────┤
│ id (PK)         │◄────│ product_id (FK)  │     │ product_id (FK) │
│ name            │     │ id (PK)          │     │ color_id (FK)   │
│ slug (UNIQUE)   │     │ image            │     │ id (PK)         │
│ product_code(U) │     │ sort_order       │     │ timestamps      │
│ category_id(FK) │     │ is_primary       │     └─────────────────┘
│ subcategory_id  │     │ timestamps       │            │
│ (FK, nullable)  │     └──────────────────┘            │
│ childcategory_id│                                     ▼
│ (FK, nullable)  │     ┌──────────────────┐     ┌─────────────────┐
│ brand_id (FK)   │     │product_sizes     │     │     colors      │
│ purchase_price  │     ├──────────────────┤     ├─────────────────┤
│ (DECIMAL 12,2)  │     │ product_id (FK)  │     │ id (PK)         │
│ old_price       │     │ size_id (FK)     │     │ colorName       │
│ (DECIMAL 12,2)  │     │ id (PK)          │     │ color (hex)     │
│ new_price       │     │ timestamps       │     │ status (TINYINT)│
│ (DECIMAL 12,2)  │     └──────────────────┘     │ timestamps      │
│ stock (UINT)    │            │                  └─────────────────┘
│ description     │            ▼
│ meta_description│     ┌──────────────────┐     ┌─────────────────┐
│ topsale (0/1)   │     │      sizes       │     │     brands      │
│ feature (0/1)   │     ├──────────────────┤     ├─────────────────┤
│ status (TINYINT)│     │ id (PK)          │     │ id (PK)         │
│ timestamps      │     │ sizeName         │     │ name            │
└─────────────────┘     │ status (TINYINT) │     │ slug (UNIQUE)   │
                        │ timestamps       │     │ image           │
                        └──────────────────┘     │ status (TINYINT)│
                                                 │ timestamps      │
                                                 └─────────────────┘

┌─────────────────┐     ┌──────────────────┐     ┌─────────────────┐
│    customers    │     │     orders       │     │  order_details  │
├─────────────────┤     ├──────────────────┤     ├─────────────────┤
│ id (PK)         │◄────│ customer_id (FK) │◄────│ order_id (FK)   │
│ name            │     │ id (PK)          │     │ id (PK)         │
│ phone (UNIQUE)  │     │ invoice_id (IDX) │     │ product_id (FK) │
│ email (UNIQUE)  │     │ amount (DECIMAL) │     │ product_name    │
│ password        │     │ discount (DEC)   │     │ purchase_price  │
│ balance (DEC)   │     │ shipping_charge  │     │ (DECIMAL)       │
│ district (FK)   │     │ (DECIMAL)        │     │ sale_price      │
│ area (FK)       │     │ customer_id (FK) │     │ (DECIMAL)       │
│ address         │     │ user_id (FK,null)│     │ qty (UINT)      │
│ verify (0/1)    │     │ order_status(FK) │     │ timestamps      │
│ otp             │     │ payment_status   │     └─────────────────┘
│ otp_expires_at  │     │ notes (TEXT)     │
│ image           │     │ timestamps       │
│ status (TINYINT)│     └──────────────────┘
│ timestamps      │            │
└─────────────────┘            │
        │                      ├─── order_status (FK) ──► order_statuses
        │                      │
        │                      ▼
        │               ┌──────────────────┐     ┌─────────────────┐
        │               │    shippings     │     │    payments     │
        │               ├──────────────────┤     ├─────────────────┤
        │               │ id (PK)          │     │ id (PK)         │
        │               │ order_id (FK, UQ)│     │ order_id (FK)   │
        │               │ customer_id (FK) │     │ customer_id (FK)│
        │               │ name             │     │ amount (DECIMAL)│
        │               │ phone            │     │ payment_method  │
        │               │ address          │     │ trx_id          │
        │               │ area             │     │ sender_number   │
        │               │ district         │     │ payment_status  │
        │               │ shipping_charge_id│    │ gateway_response│
        │               │ (FK, nullable)   │     │ timestamps      │
        │               │ timestamps       │     └─────────────────┘
        │               └──────────────────┘
        │
        ▼
┌─────────────────┐     ┌──────────────────┐     ┌─────────────────┐
│    districts    │     │ shipping_charges │     │ order_statuses  │
├─────────────────┤     ├──────────────────┤     ├─────────────────┤
│ id (PK)         │     │ id (PK)          │     │ id (PK)         │
│ area_id         │     │ name             │     │ name            │
│ area_name       │     │ amount (DECIMAL) │     │ slug            │
│ district        │     │ status (TINYINT) │     │ status (TINYINT)│
│ shippingfee(DEC)│     │ timestamps       │     │ timestamps      │
│ partialpay (DEC)│     └──────────────────┘     └─────────────────┘
│ timestamps      │
└─────────────────┘

┌─────────────────┐     ┌──────────────────┐     ┌─────────────────┐
│   campaigns     │     │ campaign_reviews │     │    reviews      │
├─────────────────┤     ├──────────────────┤     ├─────────────────┤
│ id (PK)         │◄────│ campaign_id (FK) │     │ id (PK)         │
│ name            │     │ id (PK)          │     │ name            │
│ slug (UNIQUE)   │     │ image            │     │ email           │
│ date            │     │ timestamps       │     │ rating (TINYINT)│
│ short_desc      │     └──────────────────┘     │ review (TEXT)   │
│ review          │                              │ product_id (FK) │
│ description     │                              │ customer_id(FK) │
│ image_one       │                              │ status (TINYINT)│
│ image_two       │                              │ timestamps      │
│ image_three     │                              └─────────────────┘
│ product_id (FK) │
│ status (TINYINT)│     ┌──────────────────┐     ┌─────────────────┐
│ timestamps      │     │     banners      │     │banner_categories│
└─────────────────┘     ├──────────────────┤     ├─────────────────┤
                        │ id (PK)          │     │ id (PK)         │
                        │ category_id (FK) │────►│ name            │
                        │ link             │     │ status (TINYINT)│
                        │ image            │     │ timestamps      │
                        │ sort_order       │     └─────────────────┘
                        │ status (TINYINT) │
                        │ timestamps       │
                        └──────────────────┘

┌──────────────────────────────────────────────────────────────────┐
│                    NEW TABLES (Phase 2+)                          │
├──────────────────────────────────────────────────────────────────┤
│                                                                  │
│  inventory_ledger          outbox_messages         webhook_events │
│  ├─ id (PK)               ├─ id (PK)              ├─ id (PK)    │
│  ├─ product_id (FK)       ├─ aggregate_type       ├─ provider   │
│  ├─ movement_type (ENUM)  ├─ aggregate_id         ├─ ext_event_id│
│  ├─ quantity (INT)        ├─ event_type           │  (UNIQUE)   │
│  ├─ reference_type        ├─ event_version        ├─ payload    │
│  ├─ reference_id          ├─ payload_json         ├─ status     │
│  ├─ before_qty            ├─ occurred_at          ├─ received_at│
│  ├─ after_qty             ├─ published_at         ├─ processed  │
│  ├─ created_by            ├─ attempts             ├─ timestamps │
│  └─ timestamps            ├─ last_error           └─────────────┘
│                           └─ timestamps                         │
│                                                                  │
│  audit_logs                idempotency_keys                      │
│  ├─ id (PK)               ├─ id (PK)                            │
│  ├─ actor_type            ├─ key (UNIQUE)                       │
│  ├─ actor_id              ├─ action_type                        │
│  ├─ action                ├─ entity_type                        │
│  ├─ subject_type          ├─ entity_id                          │
│  ├─ subject_id            ├─ response_json                      │
│  ├─ before_json           ├─ created_at                         │
│  ├─ after_json            └─────────────────────                │
│  ├─ request_id                                                │
│  ├─ ip_address                                                │
│  └─ timestamps                                                │
└──────────────────────────────────────────────────────────────────┘
```

---

## ৩. Migration Strategy (Incremental, Backward-Compatible)

### Phase 1: Column Type Fixes (No FK yet)
> Safe — only changes column types, no data loss

```php
// Migration: fix_column_types_products
Schema::table('products', function (Blueprint $table) {
    $table->decimal('purchase_price', 12, 2)->change();
    $table->decimal('old_price', 12, 2)->nullable()->change();
    $table->decimal('new_price', 12, 2)->change();
    $table->unsignedInteger('stock')->change();
    $table->unsignedBigInteger('category_id')->change();
    $table->unsignedBigInteger('brand_id')->nullable()->change();
    $table->unsignedBigInteger('campaign_id')->nullable()->change();
});

// Migration: fix_column_types_orders
Schema::table('orders', function (Blueprint $table) {
    $table->decimal('amount', 12, 2)->change();
    $table->decimal('discount', 12, 2)->default(0)->change();
    $table->decimal('shipping_charge', 12, 2)->default(0)->change();
    $table->unsignedBigInteger('customer_id')->change();
    $table->unsignedBigInteger('user_id')->nullable()->change();
});

// Migration: fix_column_types_order_details
Schema::table('order_details', function (Blueprint $table) {
    $table->decimal('purchase_price', 12, 2)->change();
    $table->decimal('sale_price', 12, 2)->change();
    $table->unsignedInteger('qty')->change();
    $table->unsignedBigInteger('order_id')->change();
    $table->unsignedBigInteger('product_id')->change();
});

// Migration: fix_column_types_payments
Schema::table('payments', function (Blueprint $table) {
    $table->decimal('amount', 12, 2)->change();
    $table->unsignedBigInteger('order_id')->change();
    $table->unsignedBigInteger('customer_id')->change();
});

// Migration: fix_column_types_customers
Schema::table('customers', function (Blueprint $table) {
    $table->decimal('balance', 12, 2)->default(0)->change();
    $table->tinyInteger('status')->default(1)->change();
    $table->tinyInteger('verify')->default(0)->change();
});

// Migration: fix_column_types_others
Schema::table('reviews', function (Blueprint $table) {
    $table->tinyInteger('rating')->change();  // rename ratting → rating
    $table->tinyInteger('status')->default(1)->change();
    $table->unsignedBigInteger('product_id')->change();
});

Schema::table('districts', function (Blueprint $table) {
    $table->decimal('shippingfee', 12, 2)->change();
    $table->decimal('partialpayment', 12, 2)->change();
});

Schema::table('shipping_charges', function (Blueprint $table) {
    $table->decimal('amount', 12, 2)->change();
    $table->tinyInteger('status')->default(1)->change();
});
```

### Phase 2: Add Indexes
> Safe — only adds indexes, no data change

```php
// Migration: add_optimized_indexes
Schema::table('products', function (Blueprint $table) {
    $table->index(['category_id', 'status'], 'idx_products_category_status');
    $table->index(['status', 'created_at'], 'idx_products_status_created');
    $table->index(['status', 'topsale'], 'idx_products_status_topsale');
    $table->unique('slug', 'idx_products_slug_unique');
});

Schema::table('orders', function (Blueprint $table) {
    $table->index(['customer_id', 'created_at'], 'idx_orders_customer_date');
    $table->index(['order_status', 'created_at'], 'idx_orders_status_date');
});

Schema::table('order_details', function (Blueprint $table) {
    $table->index('order_id', 'idx_order_details_order');
    $table->index('product_id', 'idx_order_details_product');
});

Schema::table('payments', function (Blueprint $table) {
    $table->index('order_id', 'idx_payments_order');
    $table->index('customer_id', 'idx_payments_customer');
});

Schema::table('reviews', function (Blueprint $table) {
    $table->index(['product_id', 'status'], 'idx_reviews_product_status');
});

Schema::table('productimages', function (Blueprint $table) {
    $table->index('product_id', 'idx_productimages_product');
});

Schema::table('productsizes', function (Blueprint $table) {
    $table->index('product_id', 'idx_productsizes_product');
});

Schema::table('productcolors', function (Blueprint $table) {
    $table->index('product_id', 'idx_productcolors_product');
});

Schema::table('categories', function (Blueprint $table) {
    $table->unique('slug', 'idx_categories_slug_unique');
});

Schema::table('subcategories', function (Blueprint $table) {
    $table->index(['category_id', 'status'], 'idx_subcategories_category_status');
    $table->unique('slug', 'idx_subcategories_slug_unique');
});

Schema::table('childcategories', function (Blueprint $table) {
    $table->index(['subcategory_id', 'status'], 'idx_childcategories_sub_status');
    $table->unique('slug', 'idx_childcategories_slug_unique');
});

Schema::table('customers', function (Blueprint $table) {
    $table->unique('phone', 'idx_customers_phone_unique');
    $table->unique('email', 'idx_customers_email_unique');
});

Schema::table('campaigns', function (Blueprint $table) {
    $table->unique('slug', 'idx_campaigns_slug_unique');
});
```

### Phase 3: Add Foreign Keys
> Medium risk — requires data cleanup first

```php
// Migration: add_foreign_keys_products
// BEFORE: Clean orphan records
DB::statement('DELETE FROM products WHERE category_id NOT IN (SELECT id FROM categories)');
DB::statement('DELETE FROM productimages WHERE product_id NOT IN (SELECT id FROM products)');

Schema::table('products', function (Blueprint $table) {
    $table->foreign('category_id')->references('id')->on('categories')->onDelete('cascade');
    $table->foreign('brand_id')->references('id')->on('brands')->onDelete('set null');
});

Schema::table('productimages', function (Blueprint $table) {
    $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
});

Schema::table('productsizes', function (Blueprint $table) {
    $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
    $table->foreign('size_id')->references('id')->on('sizes')->onDelete('cascade');
});

Schema::table('productcolors', function (Blueprint $table) {
    $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
    $table->foreign('color_id')->references('id')->on('colors')->onDelete('cascade');
});

Schema::table('orders', function (Blueprint $table) {
    $table->foreign('customer_id')->references('id')->on('customers')->onDelete('cascade');
    $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
});

Schema::table('order_details', function (Blueprint $table) {
    $table->foreign('order_id')->references('id')->on('orders')->onDelete('cascade');
    $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
});

Schema::table('shippings', function (Blueprint $table) {
    $table->foreign('order_id')->references('id')->on('orders')->onDelete('cascade');
    $table->foreign('customer_id')->references('id')->on('customers')->onDelete('cascade');
});

Schema::table('payments', function (Blueprint $table) {
    $table->foreign('order_id')->references('id')->on('orders')->onDelete('cascade');
    $table->foreign('customer_id')->references('id')->on('customers')->onDelete('cascade');
});

Schema::table('reviews', function (Blueprint $table) {
    $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
});

Schema::table('subcategories', function (Blueprint $table) {
    $table->foreign('category_id')->references('id')->on('categories')->onDelete('cascade');
});

Schema::table('childcategories', function (Blueprint $table) {
    $table->foreign('subcategory_id')->references('id')->on('subcategories')->onDelete('cascade');
});

Schema::table('banners', function (Blueprint $table) {
    $table->foreign('category_id')->references('id')->on('banner_categories')->onDelete('cascade');
});

Schema::table('campaign_reviews', function (Blueprint $table) {
    $table->foreign('campaign_id')->references('id')->on('campaigns')->onDelete('cascade');
});
```

### Phase 4: Add Missing Columns + New Tables

```php
// Migration: add_missing_columns_products
Schema::table('products', function (Blueprint $table) {
    $table->unsignedBigInteger('subcategory_id')->nullable()->after('category_id');
    $table->unsignedBigInteger('childcategory_id')->nullable()->after('subcategory_id');
    $table->foreign('subcategory_id')->references('id')->on('subcategories')->onDelete('set null');
    $table->foreign('childcategory_id')->references('id')->on('childcategories')->onDelete('set null');
});

// Migration: add_missing_columns_orders
Schema::table('orders', function (Blueprint $table) {
    $table->string('payment_status', 55)->default('pending')->after('order_status');
    $table->text('notes')->nullable()->after('payment_status');
});

// Migration: add_missing_columns_shippings
Schema::table('shippings', function (Blueprint $table) {
    $table->string('district')->nullable()->after('area');
    $table->unsignedBigInteger('shipping_charge_id')->nullable()->after('district');
});

// Migration: add_missing_columns_customers
Schema::table('customers', function (Blueprint $table) {
    $table->string('otp', 10)->nullable()->after('verify');
    $table->timestamp('otp_expires_at')->nullable()->after('otp');
});

// Migration: add_missing_columns_reviews
Schema::table('reviews', function (Blueprint $table) {
    $table->unsignedBigInteger('customer_id')->nullable()->after('product_id');
    $table->foreign('customer_id')->references('id')->on('customers')->onDelete('set null');
});
```

### Phase 5: New Architecture Tables

```php
// Migration: create_inventory_ledger_table
Schema::create('inventory_ledger', function (Blueprint $table) {
    $table->id();
    $table->unsignedBigInteger('product_id');
    $table->enum('movement_type', [
        'PURCHASE', 'OPENING_BALANCE', 'SALE', 'SALE_RETURN',
        'RESERVATION', 'RELEASE', 'ADJUSTMENT', 'DAMAGE',
        'TRANSFER_IN', 'TRANSFER_OUT'
    ]);
    $table->integer('quantity');
    $table->string('reference_type')->nullable(); // Order, Return, etc.
    $table->unsignedBigInteger('reference_id')->nullable();
    $table->integer('before_qty');
    $table->integer('after_qty');
    $table->unsignedBigInteger('created_by')->nullable();
    $table->timestamps();

    $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
    $table->index(['product_id', 'created_at']);
    $table->index(['reference_type', 'reference_id']);
});

// Migration: create_outbox_messages_table
Schema::create('outbox_messages', function (Blueprint $table) {
    $table->id();
    $table->string('aggregate_type');
    $table->unsignedBigInteger('aggregate_id');
    $table->string('event_type');
    $table->integer('event_version')->default(1);
    $table->json('payload_json');
    $table->timestamp('occurred_at');
    $table->timestamp('published_at')->nullable();
    $table->integer('attempts')->default(0);
    $table->text('last_error')->nullable();
    $table->timestamps();

    $table->index(['aggregate_type', 'aggregate_id']);
    $table->index('published_at');
});

// Migration: create_webhook_events_table
Schema::create('webhook_events', function (Blueprint $table) {
    $table->id();
    $table->string('provider'); // shurjopay, bkash, pathao
    $table->string('external_event_id')->unique();
    $table->timestamp('signature_verified_at')->nullable();
    $table->timestamp('received_at');
    $table->timestamp('processed_at')->nullable();
    $table->string('payload_hash');
    $table->string('status')->default('received'); // received, processing, processed, failed
    $table->json('payload')->nullable();
    $table->text('error')->nullable();
    $table->timestamps();

    $table->index(['provider', 'status']);
});

// Migration: create_audit_logs_table
Schema::create('audit_logs', function (Blueprint $table) {
    $table->id();
    $table->string('actor_type'); // User, Customer, System
    $table->unsignedBigInteger('actor_id')->nullable();
    $table->string('action'); // created, updated, deleted, status_changed
    $table->string('subject_type'); // Order, Product, Payment
    $table->unsignedBigInteger('subject_id');
    $table->json('before_json')->nullable();
    $table->json('after_json')->nullable();
    $table->string('request_id')->nullable();
    $table->string('ip_address', 45)->nullable();
    $table->string('user_agent')->nullable();
    $table->timestamps();

    $table->index(['subject_type', 'subject_id']);
    $table->index(['actor_type', 'actor_id']);
    $table->index('created_at');
});

// Migration: create_idempotency_keys_table
Schema::create('idempotency_keys', function (Blueprint $table) {
    $table->id();
    $table->string('key')->unique();
    $table->string('action_type');
    $table->string('entity_type')->nullable();
    $table->unsignedBigInteger('entity_id')->nullable();
    $table->json('response_json')->nullable();
    $table->timestamp('created_at');

    $table->index(['action_type', 'entity_type', 'entity_id']);
});
```

---

## ৪. Model Relationship Fixes

### Product Model (FIXED)
```php
class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'slug', 'product_code', 'category_id', 'subcategory_id',
        'childcategory_id', 'brand_id', 'purchase_price', 'old_price',
        'new_price', 'stock', 'description', 'meta_description',
        'topsale', 'feature_product', 'campaign_id', 'status',
    ];

    protected $casts = [
        'purchase_price' => 'decimal:2',
        'old_price' => 'decimal:2',
        'new_price' => 'decimal:2',
        'stock' => 'integer',
        'topsale' => 'boolean',
        'feature_product' => 'boolean',
        'status' => 'boolean',
    ];

    // ✅ belongsTo (NOT hasOne) — Product OWNS the FK
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function subcategory(): BelongsTo
    {
        return $this->belongsTo(Subcategory::class);
    }

    public function childcategory(): BelongsTo
    {
        return $this->belongsTo(Childcategory::class);
    }

    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(Campaign::class);
    }

    // ✅ hasMany — Product is the parent
    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class);
    }

    public function image(): HasOne
    {
        return $this->hasOne(ProductImage::class)->where('is_primary', true);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    // ✅ belongsToMany — Pivot tables
    public function sizes(): BelongsToMany
    {
        return $this->belongsToMany(Size::class, 'product_sizes')->withTimestamps();
    }

    public function colors(): BelongsToMany
    {
        return $this->belongsToMany(Color::class, 'product_colors')->withTimestamps();
    }

    // ✅ Scopes
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', true);
    }

    public function scopeHotDeals(Builder $query): Builder
    {
        return $query->where('topsale', true);
    }

    public function scopeFeatured(Builder $query): Builder
    {
        return $query->where('feature_product', true);
    }
}
```

### Order Model (FIXED)
```php
class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_id', 'amount', 'discount', 'shipping_charge',
        'customer_id', 'user_id', 'order_status', 'payment_status', 'notes',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'discount' => 'decimal:2',
        'shipping_charge' => 'decimal:2',
    ];

    // ✅ belongsTo — Order OWNS customer_id
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function status(): BelongsTo
    {
        return $this->belongsTo(OrderStatus::class, 'order_status');
    }

    // ✅ hasOne — Reverse side (Shipping/Payment have order_id)
    public function shipping(): HasOne
    {
        return $this->hasOne(Shipping::class);
    }

    public function payment(): HasOne
    {
        return $this->hasOne(Payment::class);
    }

    // ✅ hasMany — Order has many details
    public function orderDetails(): HasMany
    {
        return $this->hasMany(OrderDetails::class);
    }

    // ❌ REMOVE: product() — wrong relationship, use orderDetails
}
```

### Category Model (FIXED)
```php
class Category extends Model
{
    protected $fillable = [
        'name', 'slug', 'parent_id', 'image', 'meta_title',
        'meta_description', 'front_view', 'status',
    ];

    protected $casts = [
        'status' => 'boolean',
        'front_view' => 'boolean',
    ];

    // Self-referencing
    public function parent(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(Category::class, 'parent_id');
    }

    // Has many
    public function subcategories(): HasMany
    {
        return $this->hasMany(Subcategory::class);
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }
}
```

---

## ৫. Execution Checklist

```
Phase 1: Column Type Fixes
[ ] products — price columns → decimal(12,2)
[ ] orders — amount/discount/shipping → decimal(12,2)
[ ] order_details — prices → decimal(12,2)
[ ] payments — amount → decimal(12,2)
[ ] customers — balance → decimal, status → tinyint
[ ] reviews — ratting → rating (tinyint)
[ ] districts — fees → decimal
[ ] shipping_charges — amount → decimal
[ ] All status columns → tinyint

Phase 2: Add Indexes
[ ] products — category_status, status_created, status_topsale, slug
[ ] orders — customer_date, status_date
[ ] order_details — order_id, product_id
[ ] payments — order_id, customer_id
[ ] reviews — product_status
[ ] productimages — product_id
[ ] productsizes — product_id
[ ] productcolors — product_id
[ ] categories — slug
[ ] subcategories — category_status, slug
[ ] childcategories — sub_status, slug
[ ] customers — phone, email
[ ] campaigns — slug

Phase 3: Add Foreign Keys
[ ] Clean orphan records first
[ ] products → categories, brands
[ ] productimages → products
[ ] productsizes → products, sizes
[ ] productcolors → products, colors
[ ] orders → customers, users
[ ] order_details → orders, products
[ ] shippings → orders, customers
[ ] payments → orders, customers
[ ] reviews → products
[ ] subcategories → categories
[ ] childcategories → subcategories
[ ] banners → banner_categories
[ ] campaign_reviews → campaigns

Phase 4: Add Missing Columns
[ ] products — subcategory_id, childcategory_id
[ ] orders — payment_status, notes
[ ] shippings — district, shipping_charge_id
[ ] customers — otp, otp_expires_at
[ ] reviews — customer_id

Phase 5: New Architecture Tables
[ ] inventory_ledger
[ ] outbox_messages
[ ] webhook_events
[ ] audit_logs
[ ] idempotency_keys

Phase 6: Model Fixes
[ ] Product — belongsTo for category/subcategory/childcategory/brand
[ ] Order — hasOne for shipping/payment, remove product()
[ ] Category — belongsTo parent, hasMany children
[ ] All models — $fillable, $casts
[ ] All models — proper return types
```


---

## ১১. Engineering Rules (Final)

1. Controllers transport data; they do not decide business policy.
2. Use cases express business operations.
3. Domain objects protect business invariants.
4. Repositories where they represent meaningful persistence abstractions — not ceremony.
5. Queries may use direct Eloquent without forcing repository indirection.
6. DTOs exist at meaningful boundaries only.
7. Form Requests validate transport input.
8. Policies authorize object access.
9. Modules communicate through contracts/events, not internal Eloquent models.
10. No module may create an architectural cycle.
11. External APIs are always behind adapters/ports.
12. Webhook handlers are authenticated and idempotent.
13. Payment commands are idempotent and auditable.
14. Inventory mutations are concurrency-safe.
15. No external HTTP call inside a DB transaction.
16. Money uses decimal-safe or integer-minor-unit representations.
17. State transitions are explicit and validated.
18. Cache is never the source of financial truth.
19. Sensitive actions produce audit records.
20. Secrets never enter application logs.
21. CI enforces security and architecture rules.
22. Database migrations are backward compatible during deployment.
23. Every critical async operation has retry and failure semantics.
24. Every critical external integration has observability.
25. Every module has rollback-safe migration checkpoints.

---

## ১২. Definition of Done (Per Module)

A module is not considered migrated until ALL are true:

```
[ ] Business boundaries documented
[ ] Public contract defined
[ ] Controller is transport-only
[ ] Authorization enforced
[ ] Validation centralized
[ ] Use cases implemented
[ ] Domain invariants protected
[ ] Persistence boundary defined
[ ] Cross-module dependencies reviewed
[ ] Domain/integration events reviewed
[ ] Idempotency requirements reviewed
[ ] Audit requirements reviewed
[ ] Queries profiled
[ ] N+1 checks passed
[ ] Unit tests passed
[ ] Feature tests passed
[ ] Failure-path tests passed
[ ] Concurrency tests passed
[ ] Architecture rules passed
[ ] Observability added
[ ] Rollback plan documented
[ ] Production smoke test passed
```

---

## ১৩. Success Criteria

- [ ] Laravel 13 + PHP 8.5 running
- [ ] All code in bounded contexts/modules
- [ ] Zero business logic in controllers
- [ ] Authorization policies on all sensitive resources
- [ ] Payment idempotency + webhook verification
- [ ] Inventory concurrency-safe with ledger
- [ ] Transactional outbox for async operations
- [ ] Redis for cache/session/queue/cart
- [ ] MySQL indexes optimized
- [ ] 80%+ behavioral test coverage
- [ ] Zero `$guarded = []` in models
- [ ] Zero magic strings (Enums everywhere)
- [ ] Architecture fitness tests in CI
- [ ] Audit trail for sensitive operations
- [ ] Health/readiness endpoints
- [ ] Zero-downtime deployment capability
