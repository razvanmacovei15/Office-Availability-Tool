<?php

namespace App\Providers;

use App\Services\CustomNotificationService;
use App\Services\FilamentNotificationManager;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(CustomNotificationService::class, function ($app) {
            return new CustomNotificationService();
        });

        $this->app->singleton(FilamentNotificationManager::class, function ($app) {
            return new FilamentNotificationManager();
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
