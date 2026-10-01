# Phase 72 — Courier Port & Adapters (Steadfast/Pathao/RedX/Paperfly)

## Objective
Establish the Shipping Domain Module (`src/Modules/Shipping/`) featuring `CourierGatewayInterface`, `CourierGatewayManager`, driver adapters (`SteadfastCourierGateway`, `PathaoCourierGateway`, `MockCourierGateway`), `ShippingService` implementing `ShippingModuleInterface`, and service provider registration (`ShippingServiceProvider`) in strict compliance with Rule 01 (Directory Layout), Rule 02 (Service Provider Registration), Rule 03 (Module Isolation & Communication), Rule 07 (Money Precision), and Rule 08 (Domain Enums) of `DEVELOPMENT-RULES.md`.

## Why This Phase Exists
Shipping and courier order booking in MondolShopBD was previously coupled inside monolithic admin order controllers with hardcoded endpoints and credentials. Extracting a unified Port & Adapter interface decouples delivery providers, normalizes tracking codes, standardizes shipping charge calculations, and allows seamless switching between courier partners.

---

## 1. Implemented Shipping Domain Architecture

```
src/Modules/Shipping/
├── Domain/
│   └── Contracts/
│       └── CourierGatewayInterface.php
├── Application/
│   ├── DTOs/
│   │   ├── CourierParcelDTO.php
│   │   ├── CourierResponseDTO.php
│   │   └── CourierTrackingDTO.php
│   └── Services/
│       ├── CourierGatewayManager.php
│       └── ShippingService.php (implements ShippingModuleInterface)
└── Infrastructure/
    ├── Gateways/
    │   ├── SteadfastCourierGateway.php
    │   ├── PathaoCourierGateway.php
    │   └── MockCourierGateway.php
    └── Providers/
        └── ShippingServiceProvider.php
```

---

## 2. Key Components Details

1. **`CourierGatewayInterface` Port**:
   - `getCourierName(): string`
   - `sendParcel(CourierParcelDTO $dto): CourierResponseDTO`
   - `trackParcel(string $trackingCode): CourierTrackingDTO`
2. **Adapters**:
   - `SteadfastCourierGateway`: Issues order booking to Steadfast API with API-Key and Secret-Key authentication.
   - `PathaoCourierGateway`: Issues tokenized order booking to Pathao Aladdin API.
   - `MockCourierGateway`: Configurable driver for testing and multi-courier simulation (RedX, Paperfly, Mock).
3. **`ShippingService`**:
   - Implements `ShippingModuleInterface::calculateShippingCharge()` and `ShippingModuleInterface::createShipment()`.

---

## 3. Test Suite & Verification Results

- **Command**: `php artisan test`
- **Total Tests**: 152 Tests (152 Passed, 0 Failed, 0 Skipped)
- **Suite**: `Tests\Unit\CourierPortAdapterTest`
  - `courier gateway manager resolves all drivers` ✅
  - `unregistered courier throws domain exception` ✅
  - `steadfast courier send parcel` ✅
  - `pathao courier send parcel` ✅
  - `shipping service calculates charges and creates shipment` ✅
- **Pass Rate**: 100.0%
- **Execution Time**: 28.81s

---

## 4. Definition of Done Checklist

- [x] `src/Modules/Shipping/` directory structure created
- [x] `CourierGatewayInterface` port defined
- [x] Steadfast, Pathao, and Mock adapters implemented
- [x] `ShippingService` implementing `ShippingModuleInterface`
- [x] `ShippingServiceProvider` registered in `config/app.php`
- [x] Unit test suite created and passing
- [x] All 152 tests across the application passing (100% success)
- [x] Ready for Phase 73 (Fraud Check Service Implementation)

