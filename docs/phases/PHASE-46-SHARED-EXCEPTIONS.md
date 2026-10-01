# Phase 46 — Create Shared Exception Classes

## Objective
Create domain-specific, context-carrying Domain Exceptions under `src/Shared/Domain/Exceptions/` (`DomainException`, `EntityNotFoundException`, `InvalidStateTransitionException`, `InsufficientStockException`, `PaymentFailedException`, `UnauthorizedModuleAccessException`, `OrderAlreadyProcessedException`) in strict adherence to Rule 09 (Domain Exceptions) of `DEVELOPMENT-RULES.md`.

## Why This Phase Exists
Throwing generic `\Exception` or `\RuntimeException` obscures the root cause of business rule violations and prevents the application layer from differentiating between domain invariants, not-found conditions, and fatal server errors. Structured domain exceptions carry rich context (IDs, state transitions, stock deficits, gateway errors) to enable precise handling and observability.

---

## 1. Implemented Domain Exception Classes

### A. Base Domain Exception (`src/Shared/Domain/Exceptions/DomainException.php`)
- Extends `\RuntimeException`.
- Encapsulates contextual metadata `$context` array via `getContext(): array`.

### B. Specialized Domain Exceptions
1. **`EntityNotFoundException.php`**: `forEntity(string $entity, int|string $id): self` (HTTP 404).
2. **`InvalidStateTransitionException.php`**: `forTransition(string $entity, string $from, string $to): self` (HTTP 422).
3. **`InsufficientStockException.php`**: `forProduct(int|string $productId, int $requested, int $available): self` (HTTP 409).
4. **`PaymentFailedException.php`**: `withReason(string $gateway, string $reason, ?string $txId): self` (HTTP 402).
5. **`UnauthorizedModuleAccessException.php`**: `forModule(string $module, ?string $user): self` (HTTP 403).
6. **`OrderAlreadyProcessedException.php`**: `forOrder(int|string $orderId, string $status): self` (HTTP 409).

---

## 2. Verification & Test Results

- **Command**: `php artisan test`
- **Total Tests**: 63 Tests (63 Passed, 0 Failed, 0 Skipped)
- **Suite**: `Tests\Unit\SharedDomainExceptionsTest`
  - `test_entity_not_found_exception_carries_context` ✅
  - `test_invalid_state_transition_exception` ✅
  - `test_insufficient_stock_exception` ✅
  - `test_payment_failed_exception` ✅
  - `test_unauthorized_and_order_already_processed_exceptions` ✅

---

## 3. Definition of Done Checklist

- [x] 7 Context-carrying Domain Exceptions implemented
- [x] Static factory constructors created with proper HTTP status codes and structured context
- [x] Unit test suite created with 100% assertions passing
- [x] All 63 tests verified passing (100% success)
- [x] Ready for Phase 47 (Define Module Contracts & Public APIs)

