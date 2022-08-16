<?php

namespace App\Exceptions\DriverExceptions;

use App\Exceptions\AppException;

class NoMatchingDriverException extends AppException
{
    public function __construct(string $identifier)
    {
        parent::__construct('No matching driver for ' . $identifier);
    }
}
