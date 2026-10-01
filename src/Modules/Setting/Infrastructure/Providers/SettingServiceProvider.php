<?php

namespace Modules\Setting\Infrastructure\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\Setting\Application\Actions\GetContactInfoAction;
use Modules\Setting\Application\Actions\GetGeneralSettingAction;
use Modules\Setting\Application\Actions\GetPageBySlugAction;
use Modules\Setting\Application\Actions\GetSocialMediaLinksAction;
use Modules\Setting\Application\Actions\UpdateGeneralSettingAction;
use Modules\Setting\Application\Services\SettingService;
use Modules\Setting\Domain\Contracts\SettingRepositoryInterface;
use Modules\Setting\Infrastructure\Repositories\EloquentSettingRepository;
use Shared\Domain\Contracts\Modules\SettingModuleInterface;

class SettingServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(SettingRepositoryInterface::class, EloquentSettingRepository::class);
        $this->app->singleton(
            \Modules\Setting\Domain\Contracts\CodeSnippetRepositoryInterface::class,
            \Modules\Setting\Infrastructure\Repositories\EloquentCodeSnippetRepository::class
        );
        $this->app->singleton(\Modules\Setting\Application\Services\CodeSnippetService::class);

        $this->app->singleton(GetGeneralSettingAction::class);
        $this->app->singleton(UpdateGeneralSettingAction::class);
        $this->app->singleton(GetSocialMediaLinksAction::class);
        $this->app->singleton(GetContactInfoAction::class);
        $this->app->singleton(GetPageBySlugAction::class);

        $this->app->singleton(SettingModuleInterface::class, SettingService::class);
        $this->app->singleton(SettingService::class);
    }

    public function boot(): void
    {
        view()->composer('frontEnd.layouts.master', function ($view) {
            try {
                $snippetService = $this->app->make(\Modules\Setting\Application\Services\CodeSnippetService::class);
                $currentPath = request()->path();
                $userAgent = request()->userAgent();
                $isAuth = auth()->check();

                $view->with('injectedHeadSnippets', $snippetService->getRenderableSnippets('head', $currentPath, $userAgent, $isAuth));
                $view->with('injectedBodyOpenSnippets', $snippetService->getRenderableSnippets('body_open', $currentPath, $userAgent, $isAuth));
                $view->with('injectedFooterSnippets', $snippetService->getRenderableSnippets('footer', $currentPath, $userAgent, $isAuth));
            } catch (\Throwable) {
                $view->with('injectedHeadSnippets', []);
                $view->with('injectedBodyOpenSnippets', []);
                $view->with('injectedFooterSnippets', []);
            }
        });
    }
}