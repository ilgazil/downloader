<?php

namespace App\Commands;

use App\Services\Driver\DriverService;
use App\Services\Output\ColoredStringWriter;

class UrlInfoCommand extends AbstractCommand
{
    protected $signature = 'url:info {url* : the full url of the hosted file}';

    protected $description = 'Check for file infos';

    protected DriverService $driverService;

    public function __construct(DriverService $driverService)
    {
        parent::__construct();

        $this->driverService = $driverService;
    }

    protected function _handle()
    {
        $urls = $this->argument('url');

        foreach ($urls as $url) {
            $metadata = $this->driverService
                ->findByUrl($url)
                ->getMetadata($url);

            if ($metadata->getFileError()) {
                $state = (new ColoredStringWriter())->getColoredString($metadata->getFileError(), 'red');
            } else if ($metadata->getDownloadCooldown()) {
                $state = (new ColoredStringWriter())->getColoredString($metadata->getDownloadCooldown() . ' cooldown', 'cyan');
            } else {
                $state = (new ColoredStringWriter())->getColoredString('Ready', 'green');
            }

            $this->line('Host: ' . $metadata->getDriverName());
            $this->line('File name: ' . $metadata->getFileName());
            $this->line('Size: ' . $metadata->getFileSize());
            $this->line('State: ' . $state);
        }
    }
}
