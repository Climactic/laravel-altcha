<?php

declare(strict_types=1);

namespace Climactic\Altcha;

use Climactic\Altcha\Http\Controllers\AltchaChallengeController;
use Climactic\Altcha\Http\Middleware\VerifyAltcha;
use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Route;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class AltchaServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('laravel-altcha')
            ->hasConfigFile('altcha');
    }

    public function packageBooted(): void
    {
        $this->app->make(Router::class)->aliasMiddleware('altcha', VerifyAltcha::class);

        $this->registerChallengeRoute();

        $this->publishes([
            __DIR__.'/../resources/stubs/js/altcha-widget.tsx' => resource_path('js/components/altcha-widget.tsx'),
            __DIR__.'/../resources/stubs/js/altcha.d.ts' => resource_path('js/types/altcha.d.ts'),
        ], 'altcha-frontend');
    }

    protected function registerChallengeRoute(): void
    {
        if (! config('altcha.route.enabled', true)) {
            return;
        }

        Route::middleware(config('altcha.route.middleware', ['web']))
            ->prefix(config('altcha.route.prefix', ''))
            ->domain(config('altcha.route.domain'))
            ->group(function (): void {
                Route::get(config('altcha.route.path', 'altcha'), AltchaChallengeController::class)
                    ->name(config('altcha.route.name', 'altcha.challenge'));
            });
    }
}
