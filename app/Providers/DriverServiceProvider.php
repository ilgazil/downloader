<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

use App\Services\Driver\DriverService;
use App\Services\Driver\Drivers\UpToBoxDriver;

class DriverServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(DriverService::class, function () {
            $driverService = new DriverService();

            $driverService->register(new UpToBoxDriver());

            return $driverService;
        });
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
