<?php

namespace Modules\Inventory\Infrastructure\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\Inventory\Application\Services\InventoryService;
use Modules\Inventory\Domain\Contracts\InventoryRepositoryInterface;
use Modules\Inventory\Infrastructure\Repositories\EloquentInventoryRepository;
use Shared\Domain\Contracts\Modules\InventoryModuleInterface;

class InventoryServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(
            InventoryRepositoryInterface::class,
            EloquentInventoryRepository::class
        );

        $this->app->singleton(
            InventoryModuleInterface::class,
            InventoryService::class
        );
    }

    public function boot(): void
    {
        // Event listeners or triggers if needed
    }
}