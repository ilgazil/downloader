<?php

namespace App\Services\File;

class Cooldown
{
    protected int $hours = 0;
    protected int $minutes = 0;
    protected int $seconds = 0;

    public function setHours(int $hours): void
    {
        $this->hours = $hours;
    }

    public function getHours(): int
    {
        return $this->hours;
    }

    public function setMinutes(int $minutes): void
    {
        $this->minutes = $minutes;
    }

    public function getMinutes(): int
    {
        return $this->minutes;
    }

    public function setSeconds(int $seconds): void
    {
        $this->seconds = $seconds;
    }

    public function getSeconds(): int
    {
        return $this->seconds;
    }

    public function getValue(): int
    {
        return $this->seconds + $this->minutes * 60 + $this->hours * 3600;
    }

    public function getText(): string
    {
        $data = [];

        if ($this->hours) {
            $data[] = $this->pluralize("{$this->hours} hour", $this->hours);
        }

        if ($this->minutes) {
            $data[] = $this->pluralize("{$this->minutes} minute", $this->minutes);
        }

        if ($this->seconds) {
            $data[] = $this->pluralize("{$this->seconds} second", $this->seconds);
        }

        return preg_replace('/(.*)(,\s)([^,]+)$/', '$1 and $3', join(', ', $data));
    }

    protected function pluralize(string $message, int $count): string
    {
        return $message . (($count > 1) ? 's' : '');
    }
}
