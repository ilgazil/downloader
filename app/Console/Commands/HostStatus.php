<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use App\Models\Driver as DriverModel;
use App\Services\Driver\DriverService;
use App\Services\Output\ColoredStringWriter;

class HostStatus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'host:status {driver?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Display host auth infos. If no host is provided, displays every hosts';

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
        $statuses = [];
        $drivers = $this->argument('driver')
            ? [$this->driverService->findByName($this->argument('driver'))]
            : $this->driverService->all();

        $writer = new ColoredStringWriter();

        foreach ($drivers as $driver) {
            $model = DriverModel::find($driver->getName());

            $statuses[] = 'Host: ' . $driver->getName() . PHP_EOL .
                'Login: ' . (($model && $model->login)
                    ? $writer->getColoredString('Configured', 'green')
                    : $writer->getColoredString('Not configured', 'yellow') . PHP_EOL);
        }

        echo implode(PHP_EOL . PHP_EOL, $statuses) . PHP_EOL;

        return 0;
    }
}
