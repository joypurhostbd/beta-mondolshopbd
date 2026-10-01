<?php

namespace Shared\Infrastructure\Providers;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;
use Shared\Infrastructure\Views\Components\MoneyDisplay;
use Shared\Infrastructure\Views\Components\OrderStatusBadge;
use Shared\Infrastructure\Views\Components\PriceBadge;

class SharedViewServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Blade::component('shared-price-badge', PriceBadge::class);
        Blade::component('shared-order-status-badge', OrderStatusBadge::class);
        Blade::component('shared-money-display', MoneyDisplay::class);
    }
}