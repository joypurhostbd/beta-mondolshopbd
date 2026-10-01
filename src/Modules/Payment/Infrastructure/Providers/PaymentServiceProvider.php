<?php

namespace Modules\Payment\Infrastructure\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\Payment\Application\Services\PaymentGatewayManager;
use Modules\Payment\Application\Services\PaymentService;
use Modules\Payment\Domain\Contracts\PaymentRepositoryInterface;
use Modules\Payment\Infrastructure\Gateways\BkashPaymentGateway;
use Modules\Payment\Infrastructure\Gateways\CodPaymentGateway;
use Modules\Payment\Infrastructure\Gateways\MockPaymentGateway;
use Modules\Payment\Infrastructure\Gateways\ShurjoPayPaymentGateway;
use Modules\Payment\Infrastructure\Repositories\EloquentPaymentRepository;
use Shared\Domain\Contracts\Modules\PaymentModuleInterface;
use Shared\Domain\Enums\PaymentMethodEnum;

class PaymentServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(PaymentRepositoryInterface::class, EloquentPaymentRepository::class);

        $this->app->singleton(PaymentGatewayManager::class, function () {
            $manager = new PaymentGatewayManager();
            $manager->register(new CodPaymentGateway());
            $manager->register(new ShurjoPayPaymentGateway());
            $manager->register(new BkashPaymentGateway());
            $manager->register(new MockPaymentGateway(PaymentMethodEnum::NAGAD));
            $manager->register(new MockPaymentGateway(PaymentMethodEnum::ROCKET));
            return $manager;
        });

        $this->app->singleton(PaymentModuleInterface::class, PaymentService::class);
        $this->app->singleton(PaymentService::class);
    }

    public function boot(): void
    {
        //
    }
}