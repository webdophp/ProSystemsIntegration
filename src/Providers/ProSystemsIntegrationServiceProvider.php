<?php

namespace webdophp\ProSystemsIntegration\Providers;

use Illuminate\Routing\Router;
use Illuminate\Support\ServiceProvider;
use webdophp\ProSystemsIntegration\Services\ProSystemsService;
use webdophp\ProSystemsIntegration\Http\Middleware\CheckApiKey;

class ProSystemsIntegrationServiceProvider extends ServiceProvider
{

    /**
     * Register services.
     */
    public function register(): void
    {
        /** @var Router $router */
        $router = $this->app['router'];

        // Регистрируем middleware с псевдонимом 'pro-systems.key'
        $router->aliasMiddleware('pro-systems.key', CheckApiKey::class);

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
        if ($this->app->runningInConsole()) {
            // Регистрация artisan-команд
            $this->commands([
                \webdophp\ProSystemsIntegration\Console\Commands\ProSystemsData::class,
                \webdophp\ProSystemsIntegration\Console\Commands\ProSystemsDataInfo::class,
            ]);
        }

        // Публикация конфигов
        $this->publishes([
            __DIR__.'/../../config/pro-systems-integration.php' => config_path('pro-systems-integration.php'),
        ], 'pro-systems-integration');

        // Публикация миграций
        $this->publishes([
            __DIR__.'/../../database/migrations' => database_path('migrations'),
        ], 'pro-systems-integration');

        // Публикация вьюшек
        $this->publishes([
            __DIR__.'/../../resources/views/emails/pro-systems' => resource_path('views/vendor/pro-systems-integration'),
        ], 'pro-systems-integration-views');

        // Загрузка вьюшек
        $this->loadViewsFrom(__DIR__.'/../../resources/views/emails/pro-systems', 'pro-systems-integration');

        // Загрузка миграций
        $this->loadMigrationsFrom(__DIR__.'/../../database/migrations');

        // Загрузка API маршрутов
        $this->loadRoutesFrom(__DIR__.'/../../routes/api.php');
    }
}