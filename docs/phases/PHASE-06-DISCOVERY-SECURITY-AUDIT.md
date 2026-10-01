# Phase 06 — Security Code Audit

## Objective
Comprehensive vulnerability assessment and security code audit of the MondolShopBD Laravel application, covering Mass Assignment, Stored XSS, Insecure File Uploads, Spatie RBAC & Policy Gaps, IDOR vulnerabilities, Missing Rate Limiting, Unprotected Utility Endpoints, and Debug Residue.

## Why This Phase Exists
Security must precede structural refactoring. Identifying critical vulnerabilities (P0–P3) early ensures that security baselines (Phases 15–22) are systematically hardened before moving code into Modular Monolith boundaries, preventing vulnerabilities from propagating into new modules.

---

## 1. Executive Security Vulnerability Matrix

| # | Vulnerability Category | Severity | Occurrences | Impact Summary | Remediation Phase |
|---|---|---|---|---|---|
| 1 | **Unprotected Utility Routes** | 🔴 Critical (P0) | 2 routes (`/cc`, `/controller`) | Unauthenticated cache flushing and arbitrary artisan controller generation | Phase 01 / Immediate |
| 2 | **Spatie RBAC Bypass** | 🔴 Critical (P0) | 0 of 317 routes protected by `role`/`permission` | Any authenticated admin user has root privileges over all settings, roles, orders | Phase 17 |
| 3 | **Mass Assignment** | 🔴 Critical (P0) | 23 models with `$guarded = []`, 23 controllers with `$request->all()` | Overwriting sensitive columns via parameter tampering | Phase 15 |
| 4 | **Zero Authorization Policies** | 🔴 Critical (P0) | 0 Policy classes in `app/Policies` | Inability to verify object-level ownership (IDOR risk) | Phase 17 & 18 |
| 5 | **Stored XSS** | 🔴 High (P1) | 12 Blade templates using `{!! !!}` | Malicious JavaScript injection via product descriptions and metadata | Phase 16 & 22 |
| 6 | **Insecure File Uploads** | 🔴 High (P1) | 10 controllers using `getClientOriginalName()` + `move()` | Path traversal, missing MIME verification, arbitrary file execution | Phase 19 |
| 7 | **Missing Rate Limiting** | 🟡 Medium (P2) | 0 routes using `throttle:` | OTP brute forcing, SMS gateway draining, auth credential stuffing | Phase 20 |
| 8 | **Insecure SSL Verification** | 🔴 High (P1) | `CURLOPT_SSL_VERIFYPEER = false` in SMS cURL | Man-in-the-Middle (MITM) interception of SMS OTPs | Phase 57 |
| 9 | **Debug Statements in Prod** | 🟢 Low (P3) | 4 `dd()` occurrences across 3 controllers | Information disclosure / application halt | Cleanup / Phase 16 |

---

## 2. Detailed Vulnerability Analyses

### 2.1 Stored Cross-Site Scripting (XSS) — 12 Blade Templates
Unescaped Blade tags (`{!! !!}`) render un-sanitized HTML directly from database columns:

1. `resources/views/frontEnd/layouts/pages/details.blade.php`: `{!! $details->description !!}`
2. `resources/views/frontEnd/layouts/pages/page.blade.php`: `{!! $page->description !!}`
3. `resources/views/frontEnd/layouts/ajax/quickview.blade.php`: `{!! $data->short_description !!}`
4. `resources/views/frontEnd/layouts/customer/order_note.blade.php`: `{!! $order->admin_note !!}`
5. `resources/views/emails/order_delivered.blade.php`: `{!! $order->admin_note !!}`
6. `resources/views/frontEnd/layouts/pages/category.blade.php`: `{!! $category->meta_description !!}`
7. `resources/views/frontEnd/layouts/pages/subcategory.blade.php`: `{!! $subcategory->meta_description !!}`
8. `resources/views/frontEnd/layouts/pages/childcategory.blade.php`: `{!! $childcategory->meta_description !!}`
9. `resources/views/backEnd/createpage/edit.blade.php`: `{!! $edit_data->description !!}`
10. `resources/views/backEnd/category/edit.blade.php`: `{!! $edit_data->meta_description !!}`
11. `resources/views/backEnd/subcategory/edit.blade.php`: `{!! $edit_data->meta_description !!}`
12. `resources/views/backEnd/childcategory/edit.blade.php`: `{!! $edit_data->meta_description !!}`

*Remediation*: Switch to `{{ $var }}` default escaping or pass rich text through a strict HTMLPurifier pipeline before output.

### 2.2 Insecure File Uploads (10 Controllers)
The following controllers use raw `getClientOriginalName()` and `->move()` directly into `public/uploads`:
- `BannerController.php` (2)
- `BrandController.php` (2)
- `CampaignController.php` (8)
- `CategoryController.php` (2)
- `CustomerManageController.php` (1)
- `GeneralSettingController.php` (6)
- `ProductController.php` (2)
- `SubcategoryController.php` (2)
- `UserController.php` (2)
- `CustomerController.php` (1)

*Remediation*: Use Laravel Storage (`$request->file('image')->store('directory', 'public')`) with strict FormRequest rules: `'image|mimes:jpg,jpeg,png,webp|max:2048'`.

### 2.3 Spatie RBAC & Policy Gaps
- `spatie/laravel-permission` is installed, but `routes/web.php` admin groups only specify `['auth', 'lock', 'check_refer']`.
- No route applies `role:admin` or `permission:edit-order`.
- 0 Policy classes exist to check if a Customer owns an order before rendering an invoice (`/customer/invoice?order_id=X`).

### 2.4 Missing Rate Limiting (0 Throttle Endpoints)
None of the sensitive endpoints implement `throttle`:
- `POST /customer/signin` (Login)
- `POST /customer/resend-otp` (SMS Flooding)
- `POST /customer/forgot-verify` (OTP Brute Force)
- `POST /customer/order-save` (Checkout Spamming)
- `POST /login` (Admin Backoffice Login)

---

## 3. Security Hardening Roadmap (Phases 15–22)

```
┌─────────────────────────────────────────────────────────────┐
│                 SECURITY HARDENING ROADMAP                  │
├─────────────────────────────────────────────────────────────┤
│ Phase 15: Fix Mass Assignment (All $fillable models)        │
│ Phase 16: Add FormRequests for all 40 controllers           │
│ Phase 17: Add Authorization Policies & Spatie RBAC Routes   │
│ Phase 18: Fix IDOR Vulnerabilities with Policy Gates        │
│ Phase 19: File Upload Hardening (MIME, Storage, Random UUID)│
│ Phase 20: Rate Limiting on Auth, OTP, and Checkout Routes   │
│ Phase 21: Session & Password Security (Hashed OTPs, TLS)    │
│ Phase 22: Security Headers & Content Security Policy (CSP)  │
└─────────────────────────────────────────────────────────────┘
```

---

## 4. Definition of Done Checklist

- [x] Complete audit of all security vulnerabilities across routes, controllers, models, and views
- [x] Cataloging of 12 Stored XSS candidate templates
- [x] Identification of 10 controllers with insecure file upload implementations
- [x] Assessment of Spatie RBAC bypass and Policy gap
- [x] Mapping of missing rate limit endpoints
- [x] Remediation roadmap structured for Phases 15–22
- [x] Ready for Phase 07 (Legacy Pattern Inventory)

