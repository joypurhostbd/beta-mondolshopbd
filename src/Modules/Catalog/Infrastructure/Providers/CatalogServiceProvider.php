<?php

namespace Modules\Catalog\Infrastructure\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\Catalog\Application\Services\CatalogService;
use Modules\Catalog\Domain\Contracts\AttributeRepositoryInterface;
use Modules\Catalog\Domain\Contracts\CategoryRepositoryInterface;
use Modules\Catalog\Domain\Contracts\ProductRepositoryInterface;
use Modules\Catalog\Infrastructure\Repositories\EloquentAttributeRepository;
use Modules\Catalog\Infrastructure\Repositories\EloquentCategoryRepository;
use Modules\Catalog\Infrastructure\Repositories\EloquentProductRepository;
use Shared\Domain\Contracts\Modules\CatalogModuleInterface;

class CatalogServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(
            ProductRepositoryInterface::class,
            EloquentProductRepository::class
        );

        $this->app->bind(
            CategoryRepositoryInterface::class,
            EloquentCategoryRepository::class
        );

        $this->app->bind(
            AttributeRepositoryInterface::class,
            EloquentAttributeRepository::class
        );

        $this->app->singleton(
            CatalogModuleInterface::class,
            CatalogService::class
        );
    }

    public function boot(): void
    {
        // Boot configuration, routes, or events if needed
    }
}