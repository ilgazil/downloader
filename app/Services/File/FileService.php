<?php

namespace App\Services\File;

use App\Services\Driver\DriverService;

class FileService
{
    protected DriverService $driverService;

    public function __construct(DriverService $driverService)
    {
        $this->driverService = $driverService;
    }

    public function info(string $url): Metadata
    {
        return $this->driverService->findByUrl($url)->infos($url);
    }

    public function download(string $url, string $target): Download
    {
        return $this->driverService->findByUrl($url)->download($url, $target);
    }
}
