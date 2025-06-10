<?php

namespace webdophp\ProSystemsIntegration\Providers;

use Illuminate\Support\ServiceProvider;
use webdophp\ProSystemsIntegration\Services\ProSystemsService;

class ProSystemsIntegrationServiceProvider extends ServiceProvider
{

    /**
     * Register services.
     */
    public function register(): void
    {

        $this->mergeConfigFrom(__DIR__.'/../../config/pro-systems-integration.php', 'pro-systems-integration');

        $this->app->singleton(ProSystemsService::class, function () {
            return new ProSystemsService();
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        $this->publishes([
            __DIR__.'/../../config/pro-systems-integration.php' => config_path('pro-systems-integration.php'),
        ], 'pro-systems-integration');

        $this->publishes([
            __DIR__.'/../../database/migrations' => database_path('migrations'),
        ], 'pro-systems-integration');

        $this->publishes([
            __DIR__.'/../../resources/views/emails/pro-systems' => resource_path('views/vendor/pro-systems-integration'),
        ], 'pro-systems-integration-views');

        $this->loadViewsFrom(__DIR__.'/../../resources/views/emails/pro-systems', 'pro-systems-integration');

        $this->loadMigrationsFrom(__DIR__.'/../../database/migrations');
    }
}