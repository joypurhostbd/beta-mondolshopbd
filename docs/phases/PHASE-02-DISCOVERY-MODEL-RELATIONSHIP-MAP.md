# Phase 02 — Model & Relationship Map

## Objective
Comprehensive audit and architectural mapping of all 36 Eloquent models, their relationships, foreign key directions, mass-assignment configurations, casting definitions, and domain module clustering for the MondolShopBD application.

## Why This Phase Exists
This phase ensures that every model's data access boundaries, relationship semantics, and security vulnerabilities (mass assignment via `$guarded = []` and inverted keys in `hasOne`/`belongsTo`) are precisely mapped before introducing characterization tests and module refactoring.

---

## 1. Complete Model Inventory (36 Total Models)

| # | Model | Table | Fillable / Guarded | Casts | Relations Count | Domain Module |
|---|---|---|---|---|---|---|
| 1 | `Banner` | `banners` | ⚠️ `$guarded = []` | ❌ None | 1 | Marketing / Banner |
| 2 | `BannerCategory` | `banner_categories` | ⚠️ `$guarded = []` | ❌ None | 0 | Marketing / Banner |
| 3 | `Brand` | `brands` | ⚠️ `$guarded = []` | ❌ None | 0 | Catalog |
| 4 | `Campaign` | `campaigns` | ⚠️ `$guarded = []` | ❌ None | 2 | Marketing / Campaign |
| 5 | `CampaignReview` | `campaign_reviews` | ⚠️ Unspecified | ❌ None | 0 | Marketing / Campaign |
| 6 | `Category` | `categories` | ⚠️ `$guarded = []` | ❌ None | 9 | Catalog |
| 7 | `Childcategory` | `childcategories` | ⚠️ `$guarded = []` | ❌ None | 1 | Catalog |
| 8 | `Color` | `colors` | ⚠️ `$guarded = []` | ❌ None | 0 | Catalog |
| 9 | `Contact` | `contacts` | ⚠️ `$guarded = []` | ❌ None | 0 | Operations / Settings |
| 10 | `Courierapi` | `courierapis` | ⚠️ `$guarded = []` | ❌ None | 0 | Shipping |
| 11 | `CreatePage` | `create_pages` | ⚠️ `$guarded = []` | ❌ None | 0 | Content / Pages |
| 12 | `Customer` | `customers` | ✅ `$fillable` (partial) | ❌ None | 2 | Identity |
| 13 | `District` | `districts` | ⚠️ Unspecified | ❌ None | 0 | Shipping / Core |
| 14 | `EcomPixel` | `ecom_pixels` | ⚠️ `$guarded = []` | ❌ None | 0 | Operations / Analytics |
| 15 | `Flavor` | `flavors` | ⚠️ `$guarded = []` | ❌ None | 0 | Catalog |
| 16 | `GeneralSetting` | `general_settings` | ⚠️ `$guarded = []` | ❌ None | 0 | Operations / Settings |
| 17 | `GoogleTagManager` | `google_tag_managers` | ⚠️ `$guarded = []` | ❌ None | 0 | Operations / Analytics |
| 18 | `IncompleteOrder` | `incomplete_orders` | ⚠️ Unspecified | ❌ None | 0 | Order |
| 19 | `IpBlock` | `ip_blocks` | ⚠️ Unspecified | ❌ None | 0 | Security / Operations |
| 20 | `Order` | `orders` | ⚠️ Unspecified | ❌ None | 7 | Order |
| 21 | `OrderDetails` | `order_details` | ⚠️ Unspecified | ❌ None | 3 | Order |
| 22 | `OrderStatus` | `order_statuses` | ⚠️ `$guarded = []` | ❌ None | 1 | Order |
| 23 | `Payment` | `payments` | ⚠️ Unspecified | ❌ None | 0 | Payment |
| 24 | `PaymentGateway` | `payment_gateways` | ⚠️ `$guarded = []` | ❌ None | 0 | Payment |
| 25 | `Product` | `products` | ⚠️ `$guarded = []` | ❌ None | 13 | Catalog |
| 26 | `Productcolor` | `productcolors` | ⚠️ Unspecified | ❌ None | 1 | Catalog |
| 27 | `Productimage` | `productimages` | ⚠️ Unspecified | ❌ None | 0 | Catalog |
| 28 | `Productsize` | `productsizes` | ⚠️ Unspecified | ❌ None | 1 | Catalog |
| 29 | `Review` | `reviews` | ⚠️ `$guarded = []` | ❌ None | 0 | Social Proof / Review |
| 30 | `Shipping` | `shippings` | ⚠️ Unspecified | ❌ None | 1 | Shipping |
| 31 | `ShippingCharge` | `shipping_charges` | ⚠️ `$guarded = []` | ❌ None | 0 | Shipping |
| 32 | `Size` | `sizes` | ⚠️ `$guarded = []` | ❌ None | 0 | Catalog |
| 33 | `SmsGateway` | `sms_gateways` | ⚠️ `$guarded = []` | ❌ None | 0 | Operations / Integrations |
| 34 | `SocialMedia` | `social_media` | ⚠️ `$guarded = []` | ❌ None | 0 | Operations / Settings |
| 35 | `Subcategory` | `subcategories` | ⚠️ `$guarded = []` | ❌ None | 3 | Catalog |
| 36 | `User` | `users` | ✅ `$fillable` | ✅ Yes | 0 | Identity / Admin |

---

## 2. Inverted Relationships Audit (15 Critical Semantic Inversions)

In Eloquent, if Model A contains the foreign key `b_id`, the relationship is `ModelA->belongsTo(ModelB, 'b_id')`.
Using `hasOne` with inverted argument keys (`hasOne(ModelB, 'id', 'b_id')`) produces incorrect join conditions, breaks eager loading optimizations, and prevents cascading operations.

| Source Model | FK Column | Current Broken Definition | Required Correct Definition |
|---|---|---|---|
| `Banner` | `category_id` | `hasOne(BannerCategory::class, 'id', 'category_id')` | `belongsTo(BannerCategory::class, 'category_id')` |
| `Campaign` | `product_id` | `hasOne(Product::class, 'id', 'product_id')` | `belongsTo(Product::class, 'product_id')` |
| `Category` | `parent_id` | `hasOne(Category::class, 'id', 'parent_id')` | `belongsTo(Category::class, 'parent_id')` |
| `Childcategory` | `subcategory_id` | `hasOne(Subcategory::class, 'id', 'subcategory_id')` | `belongsTo(Subcategory::class, 'subcategory_id')` |
| `Product` | `category_id` | `hasOne(Category::class, 'id', 'category_id')` | `belongsTo(Category::class, 'category_id')` |
| `Product` | `subcategory_id` | `hasOne(Subcategory::class, 'id', 'subcategory_id')` | `belongsTo(Subcategory::class, 'subcategory_id')` |
| `Product` | `childcategory_id` | `hasOne(Childcategory::class, 'id', 'childcategory_id')` | `belongsTo(Childcategory::class, 'childcategory_id')` |
| `Product` | `brand_id` | `hasOne(Brand::class, 'id', 'brand_id')` | `belongsTo(Brand::class, 'brand_id')` |
| `Productcolor` | `color_id` | `hasOne(Color::class, 'id', 'color_id')` | `belongsTo(Color::class, 'color_id')` |
| `Productsize` | `size_id` | `hasOne(Size::class, 'id', 'size_id')` | `belongsTo(Size::class, 'size_id')` |
| `Shipping` | `area` | `hasOne(ShippingCharge::class, 'id', 'area')` | `belongsTo(ShippingCharge::class, 'area')` |
| `Subcategory` | `category_id` | `hasOne(Category::class, 'id', 'category_id')` | `belongsTo(Category::class, 'category_id')` |
| `Order` | `order_id` in `shippings` | `belongsTo(Shipping::class, 'id', 'order_id')` | `hasOne(Shipping::class, 'order_id')` |
| `Order` | `order_id` in `payments` | `belongsTo(Payment::class, 'id', 'order_id')` | `hasOne(Payment::class, 'order_id')` |
| `Order` | `order_id` in `order_details`| `belongsTo(OrderDetails::class, 'id', 'order_id')` | `hasMany(OrderDetails::class, 'order_id')` |

---

## 3. Business Logic & Anti-Patterns Inside Models

### 3.1 `Order::fraud_check()`
- **Location**: `app/Models/Order.php:L53-L175`
- **Issue**: Directly executes external cURL/HTTP requests to `dash.hoorin.com`, parses Pathao, Steadfast, RedX, and Paperfly courier APIs, and calculates statistics inside the Eloquent Model.
- **Remediation Plan**: Extract into `App\Modules\Shipping\Services\FraudCheckService` with dedicated Port and Adapter in Phase 73.

### 3.2 Invoice ID Generation in `Order::booted()`
- **Location**: `app/Models/Order.php:L12-L21`
- **Issue**: Uses `rand(1111, 9999)` appended to invoice_id during `creating` hook. Non-deterministic and collision-prone under concurrency.
- **Remediation Plan**: Move to dedicated `InvoiceNumberGenerator` service in Phase 63.

---

## 4. Domain Module Clustering for Modular Monolith

```
┌─────────────────────────────────────────────────────────────┐
│                       CATALOG MODULE                        │
│ Product, Category, Subcategory, Childcategory, Brand,       │
│ Color, Size, Flavor, Productimage, Productsize, Productcolor│
└─────────────────────────────────────────────────────────────┘
┌──────────────────────────────┐┌──────────────────────────────┐
│       IDENTITY MODULE        ││         ORDER MODULE         │
│ User, Customer, IpBlock      ││ Order, OrderDetails, Status, │
│                              ││ IncompleteOrder              │
└──────────────────────────────┘└──────────────────────────────┘
┌──────────────────────────────┐┌──────────────────────────────┐
│        PAYMENT MODULE        ││       SHIPPING MODULE        │
│ Payment, PaymentGateway      ││ Shipping, ShippingCharge,    │
│                              ││ Courierapi, District         │
└──────────────────────────────┘└──────────────────────────────┘
┌──────────────────────────────┐┌──────────────────────────────┐
│      MARKETING & PAGES       ││     SETTINGS & ANALYTICS     │
│ Campaign, CampaignReview,    ││ GeneralSetting, SocialMedia, │
│ Banner, BannerCategory,      ││ EcomPixel, GoogleTagManager, │
│ Review, CreatePage           ││ Contact, SmsGateway          │
└──────────────────────────────┘└──────────────────────────────┘
```

---

## 5. Definition of Done Checklist

- [x] Complete inventory of all 36 Eloquent models
- [x] Identification of 23 models using `$guarded = []`
- [x] Audit of missing `$casts` across 35 models
- [x] Mapping of 15 broken `hasOne` / `belongsTo` inversions
- [x] Detection of model-level business logic (`Order::fraud_check`)
- [x] Domain module clustering established
- [x] Ready for Phase 03 (Database Schema Audit)

