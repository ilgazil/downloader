<?php

namespace App\Commands;

use App\Models\Driver;
use Exception;
use Ilgazil\LibDownload\Driver\DriverService;
use LaravelZero\Framework\Commands\Command;

class HostAuthCommand extends Command
{
    protected $signature = 'host:auth
        {driver : driver name}
        {login : credential login}
        {password : credential password}';

    protected $description = 'Authenticate on a host';

    protected DriverService $driverService;

    public function __construct(DriverService $driverService)
    {
        parent::__construct();

        $this->driverService = $driverService;
    }

    protected function handle(): int
    {
        try {
            $driver = $this->driverService->findByName($this->argument('driver'));

            $driver->login(
                $this->argument('login'),
                $this->argument('password'),
            );

            $model = Driver::findOr(
                $this->argument('driver'),
                function () {
                    $driver = new Driver();
                    $driver->name = $this->argument('driver');
                },
            );

            $model->login = $this->argument('login');
            $model->password = $this->argument('password');
            $model->vector = $driver->getSession()->getVector()->getValue();
        } catch (Exception $exception) {
            $this->line('Unable to connected to ' . $this->argument('driver') . ': ' . $exception->getMessage());

            return self::FAILURE;
        }

        $this->line('Connected to ' . $driver->getName());

        return self::SUCCESS;
    }
}
