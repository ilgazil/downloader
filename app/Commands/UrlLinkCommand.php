<?php

namespace App\Commands;

use App\Services\Builders\DownloadBuilder;
use App\Services\Output\ColoredStringWriter;
use Exception;
use Ilgazil\LibDownload\Driver\DriverService;
use LaravelZero\Framework\Commands\Command;

class UrlLinkCommand extends Command
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

    protected function handle(): int
    {
        $urls = [];
        $hasError = false;

        $downloadBuilder = new DownloadBuilder();
        $downloadBuilder->setDriverService($this->driverService);
        $downloadBuilder->setOutput($this->output);

        $input = explode(' ', $this->argument('urls')[0]);

        foreach ($input as $url) {
            try {
                $download = $downloadBuilder->build($url);
                $urls[] = $download->getUrl();
            } catch (Exception $exception) {
                $hasError = true;
                $this->line("Error while processing $url:");
                $this->line((new ColoredStringWriter())->red($exception->getMessage()));
            }
        }

        if (empty($urls)) {
            return $hasError ? self::FAILURE : self::INVALID;
        }

        if ($hasError) {
            $this->line((new ColoredStringWriter())->red('Url that have generated errors are not listed below'));
        }

        foreach ($urls as $url) {
            $this->line($url);
        }

        return self::SUCCESS;
    }
}
