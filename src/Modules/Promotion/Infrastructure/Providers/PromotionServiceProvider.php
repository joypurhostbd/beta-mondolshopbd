<?php

namespace Modules\Promotion\Infrastructure\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\Promotion\Application\Actions\CreateCampaignAction;
use Modules\Promotion\Application\Actions\GetActiveBannersAction;
use Modules\Promotion\Application\Actions\SubmitReviewAction;
use Modules\Promotion\Application\Services\PromotionService;
use Modules\Promotion\Domain\Contracts\CampaignRepositoryInterface;
use Modules\Promotion\Domain\Contracts\ReviewRepositoryInterface;
use Modules\Promotion\Infrastructure\Repositories\EloquentCampaignRepository;
use Modules\Promotion\Infrastructure\Repositories\EloquentReviewRepository;
use Shared\Domain\Contracts\Modules\PromotionModuleInterface;

class PromotionServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(CampaignRepositoryInterface::class, EloquentCampaignRepository::class);
        $this->app->singleton(ReviewRepositoryInterface::class, EloquentReviewRepository::class);

        $this->app->singleton(CreateCampaignAction::class);
        $this->app->singleton(SubmitReviewAction::class);
        $this->app->singleton(GetActiveBannersAction::class);

        $this->app->singleton(PromotionModuleInterface::class, PromotionService::class);
        $this->app->singleton(PromotionService::class);
    }

    public function boot(): void
    {
        //
    }
}