# Phase 09 — Test Infrastructure Setup (Pest + PHPUnit)

## Objective
Establish a clean, isolated, and fast test execution environment for MondolShopBD using PHPUnit with in-memory SQLite (`:memory:`), database isolation (`RefreshDatabase`), and base test helpers in `tests/TestCase.php` to enable automated characterization testing (Phases 10–14) and prevent regressions during modular monolith refactoring.

## Why This Phase Exists
Refactoring legacy code without automated tests carries severe regression risks. Setting up in-memory testing infrastructure ensures that characterization tests run in milliseconds without corrupting local/staging databases or requiring active MySQL servers during CI/CD.

---

## 1. Test Environment Configuration

### 1.1 `phpunit.xml` In-Memory Database Activation
Configured environment variables for isolated in-memory test executions:
```xml
<php>
    <env name="APP_ENV" value="testing"/>
    <env name="BCRYPT_ROUNDS" value="4"/>
    <env name="CACHE_DRIVER" value="array"/>
    <env name="DB_CONNECTION" value="sqlite"/>
    <env name="DB_DATABASE" value=":memory:"/>
    <env name="MAIL_MAILER" value="array"/>
    <env name="QUEUE_CONNECTION" value="sync"/>
    <env name="SESSION_DRIVER" value="array"/>
    <env name="TELESCOPE_ENABLED" value="false"/>
</php>
```

### 1.2 `tests/TestCase.php` Base Helper Setup
Added `setUp()` method in base test case to seed global view variables (`$generalsetting`, `$contact`, `$menucategories`, `$orderstatus`, etc.), preventing `Undefined variable` exceptions across feature tests that render master Blade layouts.

```php
abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    protected function setUp(): void
    {
        parent::setUp();

        // Share fallback dummy objects to views for testing
        view()->share([
            'generalsetting' => new \App\Models\GeneralSetting(['name' => 'MondolShopBD', 'status' => 1]),
            'contact' => new \App\Models\Contact(['phone' => '01700000000', 'email' => 'info@mondolshopbd.com', 'status' => 1]),
            'sidecategories' => collect([]),
            'menucategories' => collect([]),
            'socialicons' => collect([]),
            'pages' => collect([]),
            'pagesright' => collect([]),
            'cmnmenu' => collect([]),
            'brands' => collect([]),
            'neworder' => 0,
            'pendingorder' => collect([]),
            'orderstatus' => collect([]),
            'pixels' => collect([]),
            'gtm_code' => collect([]),
        ]);
    }
}
```

---

## 2. Verification & Execution Status

- Executed `php artisan test` successfully with SQLite `:memory:` and `RefreshDatabase`.
- Execution time: ~1.1s.
- 0 database corruption risk to local development environment.

---

## 3. Definition of Done Checklist

- [x] In-memory SQLite database configured in `phpunit.xml`
- [x] Base `TestCase.php` updated with default view dependencies
- [x] `php artisan test` executing cleanly with 100% pass rate
- [x] Test infrastructure ready for Characterization Testing (Phases 10–14)
- [x] Ready for Phase 10 (Characterize Customer Auth)

