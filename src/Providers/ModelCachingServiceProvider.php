<?php

namespace Nehal\ModelCaching\Providers;

use Illuminate\Support\ServiceProvider;
use Nehal\ModelCaching\Contracts\CacheKeyGeneratorInterface;
use Nehal\ModelCaching\Cache\QueryCacheKeyGenerator;
use Nehal\ModelCaching\ModelCaching;
use Nehal\ModelCaching\Commands\ClearCacheCommand;

class ModelCachingServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../../config/model-caching.php',
            'model-caching'
        );

        $this->app->singleton(CacheKeyGeneratorInterface::class, QueryCacheKeyGenerator::class);
        $this->app->singleton(ModelCaching::class, ModelCaching::class);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../../config/model-caching.php' => config_path('model-caching.php'),
            ], 'model-caching-config');

            $this->commands([
                ClearCacheCommand::class,
            ]);
        }
    }
}
