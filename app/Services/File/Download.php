<?php

namespace App\Services\File;

use anlutro\cURL\cURL;

use App\Models\Download as DownloadModel;

class Download
{
    static public $PENDING = 'pending';
    static public $RUNNING = 'running';
    static public $PAUSED = 'paused';
    static public $DONE = 'done';

    protected DownloadModel $model;
    protected array $headers;

    protected int $fileSize = 0;
    protected int $progress = 0;

    public function __construct(DownloadModel $model, array $headers = [])
    {
        $this->model = $model;
        $this->headers = $headers;
    }

    public function getTarget(): string
    {
        return $this->model->target;
    }

    public function getFileSize(): string
    {
        return $this->model->fileSize;
    }

    public function start(string $url): void
    {
        $target = fopen($this->getTarget(), 'wb');

        if (!$target) {
            throw new DownloadException('Unable to create ' . $this->getFilePath());
        }

        $curl = new cURL();

        $this->model->state = self::$RUNNING;
        $this->model->save();

        $request = $curl
            ->newRequest('get', $url)
            ->setOption(CURLOPT_HEADER, false)
            ->setOption(CURLOPT_FILE, $target)
            ->setOption(CURLOPT_NOPROGRESS, false)
            ->setOption(
                CURLOPT_PROGRESSFUNCTION,
                function($curlResource, int $expectedSize, int $downloadedSize) {
                    if (!$expectedSize) {
                        return;
                    }

                    if (!$this->fileSize) {
                        $this->fileSize = $expectedSize;
                    }

                    $ratio = round($downloadedSize / $expectedSize * 100);

                    if ($this->progress < $ratio) {
                        $this->progress = $ratio;

                        $this->model->progress = $ratio;
                        $this->model->save();
                    }
                }
            );

        foreach ($this->headers as $header => $value) {
            $request->setHeader($header, $value);
        }

        // @todo Dig to seek why this can be falsy thrown (with UpToBox files for instance)
        try {
            $response = $request->send();
        } catch (\UnexpectedValueException $e) {
            if (!$e->getMessage() === 'Invalid response header') {
                throw $e;
            }
        }

        $this->model->progress = 100;
        $this->model->state = self::$DONE;
        $this->model->save();
    }
}
