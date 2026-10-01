# Phase 43 — Create Shared Directory Structure

## Objective
Establish the foundational modular monolith architecture by creating `src/Shared/` and `src/Modules/` directories, registering PSR-4 autoload mappings (`Shared\\` and `Modules\\`) in `composer.json`, and providing base Domain Contracts (`EntityInterface`, `AggregateRootInterface`, `ValueObjectInterface`, `DomainEventInterface`, `RepositoryInterface`), an abstract `DataTransferObject`, and an immutable `Money` Value Object in strict adherence to Rule 01, Rule 02, and Rule 05 of `DEVELOPMENT-RULES.md`.

## Why This Phase Exists
A clean Modular Monolith architecture requires a shared foundation for cross-cutting domain abstractions (Entities, Value Objects, Domain Events, Repositories, DTOs) that individual domain modules can depend upon without creating tight coupling or circular dependencies.

---

## 1. Implemented Architecture Components

### A. Autoloading Configuration (`composer.json`)
```json
"autoload": {
    "psr-4": {
        "App\\": "app/",
        "Shared\\": "src/Shared/",
        "Modules\\": "src/Modules/",
        "Database\\Factories\\": "database/factories/",
        "Database\\Seeders\\": "database/seeders/"
    }
}
```

### B. Core Domain Contracts (`src/Shared/Domain/Contracts/`)
1. **`EntityInterface.php`**: `getId(): int|string|null`
2. **`AggregateRootInterface.php`**: `releaseEvents(): array`
3. **`ValueObjectInterface.php`**: `equals(ValueObjectInterface $other): bool`
4. **`DomainEventInterface.php`**: `getEventName()`, `toPayload()`, `getOccurredAt()`
5. **`RepositoryInterface.php`**: `findById()`, `save()`, `deleteById()`

### C. Financial Calculations Value Object (`src/Shared/Domain/ValueObjects/Money.php`)
- Immutable floating-point-safe money representation.
- Provides `add()`, `subtract()`, `multiply()`, `equals()`, and `format()` (`৳150.00`).
- Strictly rejects negative values or currency mismatches (Rule 05 & Rule 10).

### D. Base DTO (`src/Shared/Application/DTO/DataTransferObject.php`)
- `toArray()` and `fromArray()` methods for clean inter-layer data transfer (Rule 06).

---

## 2. Verification & Test Results

- **Command**: `php artisan test`
- **Total Tests**: 47 Tests (47 Passed, 0 Failed, 0 Skipped)
- **Suite**: `Tests\Unit\SharedDomainContractsTest`
  - `test_money_value_object_initialization_and_formatting` ✅
  - `test_money_addition_and_subtraction` ✅
  - `test_money_equality` ✅
  - `test_negative_money_throws_exception` ✅

---

## 3. Definition of Done Checklist

- [x] `src/Shared/` and `src/Modules/` directory trees created
- [x] PSR-4 autoloading mapped in `composer.json` and dumped
- [x] Base domain contracts (`EntityInterface`, `ValueObjectInterface`, etc.) created
- [x] `Money` Value Object and `DataTransferObject` implemented with unit tests
- [x] All 47 tests verified passing (100% success)
- [x] Ready for Phase 44 (Create Domain Enums)

