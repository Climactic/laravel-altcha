<?php

declare(strict_types=1);

namespace Climactic\Altcha\Tests;

use Climactic\Altcha\AltchaServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{
    protected function getPackageProviders($app)
    {
        return [
            AltchaServiceProvider::class,
        ];
    }

    public function getEnvironmentSetUp($app)
    {
        config()->set('app.key', 'base64:'.base64_encode(random_bytes(32)));
        config()->set('database.default', 'testing');
        config()->set('cache.default', 'array');
        config()->set('altcha.enabled', true);
        config()->set('altcha.hmac_secret', 'test-secret');
        config()->set('altcha.cost', 500);
        config()->set('altcha.expires', 300);
    }
}
