<?php

namespace App\Commands;

use App\Exceptions\AppException;
use App\Services\Builders\DownloadBuilder;
use App\Services\File\Renamer;
use App\Services\Output\ColoredStringWriter;
use Ilgazil\LibDownload\Driver\DriverService;

class UrlDownloadCommand extends AbstractCommand
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

    protected function _handle(): void
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
            } catch (AppException $exception) {
                if (isset($download)) {
                    $download->setError($exception->getMessage());
                } else {
                    $this->line("Error while processing $url:");
                    $this->line((new ColoredStringWriter())->getColoredString($exception->getMessage(), 'red'));
                }
            }

            $this->line('');
        }
    }
}
