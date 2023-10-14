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
        {auth : auth key, depending on driver authentication method}';

    protected $description = 'Authenticate on a host';

    protected DriverService $driverService;

    public function __construct(DriverService $driverService)
    {
        parent::__construct();

        $this->driverService = $driverService;
    }

    public function handle(): int
    {
        try {
            $driver = $this->driverService->findByName($this->argument('driver'));

            $model = Driver::find($driver->getName());

            if (!$model) {
                $model = new Driver();
                $model->name = $driver->getName();
            }

            $model->auth = $this->argument('auth');

            $model->save();
        } catch (Exception $exception) {
            $this->line('Unable to save authenticator for ' . $this->argument('driver') . ': ' . $exception->getMessage());

            return self::FAILURE;
        }

        $this->line('Authenticator saved for ' . $driver->getName());

        return self::SUCCESS;
    }
}
