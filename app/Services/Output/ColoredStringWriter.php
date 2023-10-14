<?php

namespace App\Services\Output;

class ColoredStringWriter {
    private array $foregroundColors = [];
    private array $backgroundColors = [];

    public function __construct() {
        $this->foregroundColors['black'] = '0;30';
        $this->foregroundColors['dark-gray'] = '1;30';
        $this->foregroundColors['blue'] = '0;34';
        $this->foregroundColors['light-blue'] = '1;34';
        $this->foregroundColors['green'] = '0;32';
        $this->foregroundColors['light-green'] = '1;32';
        $this->foregroundColors['cyan'] = '0;36';
        $this->foregroundColors['light-cyan'] = '1;36';
        $this->foregroundColors['red'] = '0;31';
        $this->foregroundColors['light-red'] = '1;31';
        $this->foregroundColors['purple'] = '0;35';
        $this->foregroundColors['light-purple'] = '1;35';
        $this->foregroundColors['brown'] = '0;33';
        $this->foregroundColors['yellow'] = '1;33';
        $this->foregroundColors['light-gray'] = '0;37';
        $this->foregroundColors['white'] = '1;37';

        $this->backgroundColors['black'] = '40';
        $this->backgroundColors['red'] = '41';
        $this->backgroundColors['green'] = '42';
        $this->backgroundColors['yellow'] = '43';
        $this->backgroundColors['blue'] = '44';
        $this->backgroundColors['magenta'] = '45';
        $this->backgroundColors['cyan'] = '46';
        $this->backgroundColors['light-gray'] = '47';
    }

    public function getColoredString(string $string, string $foregroundColor = '', string $backgroundColor = ''): string {
        $coloredString = "";

        if (isset($this->foregroundColors[$foregroundColor])) {
            $coloredString .= "\033[" . $this->foregroundColors[$foregroundColor] . "m";
        }

        if (isset($this->backgroundColors[$backgroundColor])) {
            $coloredString .= "\033[" . $this->backgroundColors[$backgroundColor] . "m";
        }

        $coloredString .=  $string . "\033[0m";

        return $coloredString;
    }

    public function green(string $string): string {
        return $this->getColoredString($string, 'green');
    }

    public function cyan(string $string): string {
        return $this->getColoredString($string, 'cyan');
    }

    public function yellow(string $string): string {
        return $this->getColoredString($string, 'yellow');
    }

    public function red(string $string): string {
        return $this->getColoredString($string, 'red');
    }
}
