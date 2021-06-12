<?php

namespace App\Services\Driver;

use App\Services\File\Download;
use App\Services\File\Metadata;

abstract class DriverInterface
{
    abstract function match(string $url): bool;
    abstract function getName(): string;
    abstract function getCookie(): string;
    abstract function authenticate(string $login, string $password): void;
    abstract function unauthenticate(): void;
    abstract function infos(string $url): Metadata;
    abstract function download(string $url, string $target): Download;
}
