<?php

namespace App\Commands;

use App\Services\Output\ColoredStringWriter;
use Exception;
use Ilgazil\LibDownload\Driver\DriverService;
use LaravelZero\Framework\Commands\Command;

class UrlInfoCommand extends Command
{
    protected $signature = 'url:info {urls* : the urls to retrieve headers from any host}';

    protected $description = 'Check for file infos';

    protected DriverService $driverService;

    public function __construct(DriverService $driverService)
    {
        parent::__construct();

        $this->driverService = $driverService;
    }

    protected function handle(): int
    {
        $urls = $this->argument('urls');

        foreach ($urls as $index => $url) {
            try {
                $metadata = $this->driverService
                    ->findByUrl($url)
                    ->getMetadata($url);

                if ($metadata->getFileError()) {
                    $state = (new ColoredStringWriter())->red($metadata->getFileError());
                } else if ($metadata->getDownloadCooldown()) {
                    $state = (new ColoredStringWriter())->cyan($metadata->getDownloadCooldown() . ' cooldown');
                } else {
                    $state = (new ColoredStringWriter())->green('Ready');
                }
            } catch (Exception $exception) {
                $state = (new ColoredStringWriter())->red($exception->getMessage());
            }

            if ($index) {
                $this->line('');
            }

            $this->line('Host: ' . $metadata->getDriverName());
            $this->line('File name: ' . $metadata->getFileName());
            $this->line('Size: ' . $metadata->getFileSize());
            $this->line('State: ' . $state);
        }

        return self::SUCCESS;
    }
}
