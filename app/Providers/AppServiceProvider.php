<?php

namespace App\Providers;

use App\Models\Asset;
use App\Models\AssetCategory;
use App\Observers\AssetCategoryObserver;
use App\Observers\AssetObserver;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Asset::observe(AssetObserver::class);
        AssetCategory::observe(AssetCategoryObserver::class);

        Paginator::useBootstrapFive();

        // Sentry: disable di environment local/testing
        if ($this->app->environment('local', 'testing')) {
            config()->set('sentry.dsn', null);
        }
    }
}
