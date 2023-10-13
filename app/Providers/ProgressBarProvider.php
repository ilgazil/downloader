<?php

namespace App\Providers;

use App\Services\Output\ColoredStringWriter;
use Ilgazil\LibDownload\File\Download;
use Illuminate\Support\ServiceProvider;
use Symfony\Component\Console\Helper\ProgressBar;

class ProgressBarProvider extends ServiceProvider
{
    public function boot()
    {}

    public function register()
    {
        $writer = new ColoredStringWriter();
        $message = '%message% | ';

        ProgressBar::setFormatDefinition(Download::$PENDING, $message . $writer->getColoredString('Waiting for an available slot...', 'cyan'));
        ProgressBar::setFormatDefinition(Download::$RUNNING, $message . '[%bar%] %percent:3s%% - %remaining:6s% left');
        ProgressBar::setFormatDefinition(Download::$DONE, $message . $writer->getColoredString('Complete', 'green'));
        ProgressBar::setFormatDefinition(Download::$ERROR, $message . $writer->getColoredString('Error: %error%', 'red'));
    }
}
