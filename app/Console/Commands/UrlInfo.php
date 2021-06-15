<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use App\Services\File\FileService;
use App\Services\Output\ColoredStringWriter;

class UrlInfo extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'url:info {url*}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check for file infos';

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
        $urls = $this->argument('url');

        foreach ($urls as $url) {
            $metadata = $this->fileService->info($url);

            if ($metadata->getFileError()) {
                $state = (new ColoredStringWriter())->getColoredString($metadata->getFileError(), 'red');
            } else if ($metadata->getDownloadCooldown()) {
                $state = (new ColoredStringWriter())->getColoredString($metadata->getDownloadCooldown(), 'cyan');
            } else {
                $state = (new ColoredStringWriter())->getColoredString('ready', 'green');
            }

            echo 'Host: ' . $metadata->getDriverName() . PHP_EOL .
                'File name: ' . $metadata->getFileName() . PHP_EOL .
                'Size: ' . $metadata->getFileSize() . PHP_EOL .
                'State: ' . $state . PHP_EOL;
        }

        return 0;
    }
}
