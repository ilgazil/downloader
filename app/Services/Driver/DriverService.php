<?php

namespace App\Services\Driver;

use App\Services\Driver\DriverInterface;
use App\Services\Driver\Exceptions\NoMatchingDriverException;

class DriverService
{
    protected array $drivers = [];

    public function register(DriverInterface $driver): void
    {
        $this->drivers[] = $driver;
    }

    public function all(): array
    {
        return $this->drivers;
    }

    public function findByUrl(string $url): DriverInterface
    {
        foreach ($this->drivers as $driver) {
            if ($driver->match($url)) {
                return $driver;
            }
        }

        throw new NoMatchingDriverException($url);
    }

    public function findByName(string $name): DriverInterface
    {
        foreach ($this->drivers as $driver) {
            if ($driver->getName() === $name) {
                return $driver;
            }
        }

        throw new NoMatchingDriverException($url);
    }
}
