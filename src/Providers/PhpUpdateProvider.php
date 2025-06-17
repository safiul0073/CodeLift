<?php

namespace Src\Providers;

use Illuminate\Support\ServiceProvider;
use Src\Interface\Version;
use Src\Services\VersionUpdate;

class PhpUpdateProvider extends ServiceProvider
{
    public function boot()
    {
        $this->app->bind(Version::class, VersionUpdate::class);
    }

    public function register()
    {
        //
    }
}
