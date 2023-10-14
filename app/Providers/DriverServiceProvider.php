<?php

namespace App\Providers;

use App\Models\Driver;
use Exception;
use Ilgazil\LibDownload\Authenticators\ApiKeyAuthenticator;
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

            $this->registerUnFichier($service);

            return $service;
        });
    }

    protected function registerUnFichier(DriverService $service): void
    {
        $driver = new UnFichierDriver();

        try {
            $model = Driver::find($driver->getName());

            if ($model) {
                $driver->setAuthenticator(new ApiKeyAuthenticator($model->auth));
            }
        } catch (Exception $exception) {
        }

        $service->register($driver);
    }
}
