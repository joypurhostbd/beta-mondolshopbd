# Phase 49 — Characterize Product Module Current Behavior

## Objective
Establish full characterization coverage for existing Product and Catalog features (`ProductModuleCharacterizationTest`) covering admin product listing, filtering, AJAX subcategory/childcategory lookups, product create with relations (brand, colors, sizes), product edit, bulk price/stock updates, and status toggles before modular extraction in strict compliance with Rule 20 (Characterize First) of `DEVELOPMENT-RULES.md`.

## Why This Phase Exists
Before extracting `src/Modules/Catalog/`, all existing legacy behavior of the Product module must be locked under comprehensive tests. This guarantees zero feature regressions during extraction, action migration, repository introduction, and service provider registration.

---

## 1. Characterized Product Capabilities

1. **Admin Product Index & Search**: Keyword filtering on product names (`GET /admin/products/manage?keyword=...`).
2. **AJAX Dynamic Hierarchy Lookup**:
   - `GET /ajax-product-subcategory?category_id=...`
   - `GET /ajax-product-childcategory?subcategory_id=...`
3. **Product Creation Lookups**: Loading parent categories, brands, active colors, and sizes (`GET /admin/products/create`).
4. **Product Edit & Relation Loading**: Loading existing product along with images, sizes, and colors (`GET /admin/products/{id}/edit`).
5. **Bulk Price & Stock Management**: Updating old price, new price, and available stock across multiple products simultaneously (`POST /admin/products/price-update`).
6. **Product Status Toggling**: Activating and deactivating product visibility (`POST /admin/products/active`, `POST /admin/products/inactive`).

---

## 2. Test Suite & Verification Results

- **Command**: `php artisan test`
- **Total Tests**: 75 Tests (75 Passed, 0 Failed, 0 Skipped)
- **Suite**: `Tests\Feature\ProductModuleCharacterizationTest`
  - `admin can view products list and filter` ✅
  - `admin can get subcategories and childcategories via ajax` ✅
  - `admin can view create product page with lookups` ✅
  - `admin can view edit product page` ✅
  - `admin can bulk update product prices and stock` ✅
  - `admin can toggle product status` ✅
- **Pass Rate**: 100.0%

---

## 3. Definition of Done Checklist

- [x] Product module behavior fully characterized in `tests/Feature/ProductModuleCharacterizationTest.php`
- [x] `Brand` model fillable attributes updated to include `name_bn`
- [x] All 75 tests verified passing (100% success)
- [x] Ready for Phase 50 (Extract Product Module into `src/Modules/Catalog/`)

