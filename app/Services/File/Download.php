<?php

namespace App\Services\File;

use anlutro\cURL\cURL;

use App\Services\Driver\DriverInterface;
use App\Services\File\Exceptions\DownloadException;
use Symfony\Component\Console\Helper\ProgressBar;

class Download
{
    static public string $PENDING = 'pending';
    static public string $RUNNING = 'running';
    static public string $PAUSED = 'paused';
    static public string $DONE = 'done';

    protected string $url;
    protected string $target;
    protected array $headers = [];
    protected DriverInterface $driver;
    protected string $fileName;
    protected string $fileSize;
    protected string $status;

    public function __construct()
    {
        $this->status = self::$PENDING;
    }

    public function getUrl(): string
    {
        return $this->url;
    }

    public function setUrl(string $url): void
    {
        $this->url = $url;
    }

    public function getTarget(): string
    {
        return $this->target;
    }

    public function setTarget(string $target): void
    {
        $this->target = $target;
    }

    public function getHeaders(): array
    {
        return $this->headers;
    }

    public function setHeaders(array $headers): void
    {
        $this->headers = $headers;
    }

    public function setHeader(string $name, $value): void
    {
        $this->headers[$name] = $value;
    }

    public function removeHeader(string $name): void
    {
        unlink($this->headers[$name]);
    }

    public function getDriver(): DriverInterface
    {
        return $this->driver;
    }

    public function setDriver(DriverInterface $driver): void
    {
        $this->driver = $driver;
    }

    public function getFileName(): string
    {
        return $this->fileName;
    }

    public function setFileName(string $fileName): void
    {
        $this->fileName = $fileName;
    }

    public function getFileSize(): string
    {
        return $this->fileSize;
    }

    public function setFileSize(string $fileSize): void
    {
        $this->fileSize = $fileSize;
    }

    /**
     * @throws DownloadException
     */
    public function start(ProgressBar $bar = null): void
    {
        $target = fopen($this->getTarget(), 'wb');

        if (!$target) {
            throw new DownloadException('Unable to write into ' . $this->getTarget());
        }

        $curl = new cURL();

        $this->status = self::$RUNNING;

        $request = $curl
            ->newRequest('get', $this->url)
            ->setOption(CURLOPT_HEADER, false)
            ->setOption(CURLOPT_FILE, $target);

        if ($bar) {
            $request
                ->setOption(CURLOPT_NOPROGRESS, false)
                ->setOption(
                    CURLOPT_PROGRESSFUNCTION,
                    function($curlResource, int $expectedSize, int $downloadedSize) use($bar) {
                        if (!$expectedSize) {
                            return;
                        }

                        $bar->setMaxSteps($expectedSize);
                        $bar->setProgress($downloadedSize);
                    }
                );
        }

        foreach ($this->headers as $header => $value) {
            $request->setHeader($header, $value);
        }

        // @todo Why this can be falsy thrown (with UpToBox files for instance)
        try {
            $request->send();
        } catch (\UnexpectedValueException $e) {
            if (!$e->getMessage() === 'Invalid response header') {
                throw $e;
            }
        }

        $this->status = self::$DONE;
    }
}
