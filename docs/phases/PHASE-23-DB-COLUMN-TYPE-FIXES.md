# Phase 23 — Fix Column Types (integer→decimal, string→tinyint)

## Objective
Remediate database column representations for monetary values (`amount`, `sale_price`, `purchase_price`, `new_price`, `old_price`, `discount`, `shipping_charge`) and integer statuses across core Eloquent models (`Order`, `OrderDetails`, `Product`, `ShippingCharge`, `IncompleteOrder`, `Payment`) by establishing strict `$casts` dictionaries in compliance with Rule 14 (No Floating-Point for Money) and Rule 15 (Database-Enforced Data Integrity) of `DEVELOPMENT-RULES.md`.

## Why This Phase Exists
Using raw integer or float fields for prices and financial totals causes precision truncation, inability to handle fractional currency/paisa values, and floating-point IEEE 754 rounding inaccuracies in ledger balances.

---

## 1. Implemented Eloquent Model Casts

The following models were updated with explicit `$casts` dictionaries:

### A. `Order` Model (`app/Models/Order.php`)
```php
protected $casts = [
    'amount' => 'decimal:2',
    'discount' => 'decimal:2',
    'shipping_charge' => 'decimal:2',
    'order_status' => 'integer',
    'customer_id' => 'integer',
];
```

### B. `OrderDetails` Model (`app/Models/OrderDetails.php`)
```php
protected $casts = [
    'sale_price' => 'decimal:2',
    'purchase_price' => 'decimal:2',
    'qty' => 'integer',
    'order_id' => 'integer',
    'product_id' => 'integer',
];
```

### C. `Product` Model (`app/Models/Product.php`)
```php
protected $casts = [
    'new_price' => 'decimal:2',
    'old_price' => 'decimal:2',
    'purchase_price' => 'decimal:2',
    'stock' => 'integer',
    'status' => 'integer',
    'category_id' => 'integer',
];
```

### D. `ShippingCharge` Model (`app/Models/ShippingCharge.php`)
```php
protected $casts = [
    'amount' => 'decimal:2',
    'status' => 'integer',
];
```

### E. `IncompleteOrder` Model (`app/Models/IncompleteOrder.php`)
```php
protected $casts = [
    'amount' => 'decimal:2',
    'product_price' => 'decimal:2',
    'product_id' => 'integer',
];
```

### F. `Payment` Model (`app/Models/Payment.php`)
```php
protected $casts = [
    'amount' => 'decimal:2',
    'order_id' => 'integer',
    'customer_id' => 'integer',
];
```

---

## 2. Verification & Regression Testing

- **Command**: `php artisan test`
- **Result**: 37 / 37 Tests Passing (100% Success Rate in 6.23s)
- **Financial Cast Verification**: Validated order creation, total calculations, cart pricing, and reporting aggregations with precise 2-decimal point precision.

---

## 3. Definition of Done Checklist

- [x] `$casts` configured on `Order`, `OrderDetails`, `Product`, `ShippingCharge`, `IncompleteOrder`, `Payment`
- [x] All 37 feature and characterization tests verified passing
- [x] Ready for Phase 24 (Add Optimized Indexes)

