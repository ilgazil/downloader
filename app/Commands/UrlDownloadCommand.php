<?php

namespace App\Commands;

use App\Services\Driver\DriverService;
use App\Services\Driver\Exceptions\NoMatchingDriverException;
use App\Services\File\Exceptions\DownloadException;

class UrlDownloadCommand extends AbstractCommand
{
    protected $signature = 'url:download {url} {target}';

    protected $description = 'Download hosted file';

    protected DriverService $driverService;

    public function __construct(DriverService $driverService)
    {
        parent::__construct();

        $this->driverService = $driverService;
    }

    /**
     * @throws DownloadException
     * @throws NoMatchingDriverException
     */
    protected function _handle()
    {
        $target = $this->argument('target');

        $download = $this->driverService
            ->findByUrl($this->argument('url'))
            ->getDownload($this->argument('url'));

        // If a path is given, filename is retrieved from download link
        if (
            !preg_match('/.*\/([^\/]+\.[^\/]+)$/', $target) &&
            preg_match('/.*\/([^\/]+\.[^\/]+)$/', $download->getUrl(), $matches)
        ) {
            $target .= DIRECTORY_SEPARATOR . urldecode($matches[1]);
        }

        $download->setTarget($target);

        $this->line('Host: ' . $download->getDriver()->getName());
        $this->line('Name: ' . $download->getFileName());
        $this->line('File: ' . $download->getTarget());
        $this->line('Size: ' . $download->getFileSize());

        $bar = $this->output->createProgressBar();
        $bar->setFormat('[%bar%] %percent:3s%% - %remaining:6s% left');

        $download->start($bar);
    }
}
