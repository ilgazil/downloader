<?php

namespace App\Commands;

use App\Services\Output\ColoredStringWriter;
use Ilgazil\LibDownload\Driver\DriverService;

class UrlInfoCommand extends AbstractCommand
{
    protected $signature = 'url:info {urls* : the urls to retrieve headers from any host}';

    protected $description = 'Check for file infos';

    protected DriverService $driverService;

    public function __construct(DriverService $driverService)
    {
        parent::__construct();

        $this->driverService = $driverService;
    }

    protected function _handle()
    {
        $urls = $this->argument('urls');

        foreach ($urls as $index => $url) {
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

            if ($index) {
                $this->line('');
            }

            $this->line('Host: ' . $metadata->getDriverName());
            $this->line('File name: ' . $metadata->getFileName());
            $this->line('Size: ' . $metadata->getFileSize());
            $this->line('State: ' . $state);
        }
    }
}
