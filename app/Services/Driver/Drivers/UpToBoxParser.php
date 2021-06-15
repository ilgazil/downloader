<?php

namespace App\Services\Driver\Drivers;

use anlutro\cURL\cURL;
use PHPHtmlParser\Dom;

use App\Services\Driver\Exceptions\DriverException;

// @todo cache infos
class UpToBoxParser
{
    protected Dom $dom;

    public function __construct(Dom $dom)
    {
        $this->dom = $dom;
    }

    public function getFileName(): string
    {
        $node = $this->dom->find('div#dl h1.file-title');

        if (count($node) && preg_match('/(.+)\.\w+\s\(([\d\.]+\s\w+)\)/', $node->text, $matches)) {
            return trim($matches[1]);
        }

        return '';
    }

    public function getFileSize(): string
    {
        $node = $this->dom->find('div#dl h1.file-title');

        if (count($node) && preg_match('/(.+)\.\w+\s\(([\d\.]+\s\w+)\)/', $node->text, $matches)) {
            return trim($matches[2]);
        }

        return '';
    }

    public function getDownloadCooldown(): string
    {
        $node = $this->dom->find('div#dl span.red p');

        if (!count($node)) {
            return '';
        }

        if (preg_match('/you can wait (.*) to launch a new download/', $node->text, $matches)) {
            return $matches[1];
        }

        return '';
    }

    public function getFileError(): string
    {
        $node = $this->dom->find('div#dl span.red p');

        if (!count($node)) {
            return '';
        }

        $message = trim(str_replace('&nbsp;', ' ', $node->text));

        if (strpos($message, 'You need a PREMIUM account to download new files immediately without waiting') === 0) {
            return '';
        }

        return $message;
    }

    public function getPremiumDownloadLink(): string
    {
        $node = $this->dom->find('div#dl div center a.big-button-green-flat');

        if (!count($node)) {
            return '';
        }

        return $node->href;
    }

    public function getAnonymousDownloadLink(): string
    {
        $nodes = $this->dom->find('div#dl table.comparison-table a.big-button-green-flat');

        if (count($nodes) !== 2) {
            return '';
        }

        return $nodes->offsetGet(1)->href;
    }

    public function getAnonymousDownloadToken(): string
    {
        $node = $this->dom->find('div#dl table.comparison-table input');

        if (!count($node)) {
          return '';
        }

        return $node->value;
    }

    public function isAnonymous(): bool
    {
        $node = $this->dom->find('div#navbar .navbar-items li');

        return count($node) < 6;
    }
}
