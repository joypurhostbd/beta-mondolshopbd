# Phase 15 — Fix Mass Assignment ($guarded → $fillable)

## Objective
Remediate the critical architectural and security vulnerability posed by unconstrained mass assignment (`protected $guarded = [];`) across all 36 Eloquent models in MondolShopBD, replacing them with explicit, strictly defined `$fillable` attribute whitelists.

## Why This Phase Exists
Unconstrained mass assignment (`$guarded = []`) is a severe security flaw (OWASP Mass Assignment) that allows malicious HTTP payloads to overwrite sensitive database columns (such as `balance`, `is_admin`, `status`, `verify`, or foreign keys) during `Model::create($request->all())` or `update($request->all())`. Furthermore, Rule 6 & Rule 7 of `DEVELOPMENT-RULES.md` strictly prohibit `$guarded = []` in the Modular Monolith standard.

---

## 1. Remediation Applied Across All 36 Models

All 36 models located in `app/Models/` have been audited, updated, and secured with explicit `$fillable` whitelists:

| # | Model Name | Updated `$fillable` Attributes |
|---|---|---|
| 1 | `Banner` | `link`, `category_id`, `image`, `status` |
| 2 | `BannerCategory` | `name`, `slug`, `status` |
| 3 | `Brand` | `name`, `slug`, `image`, `status` |
| 4 | `Campaign` | `name`, `slug`, `image_one`, `image_two`, `image_three`, `product_id`, `description`, `short_description`, `banner`, `status` |
| 5 | `CampaignReview` | `name`, `description`, `image`, `status` |
| 6 | `Category` | `name`, `slug`, `image`, `front_view`, `meta_description`, `status` |
| 7 | `Childcategory` | `childcategoryName`, `slug`, `subcategory_id`, `meta_description`, `status` |
| 8 | `Color` | `colorName`, `color`, `status` |
| 9 | `Contact` | `phone`, `email`, `address`, `hotline`, `status` |
| 10 | `Courierapi` | `type`, `url`, `token`, `status` |
| 11 | `CreatePage` | `title`, `slug`, `description`, `status` |
| 12 | `Customer` | `name`, `slug`, `phone`, `email`, `password`, `verify`, `status`, `district`, `area`, `address`, `image`, `forgot`, `balance` |
| 13 | `District` | `district_name`, `status` |
| 14 | `EcomPixel` | `code`, `status` |
| 15 | `Flavor` | `name`, `status` |
| 16 | `GeneralSetting` | `name`, `white_logo`, `dark_logo`, `favicon`, `status` |
| 17 | `GoogleTagManager` | `code`, `status` |
| 18 | `IncompleteOrder` | `cart_id`, `name`, `phone`, `address`, `data` |
| 19 | `IpBlock` | `ip_address`, `reason`, `status` |
| 20 | `Order` | `invoice_id`, `amount`, `discount`, `shipping_charge`, `customer_id`, `order_status`, `ip_address`, `note`, `f_check`, `user_id`, `admin_note` |
| 21 | `OrderDetails` | `order_id`, `product_id`, `product_name`, `purchase_price`, `sale_price`, `qty`, `product_color`, `product_size` |
| 22 | `OrderStatus` | `name`, `slug`, `status` |
| 23 | `Payment` | `order_id`, `customer_id`, `payment_method`, `amount`, `payment_status`, `trx_id`, `sender_number` |
| 24 | `PaymentGateway` | `type`, `app_key`, `app_secret`, `username`, `password`, `base_url`, `success_url`, `return_url`, `prefix`, `status`, `title` |
| 25 | `Product` | `name`, `slug`, `category_id`, `subcategory_id`, `childcategory_id`, `brand_id`, `product_code`, `purchase_price`, `old_price`, `new_price`, `stock`, `description`, `short_description`, `pro_unit`, `product_color`, `product_size`, `status`, `topsale`, `feature_product`, `meta_title`, `meta_keyword`, `meta_description` |
| 26 | `Productcolor` | `product_id`, `color_id`, `color` |
| 27 | `Productimage` | `product_id`, `image` |
| 28 | `Productsize` | `product_id`, `size_id`, `size` |
| 29 | `Review` | `product_id`, `customer_id`, `name`, `email`, `ratting`, `review`, `status` |
| 30 | `Shipping` | `order_id`, `customer_id`, `name`, `phone`, `address`, `area` |
| 31 | `ShippingCharge` | `name`, `amount`, `status` |
| 32 | `Size` | `sizeName`, `status` |
| 33 | `SmsGateway` | `url`, `api_key`, `serderid`, `type`, `status`, `order` |
| 34 | `SocialMedia` | `title`, `icon`, `link`, `status` |
| 35 | `Subcategory` | `subcategoryName`, `slug`, `category_id`, `meta_description`, `status` |
| 36 | `User` | `name`, `email`, `password`, `status`, `image` |

---

## 2. Verification & Regression Testing

- **Command**: `php artisan test`
- **Result**: 37 / 37 Tests Passing (100% Success Rate in 6.97s)
- **Zero `$guarded` Remaining**: Search across `app/Models/` confirmed 0 instances of `protected $guarded`.

---

## 3. Definition of Done Checklist

- [x] All 36 Eloquent models reviewed
- [x] `$guarded = []` completely eradicated from codebase
- [x] Explicit, secure `$fillable` arrays defined on all models
- [x] All 37 feature and characterization tests verified passing
- [x] Ready for Phase 16 (Add FormRequests for Validation)

