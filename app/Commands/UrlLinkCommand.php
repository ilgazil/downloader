<?php

namespace App\Commands;

use App\Exceptions\AppException;
use App\Services\Builders\DownloadBuilder;
use App\Services\Output\ColoredStringWriter;
use Ilgazil\LibDownload\Driver\DriverService;

class UrlLinkCommand extends AbstractCommand
{
    protected $signature = 'url:link
        {urls* : the urls to download from any host}';

    protected $description = 'Get the premium links of a hosted file';

    protected DriverService $driverService;

    public function __construct(DriverService $driverService)
    {
        parent::__construct();

        $this->driverService = $driverService;
    }

    protected function _handle(): void
    {
        $urls = [];
        $hasError = false;

        $downloadBuilder = new DownloadBuilder();
        $downloadBuilder->setDriverService($this->driverService);
        $downloadBuilder->setOutput($this->output);

        $input = explode(' ', $this->argument('urls')[0]);

        foreach ($input as $index => $url) {
            try {
                $download = $downloadBuilder->build($url);
                $urls[] = $download->getUrl();
            } catch (AppException $exception) {
                $hasError = true;
                $this->line("Error while processing $url:");
                $this->line((new ColoredStringWriter())->getColoredString($exception->getMessage(), 'red'));
            }
        }

        if (empty($urls)) {
            return;
        }

        if ($hasError) {
            $this->line(
                (new ColoredStringWriter())
                    ->getColoredString('Url that have generated errors are not listed below', 'red'),
            );
        }

        foreach ($urls as $url) {
            $this->line($url);
        }
    }
}
