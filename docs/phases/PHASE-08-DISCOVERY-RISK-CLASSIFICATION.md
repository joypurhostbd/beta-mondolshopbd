# Phase 08 — Risk Classification & Gap Analysis

## Objective
Consolidate and synthesize all audit findings across the Discovery Area (Phases 01–07) into a master Architectural Gap Analysis and prioritized Risk Matrix (P0 Critical to P3 Low), establishing the baseline requirements for Testing (Phases 09–14) and Security Hardening (Phases 15–22).

## Why This Phase Exists
Phase 08 serves as the capstone of the Discovery Track. It bridges empirical audit findings into an actionable, prioritized refactoring roadmap, ensuring high-risk defects and security flaws are resolved systematically before domain module extraction.

---

## 1. Master Architectural Gap Analysis

| Dimension | Legacy Baseline (Current State) | Modular Monolith (Target State) | Resolution Track |
|---|---|---|---|
| **Architecture** | Flat MVC with direct inter-model coupling | Modular Monolith (6 Domain Modules) + Ports & Adapters | Module Structure (Phases 43–76) |
| **Framework & Runtime**| Laravel 9.19, PHP ^8.0.2 | Laravel 13.x, PHP 8.5 | Upgrade Track (Phases 29–36) |
| **Controller Layer** | 40 controllers, 64 fat methods (>15 lines), procedural code | Thin Transport Controllers (<=15 lines), FormRequests, ViewModels | Quality (Phases 16, 50, 77) |
| **Model Layer** | 23 models `$guarded = []`, 35 missing `$casts`, 15 broken `hasOne` inversions | `$fillable` + casts, strict `belongsTo`, rich domain models | Models & Data (Phases 15, 27) |
| **Database Schema** | 38 tables, 0 foreign keys, 12 non-decimal monetary columns | Full Foreign Key Constraints, `decimal(12,2)`, Inventory Ledger | Schema (Phases 23–28, 62) |
| **Security & Auth** | Spatie RBAC bypassed, 0 Policies, 12 Stored XSS templates | Spatie route enforcement, Ownership Policies, HTMLPurifier, Throttling | Security (Phases 15–22) |
| **External Gateways** | Direct cURL, hardcoded external domains, disabled TLS validation | Hexagonal Ports & Adapters, Async Queues, Idempotent Webhooks | Integrations (Phases 38, 57, 67–73) |
| **Testing & CI/CD** | 0 custom tests (only default Laravel stubs) | 80%+ test coverage (Pest/PHPUnit), Architecture Fitness Tests | Testing (Phases 09–14, 48) |
| **Frontend & Views** | 15 DB queries in Blade, 811 inline scripts, 98 `compact()` calls | 0 queries in Blade, compiled Vite JS modules, ViewModels | Modernization (Phases 60, 77–78) |

---

## 2. Master Risk Prioritization Matrix (P0 to P3)

```
┌────────────────────────────────────────────────────────────────────────┐
│ 🔴 P0: CRITICAL / BLOCKERS (Must fix in Baseline & Security Hardening) │
├────────────────────────────────────────────────────────────────────────┤
│ 1. Unprotected Utility Routes (/cc, /controller)                       │
│ 2. Spatie RBAC Bypass (0 admin routes enforce role/permission)         │
│ 3. Mass Assignment Vulnerability (23 models with $guarded = [])        │
│ 4. Zero Authorization Policies (IDOR risk on invoices and profiles)    │
│ 5. Insecure Financial Data Types (12 columns integer/float/string)     │
│ 6. Zero Foreign Key Constraints across 16 relational tables            │
│ 7. Non-Atomic Checkout & Order Mutations without DB Transactions       │
├────────────────────────────────────────────────────────────────────────┤
│ 🔴 P1: HIGH RISKS (Business Logic, Security & Upgrade Blockers)        │
├────────────────────────────────────────────────────────────────────────┤
│ 8. Stored XSS in 12 Blade Templates ({!! !!})                          │
│ 9. Insecure File Uploads (path traversal & unvalidated MIME)           │
│ 10. SMS cURL with Disabled SSL Verification (MITM risk)                │
│ 11. Hardcoded External Callback Domains (websolutionit.com in bKash)   │
│ 12. Bulk Courier Loop Bug (only 1st order dispatched)                  │
│ 13. 15 Broken / Inverted hasOne Relationships                          │
│ 14. Zero Automated Tests (0 custom tests in codebase)                  │
│ 15. Abandoned / Deprecated Packages (laravelshoppingcart, laravel8)   │
├────────────────────────────────────────────────────────────────────────┤
│ 🟡 P2: MEDIUM RISKS (Performance, Maintainability & Architecture)      │
├────────────────────────────────────────────────────────────────────────┤
│ 16. 64 Fat Controller Methods exceeding 15 lines                       │
│ 17. 15 Direct Database Queries executed inside Blade Templates         │
│ 18. 811 Inline <script> Tags preventing strict CSP deployment          │
│ 19. Missing Rate Limiting on Login, OTP, and Checkout endpoints        │
│ 20. Synchronous External API Execution (0 Queue Jobs in codebase)      │
│ 21. Legacy File Session Cart Storage                                   │
│ 22. 5 Database Schema Typo Columns (meta_decription, serderid, etc.)   │
├────────────────────────────────────────────────────────────────────────┤
│ 🟢 P3: LOW RISKS (Code Hygiene & Minor Maintenance)                    │
├────────────────────────────────────────────────────────────────────────┤
│ 23. Debug dd() statements left in production controllers               │
│ 24. Dead Middleware (CheckReffer is a no-op)                           │
│ 25. 98 Untyped compact() Calls across 29 controllers                   │
└────────────────────────────────────────────────────────────────────────┘
```

---

## 3. Discovery Area Sign-off & Roadmap Execution Plan

With Phase 08 complete, the **Discovery Track (Phases 01–08)** is formally signed off. The subsequent roadmap phases execute according to the master plan:

- **Track 2: Testing & Characterization (Phases 09–14)**: Build automated test harness for Auth, Catalog, Cart, Order, and Admin features before code modification.
- **Track 3: Security Hardening (Phases 15–22)**: Resolve all P0/P1 security vulnerabilities (Mass assignment, FormRequests, Policies, File uploads, CSP).
- **Track 4: Database Integrity (Phases 23–28)**: Fix schema types (`decimal`), FK constraints, and typos via safe expand/contract migrations.
- **Track 5: Framework Upgrade (Phases 29–36)**: Upgrade iteratively from Laravel 9 to Laravel 13 and PHP 8.5.
- **Track 6: Infrastructure & Tooling (Phases 37–42)**: Implement Redis caching, Database Queues, and Transactional Outbox.
- **Track 7: Module Architecture Extraction (Phases 43–76)**: Extract 6 modular boundaries (Identity, Catalog, Order, Payment, Shipping, Shared) with Hexagonal Ports & Adapters.
- **Track 8: Frontend & Performance Modernization (Phases 77–80)**: Implement ViewModels, Vite bundling, Redis caching, and final production certification.

---

## 4. Definition of Done Checklist

- [x] Synthesis of all audit findings from Phases 01 to 07
- [x] Complete Architectural Gap Analysis table established
- [x] Master Risk Prioritization Matrix (P0 to P3) fully documented
- [x] Discovery Track formally signed off
- [x] Testing Track (Phases 09–14) ready to commence

