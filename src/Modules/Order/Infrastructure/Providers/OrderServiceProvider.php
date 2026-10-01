<?php

namespace Modules\Order\Infrastructure\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\Order\Application\Services\OrderService;
use Modules\Order\Domain\Contracts\CartRepositoryInterface;
use Modules\Order\Domain\Contracts\OrderRepositoryInterface;
use Modules\Order\Infrastructure\Repositories\EloquentOrderRepository;
use Modules\Order\Infrastructure\Repositories\RedisCartRepository;
use Shared\Domain\Contracts\Modules\OrderModuleInterface;

class OrderServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(
            CartRepositoryInterface::class,
            RedisCartRepository::class
        );

        $this->app->singleton(
            OrderRepositoryInterface::class,
            EloquentOrderRepository::class
        );

        $this->app->singleton(
            OrderModuleInterface::class,
            OrderService::class
        );
    }

    public function boot(): void
    {
        // Event listeners or triggers if needed
    }
}