<?php

namespace App\Commands;

use App\Services\Driver\DriverService;

class UrlDownloadCommand extends AbstractCommand
{
    protected $signature = 'url:download
        {urls* : the urls to download from any host}
        {--target=. : folder where to save the file into}';

    protected $description = 'Download hosted file';

    protected DriverService $driverService;

    public function __construct(DriverService $driverService)
    {
        parent::__construct();

        $this->driverService = $driverService;
    }

    protected function _handle()
    {
        $result = 0;
        $count = count($this->argument('urls'));

        foreach ($this->argument('urls') as $index => $url) {
            try {
                $download = $this->driverService
                    ->findByUrl($url)
                    ->getDownload($url);

                $download->setTarget(
                    $this->option('target')
                    . DIRECTORY_SEPARATOR
                    . urldecode($download->getFileName())
                );

                if ($count > 1) {
                    if ($index++) {
                        $this->line('');
                    }

                    $this->line("Download $index/$count");
                }

                $this->line('Host: ' . $download->getDriver()->getName());
                $this->line('Name: ' . $download->getFileName());
                $this->line('File: ' . $download->getTarget());
                $this->line('Size: ' . $download->getFileSize());

                $bar = $this->output->createProgressBar();
                $bar->setFormat('[%bar%] %percent:3s%% - %remaining:6s% left');

                $download->start($bar);
            } catch (\Exception $exception) {
                $this->line('Error with url: ' . $url);
                $this->error($exception->getMessage());
                $result = 1;
            }

            $this->line('');

            return $result;
        }
    }
}
