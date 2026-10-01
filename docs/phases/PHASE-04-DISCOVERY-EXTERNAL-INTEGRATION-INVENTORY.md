# Phase 04 — External Integration Inventory

## Objective
Comprehensive audit and architectural cataloging of all external third-party integrations (Payment Gateways, SMS Providers, Courier Services, Fraud Detection Services, Analytics Pixels), identifying security vulnerabilities, synchronous blocking operations, hardcoded endpoints, and establishing the Ports & Adapters target architecture for MondolShopBD.

## Why This Phase Exists
External APIs are volatile and prone to downtime, breaking changes, network latency, and security vulnerabilities. Direct coupling of controllers/models to external HTTP endpoints violates SOLID principles and makes automated testing impossible without mocking. This audit establishes the baseline for extracting clean Contracts, Ports, and Adapters in Phases 57, 67–70, and 72–73.

---

## 1. External Integrations Inventory & Critical Audit

| Integration Category | Service / Provider | Codebase Location | Invocation Method | Identified Risks & Anti-Patterns | Target Port (Module) |
|---|---|---|---|---|---|
| **Payment Gateway** | bKash Checkout (Tokenized API v1.2) | `app/Http/Controllers/Frontend/BkashController.php` | Synchronous `curl_init` | Hardcoded callback URL domain (`https://ecom.websolutionit.com/`), no webhook idempotency, no signature verification. | `PaymentGatewayInterface` (Payment Module) |
| **Payment Gateway** | ShurjoPay (v2) | `app/Http/Controllers/Frontend/ShurjopayControllers.php` | Direct package class instantiation (`new ShurjopayController()`) | Pinned to abandoned `dev-master` branch in composer.json, dummy customer email (`customer@gmail.com`) and postal code (`1212`) hardcoded. | `PaymentGatewayInterface` (Payment Module) |
| **SMS Gateway** | Bulk SMS (Brand / Non-Brand Gateway) | `CustomerController` (`order_save`, `resendotp`, `forgot_resend`), `OrderController` (`order_process`) | Synchronous `curl_init` | `CURLOPT_SSL_VERIFYPEER, false` (critical SSL check bypass), blocks HTTP thread during checkout, typo column `serderid` referenced in payload. | `SmsGatewayInterface` (Identity / Shared Module) |
| **Courier Service** | Pathao Aladdin API | `OrderController::order_pathao()`, `CustomerController::order_save()` | Synchronous `Http::post` | Synchronous execution in controller, missing retry backoff, lack of error handling on rate limits. | `CourierServiceInterface` (Shipping Module) |
| **Courier Service** | Steadfast Courier API | `OrderController::bulk_courier()` | Synchronous `GuzzleHttp\Client` | Hardcoded fallback credentials (`01750578495`, `InboxHat`), logic bug where loop returns on 1st iteration (broken bulk dispatch). | `CourierServiceInterface` (Shipping Module) |
| **Fraud Detection** | Hoorin Fraud Check API (`dash.hoorin.com`) | `app/Models/Order.php:L53-L175` (`Order::fraud_check()`) | Synchronous `Http::get` inside Eloquent Model | Eloquent Model executes HTTP network call during lifecycle hooks, blocking database transactions. | `FraudCheckInterface` (Shipping Module) |
| **Analytics & Pixel** | Meta / Facebook Pixel | `PixelsController`, Blade Layouts | Inline Blade `<script>` tags | Unsanitized client injection, lacks Content Security Policy (CSP) nonces. | `AnalyticsTracker` (Shared Kernel) |
| **Tag Manager** | Google Tag Manager (GTM) | `TagManagerController`, Blade Layouts | Inline Blade `<script>` tags | Inline script evaluation without CSP nonces. | `AnalyticsTracker` (Shared Kernel) |

---

## 2. Deep Dive: Vulnerability & Implementation Analysis

### 2.1 bKash Checkout Integration (`BkashController.php`)
- **Credentials Storage**: Dynamic lookup from `payment_gateways` table (`type='bkash'`).
- **Hardcoded Domain Hazard**:
  ```php
  'callbackURL' => 'https://ecom.websolutionit.com/bkash/checkout-url/callback?orderId='.$orderId,
  ```
- **Idempotency Deficit**: `callback()` executes without checking if the transaction was already processed, exposing the merchant to duplicate capture callbacks or replay attacks.

### 2.2 SMS Provider Integration (`CustomerController.php` & `OrderController.php`)
- **SSL Insecurity**:
  ```php
  curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // ⚠️ Disables TLS certificate validation
  ```
- **Synchronous Execution**: Order placement synchronously waits for the SMS gateway HTTP response, adding 1–3 seconds of latency to user checkout.
- **Typo Reference**: Data mapped as `"senderid" => "$sms_gateway->serderid"`.

### 2.3 Bulk Courier Logic Flaw (`OrderController::bulk_courier`)
- **Early Return Bug**:
  ```php
  foreach ($orders_id as $order_id) {
      // ... creates 1 order ...
      return response()->json(['status' => $status, 'message' => $message]); // ⚠️ Exits loop on first order!
  }
  ```
- **Fallback Data**: Hardcoded `'01750578495'` phone and `'InboxHat'` store name if recipient shipping record is missing.

### 2.4 Model-Coupled Fraud API (`Order::fraud_check`)
- Direct HTTP call inside `app/Models/Order.php`:
  ```php
  $individual_response = Http::get('https://dash.hoorin.com/api/courier/api', [
      'apiKey' => $fraud?->token,
      'searchTerm' => $phone
  ]);
  ```

---

## 3. Hexagonal Ports & Adapters Architecture Blueprint

```
┌────────────────────────────────────────────────────────────────────────┐
│                        CORE APPLICATION DOMAIN                         │
│                                                                        │
│   Contracts / Ports:                                                   │
│   ├── PaymentGatewayInterface (createIntent, verify, capture, refund)  │
│   ├── SmsGatewayInterface (sendOtp, sendOrderNotification)             │
│   ├── CourierServiceInterface (createConsignment, track, getCities)    │
│   └── FraudCheckInterface (checkRiskScore)                             │
└───────────────────────────────────┬────────────────────────────────────┘
                                    │ implements
┌───────────────────────────────────┴────────────────────────────────────┐
│                    INFRASTRUCTURE ADAPTER LAYER                        │
│                                                                        │
│   Payment Adapters:         SMS Adapters:          Courier Adapters:   │
│   ├── BkashAdapter          ├── BulkSmsAdapter     ├── PathaoAdapter   │
│   ├── ShurjoPayAdapter      └── (Queued Worker)    ├── SteadfastAdapter│
│   └── CodAdapter                                   └── HoorinAdapter   │
└────────────────────────────────────────────────────────────────────────┘
```

---

## 4. Remediation Sequencing

- **Phase 38 (Queue Architecture)**: Move SMS and Courier dispatches to asynchronous background workers (`ProcessOrderFulfillment`, `SendSmsNotification`).
- **Phase 47 (Shared Contracts)**: Define standard `PaymentGatewayInterface`, `SmsGatewayInterface`, `CourierServiceInterface`, `FraudCheckInterface`.
- **Phase 57 (SMS Service Port & Adapter)**: Refactor SMS calling with queue dispatch and strict TLS verification (`CURLOPT_SSL_VERIFYPEER = true` or `Http::asJson()`).
- **Phase 67–70 (Payment Gateway Port & Adapters)**: Implement clean `BkashAdapter`, `ShurjoPayAdapter`, and `WebhookSecurity` with atomic idempotency checks.
- **Phase 72–73 (Courier Port & Fraud Check Service)**: Extract `PathaoAdapter`, `SteadfastAdapter`, and `FraudCheckService` from controller/model code.

---

## 5. Definition of Done Checklist

- [x] Complete inventory of all external integrations (bKash, ShurjoPay, SMS, Pathao, Steadfast, Hoorin, Pixels, GTM)
- [x] Audit of security risks (`CURLOPT_SSL_VERIFYPEER = false`, hardcoded callback URLs)
- [x] Detection of logic bugs (bulk courier early loop termination, model HTTP coupling)
- [x] Ports & Adapters architecture design documented
- [x] Remediation sequencing mapped to Phases 38, 47, 57, 67–73
- [x] Ready for Phase 05 (Business Flow Mapping)

