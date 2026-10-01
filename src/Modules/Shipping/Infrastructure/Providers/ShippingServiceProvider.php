<?php

namespace Modules\Shipping\Infrastructure\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\Shipping\Application\Services\CourierGatewayManager;
use Modules\Shipping\Application\Services\FraudCheckService;
use Modules\Shipping\Application\Services\ShippingService;
use Modules\Shipping\Infrastructure\Fraud\HoorinFraudChecker;
use Modules\Shipping\Infrastructure\Fraud\InternalFraudChecker;
use Modules\Shipping\Infrastructure\Gateways\MockCourierGateway;
use Modules\Shipping\Infrastructure\Gateways\PathaoCourierGateway;
use Modules\Shipping\Infrastructure\Gateways\SteadfastCourierGateway;
use Shared\Domain\Contracts\Modules\ShippingModuleInterface;

class ShippingServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(CourierGatewayManager::class, function () {
            $manager = new CourierGatewayManager();
            $manager->register(new SteadfastCourierGateway());
            $manager->register(new PathaoCourierGateway());
            $manager->register(new MockCourierGateway('redx'));
            $manager->register(new MockCourierGateway('paperfly'));
            $manager->register(new MockCourierGateway('mock'));
            return $manager;
        });

        $this->app->singleton(FraudCheckService::class, function () {
            $service = new FraudCheckService();
            $service->addChecker(new InternalFraudChecker());
            $service->addChecker(new HoorinFraudChecker());
            return $service;
        });

        $this->app->singleton(ShippingModuleInterface::class, ShippingService::class);
        $this->app->singleton(ShippingService::class);
    }

    public function boot(): void
    {
        //
    }
}