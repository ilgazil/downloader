<?php

namespace App\Providers;

use App\Services\Driver\Drivers\UnFichierDriver;
use App\Services\Driver\Drivers\UpToBoxDriver;
use App\Services\Driver\DriverService;
use Illuminate\Support\ServiceProvider;

class DriverServiceProvider extends ServiceProvider
{
    public function boot()
    {}

    public function register()
    {
        $this->app->singleton(DriverService::class, function () {
            $service = new DriverService();

            $service->register(new UpToBoxDriver());
            $service->register(new UnFichierDriver());

            return $service;
        });
    }
}
