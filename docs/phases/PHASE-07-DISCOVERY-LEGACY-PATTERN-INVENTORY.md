# Phase 07 — Legacy Pattern Inventory

## Objective
Comprehensive cataloging and architectural audit of legacy coding patterns, anti-patterns, template database queries, inline scripts, deprecated Composer packages, and untyped view bindings across the MondolShopBD Laravel application.

## Why This Phase Exists
Migrating to a clean Modular Monolith requires dismantling legacy anti-patterns (such as DB calls in Blade templates and procedural controller methods). Cataloging these occurrences provides the exact roadmap for frontend modernization (Phases 77–78), package upgrades (Phases 29–36), and ViewModel adoption.

---

## 1. Executive Legacy Pattern Summary

| # | Anti-Pattern | Severity | Scope & Volume | Architectural Violation | Target Phase |
|---|---|---|---|---|---|
| 1 | **Database Queries in Blade** | 🔴 Critical | 15 occurrences across 6 views | Violates MVC / Rule 21 (Blade Rules); causes severe N+1 overhead | Phase 77 (ViewModels) |
| 2 | **Inline `<script>` Tags** | 🟠 High | 811 tags across 120 templates | Violates CSP / Asset Pipeline; prevents strict CSP headers | Phase 22 & 77 (Vite Assets) |
| 3 | **Un-typed `compact()` Passing** | 🟡 Medium | 98 occurrences in 29 controllers | Violates Rule 6 (ViewModel Rules); lacks type safety | Phase 77 (ViewModels) |
| 4 | **Abandoned Packages in `composer.json`** | 🔴 High | 5 packages | Blocks PHP 8.2+ and Laravel 10/11/12/13 upgrades | Phase 30 (Remove Deprecated Packages) |
| 5 | **Synchronous Infrastructure (0 Jobs)** | 🟡 Medium | 0 Jobs, 0 Events, 0 Listeners | Violates Rule 18 (Async / Queue Rules) | Phase 38 (Queue Architecture) |
| 6 | **Legacy Session Cart** | 🟡 Medium | `olimortimer/laravelshoppingcart` | Abandoned library storing state in file sessions | Phase 60 (Redis Cart) |

---

## 2. Direct Database Queries in Blade Templates (15 Occurrences)

Executing queries inside Blade files tightly couples presentation to the database schema and produces unpredictable query spikes during page rendering:

1. `resources/views/backEnd/layouts/master.blade.php`:
   - `Review::where('status', 'pending')->count()` — Run on every admin page load.
2. `resources/views/frontEnd/layouts/customer/order_success.blade.php`:
   - `Payment::where('order_id', $order->id)->first()` — Query in presentation template.
3. `resources/views/frontEnd/layouts/customer/tracking_result.blade.php`:
   - `Orderstatus::where('id', $order->order_status)->first()`
   - `OrderDetails::where('order_id', $order->id)->get()`
4. `resources/views/frontEnd/layouts/pages/category.blade.php`:
   - `Productcolor::where('product_id', $value->id)->get()` (2x in loops)
   - `Productsize::where('product_id', $value->id)->get()` (2x in loops)
5. `resources/views/frontEnd/layouts/pages/index.blade.php`:
   - `Productcolor::where('product_id', $value->id)->get()` (2x in loops)
   - `Productsize::where('product_id', $value->id)->get()` (2x in loops)
6. `resources/views/emails/order_accept.blade.php`, `order_delivered.blade.php`, `order_place.blade.php`:
   - `Order::where(...)` queries embedded inside email markup.

---

## 3. Deprecated & Abandoned Package Inventory

| Package Name | Current Version | Status | Replacement Strategy |
|---|---|---|---|
| `laravelcollective/html` | `^6.3` | ❌ Abandoned | Replace with standard native Blade syntax / components in Phase 30 & 77 |
| `olimortimer/laravelshoppingcart` | `^6.0` | ❌ Abandoned (2020) | Replace with custom, high-performance `RedisCartService` in Phase 60 |
| `laravel/ui` | `^4.2` | ⚠️ Deprecated | Modernize with Laravel Sanctum / Clean Custom Auth in Phase 30 & 56 |
| `shurjopayv2/laravel8` | `dev-master` | ⚠️ Pinned to dev-master | Replace with clean, standalone `ShurjoPayAdapter` via HTTP client in Phase 68 |
| `intervention/image` | `^2.7` | ⚠️ v2 Deprecated | Upgrade to `intervention/image` v3 in Phase 35 |

---

## 4. Frontend Script Sprawl (811 Inline Scripts)

- **Finding**: 811 inline `<script>` tags across 120 Blade views containing raw AJAX calls, form submissions, and DOM manipulators.
- **Consequence**: Prevents deployment of strict Content Security Policy (`script-src 'self'`) and hinders frontend caching.
- **Modernization Strategy (Phase 77)**: Extract reusable JavaScript modules into `resources/js/modules/` bundled via Vite.

---

## 5. Definition of Done Checklist

- [x] Complete audit of 15 Blade template database queries
- [x] Full scan of 811 inline `<script>` tags across 120 templates
- [x] Identification of 98 `compact()` usages across 29 controllers
- [x] Audit of 5 deprecated/abandoned packages in `composer.json`
- [x] Documented modernization strategy for ViewModels, Vite bundling, and Redis Cart
- [x] Ready for Phase 08 (Risk Classification & Gap Analysis)

