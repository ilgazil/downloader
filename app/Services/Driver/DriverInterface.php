<?php

namespace App\Services\Driver;

use App\Services\File\Download;
use App\Services\File\Metadata;

abstract class DriverInterface
{
    abstract function match(string $url): bool;
    abstract function getName(): string;
    abstract function authenticate(string $login, string $password): void;
    abstract function unauthenticate(): void;
    abstract function getMetadata(string $url): Metadata;
    abstract function getDownload(string $url): Download;
}
