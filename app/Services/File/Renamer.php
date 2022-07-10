<?php

namespace App\Services\File;

class Renamer
{
    private static string $NUMBER_REGEX = 'S(?:(?:aison|eason)?[\s.-]?)?(?<season>\d+)[\s.-]?E(?:(?:p|pisode)?[\s.-]?)?(?<episode>\d+)';
    private static string $RESOLUTION_REGEX = '[\s.-](\d+p)[\s.-]';
    private static string $EXTENSION_REGEX = '\.(\w+)$';

    public function autoRename(string $filename): string
    {
        $parts = $this->getNameParts($filename);

        return str_replace([' ', '_'], '.', $parts['name']) . $parts['number'] . $parts['resolution'] . $parts['extension'];
    }

    public function rename(string $filename, string $name): string
    {
        $parts = $this->getNameParts($filename);

        return str_replace([' ', '_'], '.', $name) . $parts['number'] . $parts['resolution'] . $parts['extension'];
    }

    public function getName(string $filename): string
    {
        preg_match_all(
            '/(?<name>.+?)[\s.-]*(?:' . self::$NUMBER_REGEX . '|' . self::$RESOLUTION_REGEX . '|' . self::$EXTENSION_REGEX . '|$)/',
            $filename,
            $matches,
            PREG_SET_ORDER,
        );

        return $matches[0][1] ?? '';
    }

    public function getNumber(string $filename): array
    {
        preg_match_all(
            '/' . self::$NUMBER_REGEX . '/',
            $filename,
            $matches,
            PREG_SET_ORDER,
        );

        if (count($matches)) {
            return [
                'season' => (int) ($matches[0]['season']),
                'episode' => (int) ($matches[0]['episode']),
            ];
        }

        return [];
    }

    public function getResolution(string $filename): string
    {
        preg_match_all(
            '/' . self::$RESOLUTION_REGEX . '/',
            $filename,
            $matches,
            PREG_SET_ORDER,
        );

        return $matches[0][1] ?? '';
    }

    public function getExtension(string $filename): string
    {
        preg_match_all(
            '/' . self::$EXTENSION_REGEX . '/',
            $filename,
            $matches,
            PREG_SET_ORDER,
        );

        return $matches[0][1] ?? '';
    }

    private function getNameParts(string $filename): array
    {
        $name = $this->getName($filename);

        $number = $this->getNumber($filename);
        $number = count($number) ? '.S' . sprintf('%02d', $number['season']) . 'E' . sprintf('%02d', $number['episode']) : '';

        $resolution = $this->getResolution($filename);
        $resolution = $resolution ? '.' . $resolution : '';

        $extension = $this->getExtension($filename);
        $extension = $extension ? '.' . $extension : '';

        return [
            'name' => $name,
            'number' => $number,
            'resolution' => $resolution,
            'extension' => $extension,
        ];
    }
}
