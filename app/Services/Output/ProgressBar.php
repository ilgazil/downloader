<?php

namespace App\Services\Output;

use Ilgazil\LibDownload\File\DownloadProgressInterface;
use Symfony\Component\Console\Helper\ProgressBar as BaseProgressBar;

class ProgressBar implements DownloadProgressInterface
{
    protected BaseProgressBar $progress;

    public function __construct(BaseProgressBar $progress)
    {
        $this->progress = $progress;
    }

    public function onProgress(int $expectedSize, int $downloadedSize): void
    {
        $this->progress->setMaxSteps($expectedSize);
        $this->progress->setProgress($downloadedSize);
    }

    public function onStatusChanged(string $status): void
    {
        $this->progress->setFormat($status);
    }

    public function onError(string $error): void
    {
        $this->progress->setMessage($error, 'error');
        $this->finish();
    }

    public function finish(): void
    {
        $this->progress->finish();
    }
}
