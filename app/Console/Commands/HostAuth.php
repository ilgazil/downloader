<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use App\Services\Driver\DriverService;

class HostAuth extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'host:auth {driver} {login} {password}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Authenticate on a provider';

    protected DriverService $driverService;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(DriverService $driverService)
    {
        parent::__construct();

        $this->driverService = $driverService;
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $driver = $this->driverService->findByName($this->argument('driver'));
        $driver->authenticate($this->argument('login'), $this->argument('password'));

        echo 'Connected to ' . $driver->getName() . PHP_EOL .
            'Cookie: ' . $driver->getCookie() . PHP_EOL;

        return 0;
    }
}
