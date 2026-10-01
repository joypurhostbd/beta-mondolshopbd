<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Catalog\Infrastructure\Providers\CatalogServiceProvider;
use Modules\Customer\Infrastructure\Providers\CustomerServiceProvider;
use Modules\Inventory\Infrastructure\Providers\InventoryServiceProvider;
use Modules\Order\Infrastructure\Providers\OrderServiceProvider;
use Modules\Payment\Infrastructure\Providers\PaymentServiceProvider;
use Modules\Promotion\Infrastructure\Providers\PromotionServiceProvider;
use Modules\Setting\Infrastructure\Providers\SettingServiceProvider;
use Modules\Shipping\Infrastructure\Providers\ShippingServiceProvider;
use Shared\Domain\Contracts\Modules\CatalogModuleInterface;
use Shared\Domain\Contracts\Modules\CustomerModuleInterface;
use Shared\Domain\Contracts\Modules\InventoryModuleInterface;
use Shared\Domain\Contracts\Modules\OrderModuleInterface;
use Shared\Domain\Contracts\Modules\PaymentModuleInterface;
use Shared\Domain\Contracts\Modules\PromotionModuleInterface;
use Shared\Domain\Contracts\Modules\SettingModuleInterface;
use Shared\Domain\Contracts\Modules\ShippingModuleInterface;
use Shared\Domain\Enums\OrderStatusEnum;
use Shared\Domain\Enums\PaymentMethodEnum;
use Shared\Domain\Enums\PaymentStatusEnum;
use Shared\Domain\ValueObjects\Money;
use Tests\TestCase;

class ProductionReadinessSignOffTest extends TestCase
{
    use RefreshDatabase;

    public function test_all_eight_domain_service_providers_registered_and_loaded(): void
    {
        $providers = [
            CatalogServiceProvider::class,
            CustomerServiceProvider::class,
            OrderServiceProvider::class,
            InventoryServiceProvider::class,
            PaymentServiceProvider::class,
            ShippingServiceProvider::class,
            PromotionServiceProvider::class,
            SettingServiceProvider::class,
        ];

        $loadedProviders = $this->app->getLoadedProviders();

        foreach ($providers as $provider) {
            $this->assertArrayHasKey(
                $provider,
                $loadedProviders,
                "Expected provider {$provider} to be loaded in application container."
            );
        }
    }

    public function test_all_domain_module_contracts_resolvable_from_container(): void
    {
        $contracts = [
            CatalogModuleInterface::class,
            CustomerModuleInterface::class,
            OrderModuleInterface::class,
            InventoryModuleInterface::class,
            PaymentModuleInterface::class,
            ShippingModuleInterface::class,
            PromotionModuleInterface::class,
            SettingModuleInterface::class,
        ];

        foreach ($contracts as $contract) {
            $instance = $this->app->make($contract);
            $this->assertInstanceOf(
                $contract,
                $instance,
                "Container failed to resolve contract {$contract}."
            );
        }
    }

    public function test_health_monitoring_endpoints_are_operational(): void
    {
        $liveness = $this->get('/health/live');
        $liveness->assertStatus(200)
            ->assertJson(['status' => 'ok']);

        $readiness = $this->get('/health/ready');
        $readiness->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'timestamp',
                'checks' => ['database', 'cache', 'storage'],
            ]);
    }

    public function test_critical_storefront_routes_respond(): void
    {
        $home = $this->get('/');
        $home->assertStatus(200);

        $login = $this->get(route('customer.login'));
        $login->assertStatus(200);

        $register = $this->get(route('customer.register'));
        $register->assertStatus(200);
    }

    public function test_domain_invariants_and_monetary_precision_rules(): void
    {
        // 1. Money VO exact decimal precision
        $m1 = Money::from(1500.50);
        $m2 = Money::from(499.50);
        $sum = $m1->add($m2);
        $this->assertEquals(2000.00, $sum->getAmount());
        $this->assertEquals('৳2,000.00', $sum->format());

        // 2. Domain Enums
        $this->assertEquals('Pending', OrderStatusEnum::PENDING->label());
        $this->assertEquals('Completed', OrderStatusEnum::COMPLETED->label());
        $this->assertEquals('Paid', PaymentStatusEnum::PAID->label());
        $this->assertEquals('bKash', PaymentMethodEnum::BKASH->label());
    }
}