# Phase 03 — Database Schema Audit

## Objective
Comprehensive audit and architectural assessment of all 41 migration files, 38 database tables, column types, foreign key constraints, data integrity vulnerabilities, financial column formatting, index gaps, and schema typos for the MondolShopBD application.

## Why This Phase Exists
A robust Modular Monolith requires absolute data integrity at the database layer. Auditing the database schema enables the systematic removal of invalid data types, non-atomic financial computations, missing relational constraints, and performance bottlenecks in subsequent database refactoring phases (Phases 23–28).

---

## 1. Database Schema Inventory & Critical Audit Findings

### Summary Metrics
- **Total Migration Files**: 41
- **Total Tables in Migrations**: 38
- **Tables with Relational Columns but ZERO Foreign Key Constraints**: 16 tables
- **Financial/Currency Columns with Invalid Data Types**: 12 columns
- **Typo Columns in Schema**: 5 columns
- **Max Constraint Vulnerability**: `products.campaign_id` defined as `tinyInteger` (limit: 127)

---

## 2. Financial & Currency Column Audit (12 Columns)

According to `DEVELOPMENT-RULES.md` (Rule 15: Financial Rules), all monetary values MUST be stored as `decimal(12,2)`. Using `integer`, `float`, or `string` causes fractional money loss, currency truncation, and floating-point IEEE-754 rounding inaccuracies.

| # | Table | Column | Current Type | Target Type | Issue Description |
|---|---|---|---|---|---|
| 1 | `products` | `purchase_price` | `integer` | `decimal(12,2)` | Fractional purchase amounts truncated |
| 2 | `products` | `old_price` | `integer` (nullable) | `decimal(12,2)` | Cannot store fractional original prices |
| 3 | `products` | `new_price` | `integer` | `decimal(12,2)` | Cannot store paisa/fractional sale prices |
| 4 | `customers` | `balance` | `float` | `decimal(12,2)` | Floating-point accumulation errors (e.g. 100.01 + 0.01 = 100.020000000000003) |
| 5 | `districts` | `shippingfee` | `string` | `decimal(12,2)` | Money stored as text string; sorting/arithmetic fails in DB |
| 6 | `orders` | `amount` | `integer` | `decimal(12,2)` | Order total cannot represent decimals |
| 7 | `orders` | `discount` | `integer` | `decimal(12,2)` | Discount calculation truncates paisa |
| 8 | `orders` | `shipping_charge` | `integer` | `decimal(12,2)` | Shipping cost cannot have decimal precision |
| 9 | `order_details` | `purchase_price` | `integer` | `decimal(12,2)` | Cost of goods sold (COGS) loses decimal precision |
| 10 | `order_details` | `sale_price` | `integer` | `decimal(12,2)` | Item price loses decimal precision |
| 11 | `payments` | `amount` | `integer` | `decimal(12,2)` | Gateway payment amounts rounded/truncated |
| 12 | `shipping_charges` | `amount` | `integer` | `decimal(12,2)` | Fixed shipping fees lack decimal precision |

---

## 3. Foreign Key Constraint Gap Analysis (16 Tables)

Currently, zero foreign key constraints exist across all relational tables. Relationships exist solely through code conventions, allowing orphan records and broken referential integrity.

| Table | Relational Columns | Target Foreign Table & Column | Cascade / Delete Policy |
|---|---|---|---|
| `products` | `category_id`, `brand_id`, `campaign_id` | `categories(id)`, `brands(id)`, `campaigns(id)` | `restrictOnDelete` / `nullOnDelete` |
| `categories` | `parent_id` | `categories(id)` | `nullOnDelete` |
| `subcategories` | `category_id` | `categories(id)` | `cascadeOnDelete` |
| `childcategories` | `subcategory_id` | `subcategories(id)` | `cascadeOnDelete` |
| `productimages` | `product_id` | `products(id)` | `cascadeOnDelete` |
| `productsizes` | `product_id`, `size_id` | `products(id)`, `sizes(id)` | `cascadeOnDelete` |
| `productcolors` | `product_id`, `color_id` | `products(id)`, `colors(id)` | `cascadeOnDelete` |
| `banners` | `category_id` | `banner_categories(id)` | `nullOnDelete` |
| `districts` | `area_id` | `shipping_charges(id)` | `nullOnDelete` |
| `orders` | `customer_id` | `customers(id)` | `restrictOnDelete` |
| `order_details` | `order_id`, `product_id` | `orders(id)`, `products(id)` | `cascadeOnDelete` / `restrict` |
| `shippings` | `order_id`, `customer_id` | `orders(id)`, `customers(id)` | `cascadeOnDelete` |
| `payments` | `order_id`, `customer_id` | `orders(id)`, `customers(id)` | `cascadeOnDelete` |
| `reviews` | `product_id` | `products(id)` | `cascadeOnDelete` |
| `campaign_reviews` | `campaign_id` | `campaigns(id)` | `cascadeOnDelete` |
| `incomplete_orders`| `customer_id` | `customers(id)` | `nullOnDelete` |

---

## 4. Schema Typo Columns & Status Inconsistencies

### 4.1 Typo Columns
1. `categories.meta_decription` → should be `meta_description`
2. `subcategories.meta_decription` → should be `meta_description`
3. `childcategories.meta_decription` → should be `meta_description`
4. `reviews.ratting` → should be `rating`
5. `sms_gateways.serderid` → should be `sender_id`

### 4.2 Status Type Inconsistencies
- `orders.order_status`: Defined as `string(55)`. Should use backed PHP Enum mapped to `unsignedTinyInteger` in Phase 44 & 64.
- `products.status`: Defined as `tinyInteger` default 1.
- `categories.status`: Defined as `tinyInteger` default 1.
- `customers.status`: Defined as `string` or `tinyInteger`.

---

## 5. Remediation Roadmap for Database Phases

- **Phase 23 (DB Column Type Fixes)**: Safe expand/contract migration of 12 financial columns to `decimal(12,2)`.
- **Phase 24 (DB Add Indexes)**: Add composite indexes for order filtering, product slug lookups, category trees.
- **Phase 25 (DB Add Foreign Keys)**: Clean orphan records and add constraints to all 16 relational tables.
- **Phase 26 (DB Add Missing Columns & Fix Typos)**: Rename typo columns via backward-compatible view/alias migrations.
- **Phase 28 (DB New Architecture Tables)**: Add `outbox_messages`, `audit_logs`, and `inventory_ledger` tables.

---

## 6. Definition of Done Checklist

- [x] Complete audit of all 41 migrations and 38 tables
- [x] Full list of 12 financial columns requiring `decimal(12,2)`
- [x] Full list of 16 tables lacking Foreign Key constraints
- [x] Identification of 5 schema typo columns
- [x] Status column inconsistency documented
- [x] Remediation sequencing mapped to Phases 23–28
- [x] Ready for Phase 04 (External Integration Inventory)

