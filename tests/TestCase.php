<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

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

    protected function tearDown(): void
    {
        while (ob_get_level() > 1) {
            @ob_end_clean();
        }

        parent::tearDown();
    }
}
