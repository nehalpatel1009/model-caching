<?php

namespace Nehal\ModelCaching;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use Nehal\ModelCaching\Commands\ModelCachingCommand;

class ModelCachingServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package
            ->name('model-caching')
            ->hasConfigFile()
            ->hasViews()
            ->hasMigration('create_model_caching_table')
            ->hasCommand(ModelCachingCommand::class);
    }
}
