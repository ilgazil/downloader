<?php

namespace App\Commands;

use App\Services\Builders\DownloadBuilder;
use App\Services\File\Renamer;
use App\Services\Output\ColoredStringWriter;
use Exception;
use Ilgazil\LibDownload\Driver\DriverService;
use LaravelZero\Framework\Commands\Command;

class UrlDownloadCommand extends Command
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

    public function handle(): int
    {
        $count = count($this->argument('urls'));

        $downloadBuilder = new DownloadBuilder();
        $downloadBuilder->setDriverService($this->driverService);
        $downloadBuilder->setOutput($this->output);

        foreach ($this->argument('urls') as $index => $url) {
            if ($count > 1) {
                if ($index++) {
                    $this->line('');
                }

                $this->line("Download $index/$count - $url");
            }

            try {
                $download = $downloadBuilder->build($url);

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

                $download->start();
            } catch (Exception $exception) {
                $hasError = true;
                if (isset($download)) {
                    $download->setError($exception->getMessage());
                } else {
                    $this->line("Error while processing $url:");
                    $this->line((new ColoredStringWriter())->red($exception->getMessage()));
                }
            }

            $this->line('');
        }

        return empty($hasError) ? self::SUCCESS : self::FAILURE;
    }
}
