<?php

namespace Tests;

use Illuminate\Contracts\Console\Kernel;

trait CreatesApplication
{
    /**
     * Creates the application.
     *
     * @return \Illuminate\Foundation\Application
     */
    public function createApplication()
    {
        $envPath = __DIR__ . '/../.env';

        if (! file_exists($envPath)) {
            file_put_contents($envPath, '');
        }

        putenv('SETTINGS_CACHE_ENABLED=false');
        $_ENV['SETTINGS_CACHE_ENABLED'] = 'false';
        $_SERVER['SETTINGS_CACHE_ENABLED'] = 'false';

        $app = require __DIR__.'/../bootstrap/app.php';

        $app->make(Kernel::class)->bootstrap();

        return $app;
    }
}
