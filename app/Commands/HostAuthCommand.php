<?php

namespace App\Commands;

use App\Services\Driver\DriverService;
use App\Services\Driver\Exceptions\NoMatchingDriverException;

class HostAuthCommand extends AbstractCommand
{
    protected $signature = 'host:auth {driver} {login} {password}';

    protected $description = 'Authenticate on a host';

    protected DriverService $driverService;

    public function __construct(DriverService $driverService)
    {
        parent::__construct();

        $this->driverService = $driverService;
    }

    /**
     * @throws NoMatchingDriverException
     */
    protected function _handle()
    {
        $driver = $this->driverService->findByName($this->argument('driver'));
        $driver->authenticate($this->argument('login'), $this->argument('password'));

        $this->line('Connected to ' . $driver->getName());
    }
}
