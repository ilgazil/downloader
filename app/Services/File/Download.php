<?php

namespace App\Services\File;

use anlutro\cURL\cURL;

use App\Models\Download as DownloadModel;

class Download
{
    static protected $CHUNK_SIZE = 1024 * 1024 * 1; // 1 MB

    static public $PENDING = 'pending';
    static public $RUNNING = 'running';
    static public $PAUSED = 'paused';
    static public $DONE = 'done';

    protected string $id = '';
    protected string $url;
    protected string $filePath = '';
    protected string $fileName = '';
    protected int $fileSize = 0;
    protected int $progress = 0;

    public function __construct(string $url, string $target)
    {
        $this->setTarget($target);
        $this->setUrl($url);
    }

    public function setId(string $id): void
    {
        $this->id = $id;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function setUrl(string $url): void
    {
        $this->url = $url;

        if (!$this->fileName && preg_match('/.*\/([^\/]*\.[^\/]*)$/', $url, $matches)) {
            $this->setFileName(urldecode($matches[1]));
        }
    }

    public function getUrl(): string
    {
        return $this->url;
    }

    public function setFilePath(string $filePath): void
    {
        $this->filePath = $filePath;
    }

    public function getFilePath(): string
    {
        return $this->filePath;
    }

    public function setFileName(string $fileName): void
    {
        $this->fileName = $fileName;
    }

    public function getFileName(): string
    {
        return $this->fileName;
    }

    public function setTarget(string $target): void
    {
        if (preg_match('/(.*)\/([^\/]*\.[^\/]*)$/', $target, $matches)) {
            $this->filePath = $matches[1];

            if ($matches[2] !== '.') {
                $this->fileName = $matches[2];
            }
        } else {
            $this->filePath = $target;
        }
    }

    public function getTarget(): string
    {
        return realpath($this->filePath) . DIRECTORY_SEPARATOR . $this->fileName;
    }

    public function setFileSize(int $fileSize): void
    {
        $this->fileSize = $fileSize;
    }

    public function getFileSize(): int
    {
        return $this->fileSize;
    }

    public function start(array $headers = []): void
    {
        $target = fopen($this->getTarget(), 'wb');

        if (!$target) {
            throw new DownloadException('Unable to create ' . $this->getFilePath());
        }

        $curl = new cURL();

        if ($this->id) {
            $model = DownloadModel::findOrNew($this->id);
            $model->id = $this->id;
            $model->state = self::$RUNNING;
            $model->save();
        }

        $request = $curl
            ->newRequest('get', $this->url)
            ->setOption(CURLOPT_HEADER, false)
            ->setOption(CURLOPT_FILE, $target)
            ->setOption(CURLOPT_NOPROGRESS, false)
            ->setOption(
                CURLOPT_PROGRESSFUNCTION,
                function($curlResource, int $expectedSize, int $downloadedSize) use ($model) {
                    if (!$model || !$expectedSize) {
                        return;
                    }

                    if (!$this->fileSize) {
                        $this->fileSize = $expectedSize;
                    }

                    $ratio = round($downloadedSize / $expectedSize * 100);

                    if ($this->progress < $ratio) {
                        $this->progress = $ratio;

                        $model->progress = $ratio;
                        $model->save();
                    }
                }
            );

        foreach ($headers as $header => $value) {
            $request->setHeader($header, $value);
        }

        // @see https://github.com/anlutro/php-curl/issues/65
        // try {
            $response = $request->send();
        // } catch (\InvalidArgumentException $e) {
        //     if (!$e->getMessage() === 'Invalid response header') {
        //         throw $e;
        //     }
        // }

        if ($model) {
            $model->progress = 100;
            $model->state = self::$DONE;
            $model->save();
        }
    }
}
