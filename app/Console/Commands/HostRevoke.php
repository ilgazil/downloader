<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use App\Services\Driver\DriverService;

class HostRevoke extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'host:revoke {driver}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Unauthenticate on a host, and remove saved credentials';

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
        $driver->unauthenticate();

        echo 'Disconnected of ' . $driver->getName() . PHP_EOL;

        return 0;
    }
}
