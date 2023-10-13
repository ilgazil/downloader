<?php

namespace App\Providers;

use Ilgazil\LibDownload\Driver\Drivers\UnFichier\UnFichierDriver;
use Ilgazil\LibDownload\Driver\DriverService;
use Illuminate\Support\ServiceProvider;

class DriverServiceProvider extends ServiceProvider
{
    public function boot()
    {}

    public function register(): void
    {
        $this->app->singleton(DriverService::class, function () {
            $service = new DriverService();

            $service->register(new UnFichierDriver());

            return $service;
        });
    }
}
