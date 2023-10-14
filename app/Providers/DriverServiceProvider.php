<?php

namespace App\Providers;

use App\Models\Driver;
use Ilgazil\LibDownload\Driver\Drivers\UnFichier\UnFichierDriver;
use Ilgazil\LibDownload\Driver\DriverService;
use Ilgazil\LibDownload\Session\Credentials;
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

        $model = Driver::find($driver->getName());

        if ($model) {
            $driver->getSession()->setCredentials(
                (new Credentials())->setLogin($model->login)->setPassword($model->password),
            );
            $driver->getSession()->getVector()->setValue($model->vector);
        }

        $service->register($driver);
    }
}
