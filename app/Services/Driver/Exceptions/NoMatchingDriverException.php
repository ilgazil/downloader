<?php

namespace App\Services\Driver\Exceptions;

class NoMatchingDriverException extends \Exception
{
    public function __construct(string $url)
    {
        parent::__construct('No matching driver for ' . $url);
    }
}
