<?php

namespace App\Commands;

use App\Exceptions\DriverExceptions\NoMatchingDriverException;
use App\Services\Driver\DriverService;

class HostAuthCommand extends AbstractCommand
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
