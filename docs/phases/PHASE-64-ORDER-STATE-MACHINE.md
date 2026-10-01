# Phase 64 — Order State Machine (Status Transitions)

## Objective
Implement the Order State Machine (`src/Modules/Order/Domain/Services/OrderStateMachine.php`), domain events (`OrderStatusChangedEvent`), and transition actions (`ChangeOrderStatusAction`, `CancelOrderAction` with automatic inventory restoration) in strict compliance with Rule 03 (Module Isolation & Inventory release), Rule 04 (Action Pattern), Rule 05 (Domain Events), Rule 08 (Domain Enums), and Rule 09 (Domain Invariants) of `DEVELOPMENT-RULES.md`.

## Why This Phase Exists
Ad-hoc status modifications in legacy code allowed invalid state jumps (e.g. going directly from `PENDING` to `DELIVERED`, or modifying cancelled orders) and left stock un-restored when orders were cancelled. Introducing a formal state machine enforces lifecycle invariants and guarantees automatic warehouse stock release upon order cancellation or return.

---

## 1. Implemented Order State Machine Architecture (`src/Modules/Order/`)

```
src/Modules/Order/
├── Domain/
│   ├── Services/
│   │   └── OrderStateMachine.php (Enforces allowed transitions & invariant assertions)
│   └── Events/
│       └── OrderStatusChangedEvent.php (Implements DomainEventInterface)
└── Application/
    └── Actions/
        ├── ChangeOrderStatusAction.php (Validates transition, restores inventory on cancel, dispatches event)
        └── CancelOrderAction.php (Specialized cancellation action)
```

---

## 2. Key Components Details

1. **`OrderStateMachine`**:
   - Encapsulates state transition matrix:
     - `PENDING` -> `PROCESSING`, `ON_HOLD`, `CANCELLED`
     - `PROCESSING` -> `ON_HOLD`, `COMPLETED`, `CANCELLED`
     - `ON_HOLD` -> `PROCESSING`, `CANCELLED`
     - `COMPLETED` -> `DELIVERED`, `RETURNED`
     - `DELIVERED` -> `RETURNED`
     - `CANCELLED`, `RETURNED` -> Terminal states (no further transitions permitted).
   - Throws `InvalidStateTransitionException` when an illegal status change is attempted.
2. **`ChangeOrderStatusAction`**:
   - Reconstitutes order via repository, asserts transition validity with state machine.
   - If transitioning to `CANCELLED` or `RETURNED`, automatically iterates items and restores stock using `InventoryModuleInterface->releaseStock()`.
   - Persists state and dispatches `OrderStatusChangedEvent`.
3. **`CancelOrderAction`**:
   - Single-purpose cancellation action delegating to `ChangeOrderStatusAction`.

---

## 3. Test Suite & Verification Results

- **Command**: `php artisan test`
- **Total Tests**: 123 Tests (123 Passed, 0 Failed, 0 Skipped)
- **Suite**: `Tests\Unit\OrderStateMachineTest`
  - `state machine valid transitions` ✅
  - `state machine invalid transitions` ✅
  - `change order status lifecycle and events` ✅
  - `invalid state transition throws exception` ✅
- **Pass Rate**: 100.0%
- **Execution Time**: 20.40s

---

## 4. Definition of Done Checklist

- [x] `OrderStateMachine` domain service implemented with transition matrix and invariant protection
- [x] `OrderStatusChangedEvent` implemented satisfying `DomainEventInterface`
- [x] `ChangeOrderStatusAction` implemented with automatic stock restoration via `InventoryModuleInterface`
- [x] `CancelOrderAction` implemented
- [x] Full unit test suite created with 100% assertions passing
- [x] All 123 tests across the application passing (100% success)
- [x] Ready for Phase 65 (Admin POS Order Action)

