<?php

namespace App\Commands;

use App\Services\Driver\DriverService;
use App\Services\Driver\Exceptions\NoMatchingDriverException;
use App\Services\File\Exceptions\DownloadException;
use App\Services\File\Renamer;

class UrlDownloadCommand extends AbstractCommand
{
    protected $signature = 'url:download
        {urls* : the urls to download from any host}
        {--target=. : folder where to save the file into}
        {--name= : movie or tv show name for renaming the file}
        {--auto-rename : reformat name for Name.S01E01.resolution.ext pattern (ignored if --name is set)}';

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
        $count = count($this->argument('urls'));

        foreach ($this->argument('urls') as $index => $url) {
            $download = $this->driverService
                ->findByUrl($url)
                ->getDownload($url);

            $name = urldecode($download->getFileName());

            if ($this->option('name')) {
                $name = (new Renamer())->rename($name, $this->option('name'));
            } elseif ($this->option('auto-rename')) {
                $name = (new Renamer())->autoRename($name);
            }

            $download->setTarget(
                $this->option('target')
                . DIRECTORY_SEPARATOR
                . $name
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

            $this->line('');
        }
    }
}
