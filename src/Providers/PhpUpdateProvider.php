<?php

namespace Src\Providers;

use Illuminate\Support\ServiceProvider;
use Src\Interface\Version;
use Src\Services\VersionUpdate;

class PhpUpdateProvider extends ServiceProvider
{
    public function boot()
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/lifter.php', 'lifter'
        );

        $this->app->bind(Version::class, VersionUpdate::class);
    }

    public function register()
    {
        $this->publishes([
            __DIR__.'/../config/lifter.php' => config_path('lifter.php'),
        ], 'lifter-config');
    }
}
