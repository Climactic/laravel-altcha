<?php

namespace Climactic\Altcha\Altcha;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use Climactic\Altcha\Altcha\Commands\AltchaCommand;

class AltchaServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package
            ->name('laravel-altcha')
            ->hasConfigFile()
            ->hasViews()
            ->hasMigration('create_laravel_altcha_table')
            ->hasCommand(AltchaCommand::class);
    }
}
