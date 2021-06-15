<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use App\Services\File\FileService;
use App\Services\Output\ColoredStringWriter;

class UrlDownload extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'url:download {url} {target}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Download hosted file';

    protected FileService $fileService;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(FileService $fileService)
    {
        parent::__construct();

        $this->fileService = $fileService;
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        try {
            $download = $this->fileService->download(
                $this->argument('url'),
                $this->argument('target')
            );

            echo 'File: ' . realpath($download->getTarget()) . PHP_EOL .
            'Size: ' . $download->getFileSize() . PHP_EOL;
        } catch (\Exception $e) {
            echo 'Unable to download: ' . (new ColoredStringWriter())->getColoredString($e->getMessage(), 'red') . PHP_EOL;
        }

        return 0;
    }
}
