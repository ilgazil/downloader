<?php

namespace App\Commands;

use App\Exceptions\DriverExceptions\NoMatchingDriverException;
use App\Models\Driver as DriverModel;
use App\Services\Driver\DriverService;
use App\Services\Output\ColoredStringWriter;

class HostStatusCommand extends AbstractCommand
{
    protected $signature = 'host:status {driver=all : driver name}';

    protected $description = 'Display host auth infos';

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
        $messages = [];
        $drivers = $this->argument('driver') === 'all'
            ? $this->driverService->all()
            : [$this->driverService->findByName($this->argument('driver'))];

        $writer = new ColoredStringWriter();

        foreach ($drivers as $driver) {
            $model = DriverModel::find($driver->getName());

            $messages[] = 'Host: ' . $driver->getName();
            $messages[] = 'Login: ' . (($model && $model->login)
                ? $writer->getColoredString('Configured', 'green')
                : $writer->getColoredString('Not configured', 'yellow') . PHP_EOL);
        }

        foreach ($messages as $message) {
            $this->line($message);
        }
    }
}
