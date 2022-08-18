<?php

namespace App\Services\Builders;

use App\Exceptions\DriverExceptions\NoMatchingDriverException;
use App\Services\Driver\DriverService;
use App\Services\File\Download;
use Illuminate\Console\OutputStyle;

class DownloadBuilder
{
    protected DriverService $driverService;
    protected OutputStyle $output;

    public function getDriverService(): DriverService
    {
        return $this->driverService;
    }

    public function setDriverService(DriverService $driverService): void
    {
        $this->driverService = $driverService;
    }

    public function getOutput(): OutputStyle
    {
        return $this->output;
    }

    public function setOutput(OutputStyle $output): void
    {
        $this->output = $output;
    }

    /**
     * @throws NoMatchingDriverException
     */
    public function build(string $url): Download
    {
        $download = $this->driverService
            ->findByUrl($url)
            ->getDownload($url);

        $bar = $this->output->createProgressBar();
        $bar->setMessage(
            '[' . $download->getDriver()->getName() . '] ' .
            $download->getFileName() .
            ' (' . $download->getFileSize() .')'
        );

        $download->setProgressBar($bar);

        return $download;
    }
}
