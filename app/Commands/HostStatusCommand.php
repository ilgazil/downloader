<?php

namespace App\Commands;

use App\Models\Driver as DriverModel;
use App\Services\Output\ColoredStringWriter;
use Ilgazil\LibDownload\Driver\DriverService;
use LaravelZero\Framework\Commands\Command;

class HostStatusCommand extends Command
{
    protected $signature = 'host:status {driver=all : driver name}';

    protected $description = 'Display host auth infos';

    protected DriverService $driverService;

    public function __construct(DriverService $driverService)
    {
        parent::__construct();

        $this->driverService = $driverService;
    }

    public function handle(): int
    {
        $messages = [];
        $drivers = $this->argument('driver') === 'all'
            ? $this->driverService->all()
            : [$this->driverService->findByName($this->argument('driver'))];

        $writer = new ColoredStringWriter();

        foreach ($drivers as $driver) {
            $model = DriverModel::find($driver->getName());

            $messages[] = 'Host: ' . $driver->getName();
            $messages[] = 'Authenticator: ' .
                ($model?->auth ? $writer->green('Configured') : $writer->yellow('Not configured')) .
                PHP_EOL;
        }

        foreach ($messages as $message) {
            $this->line($message);
        }

        return self::SUCCESS;
    }
}
