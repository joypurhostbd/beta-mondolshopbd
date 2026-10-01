<?php

namespace Modules\Customer\Infrastructure\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\Customer\Application\Services\CustomerService;
use Modules\Customer\Domain\Contracts\CustomerRepositoryInterface;
use Modules\Customer\Domain\Contracts\SmsServiceInterface;
use Modules\Customer\Infrastructure\Adapters\Sms\SmsGatewayManager;
use Modules\Customer\Infrastructure\Repositories\EloquentCustomerRepository;
use Shared\Domain\Contracts\Modules\CustomerModuleInterface;

class CustomerServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(
            CustomerRepositoryInterface::class,
            EloquentCustomerRepository::class
        );

        $this->app->bind(
            SmsServiceInterface::class,
            function () {
                return new SmsGatewayManager();
            }
        );

        $this->app->singleton(
            CustomerModuleInterface::class,
            CustomerService::class
        );
    }

    public function boot(): void
    {
        // Boot bindings or event listeners
    }
}