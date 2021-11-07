<?php

namespace App\Services\File;

class Metadata
{
    protected string $url;
    protected string $driverName = '';
    protected string $fileName = '';
    protected string $fileSize = '';
    protected string $fileError = '';
    protected Cooldown $cooldown;

    public function __construct()
    {
        $this->cooldown = new Cooldown();
    }

    public function setUrl(string $url): void
    {
        $this->url = $url;
    }

    public function getUrl(): string
    {
        return $this->url;
    }

    public function setDriverName(string $driverName): void
    {
        $this->driverName = $driverName;
    }

    public function getDriverName(): string
    {
        return $this->driverName;
    }

    public function setFileName(string $fileName): void
    {
        $this->fileName = $fileName;
    }

    public function getFileName(): string
    {
        return $this->fileName;
    }

    public function setFileSize(string $fileSize): void
    {
        $this->fileSize = $fileSize;
    }

    public function getFileSize(): string
    {
        return $this->fileSize;
    }

    public function setFileError(string $fileError): void
    {
        $this->fileError = $fileError;
    }

    public function getFileError(): string
    {
        return $this->fileError;
    }

    public function setCooldown(Cooldown $cooldown): void
    {
        $this->cooldown = $cooldown;
    }

    public function getCooldown(): Cooldown
    {
        return $this->cooldown;
    }
}
