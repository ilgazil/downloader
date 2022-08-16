<?php

namespace App\Services\Driver\Drivers;

use PHPHtmlParser\Dom;

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
        $node = $this->dom->find('div#dl h1.file-title')[0];

        if ($node && preg_match('/(.+)\s\([\d\.]+\s\w+\)/', $node->text, $matches)) {
            return trim($matches[1]);
        }

        return '';
    }

    public function getFileSize(): string
    {
        $node = $this->dom->find('div#dl h1.file-title')[0];

        if ($node && preg_match('/.+\s\(([\d\.]+\s\w+)\)/', $node->text, $matches)) {
            return trim($matches[1]);
        }

        return '';
    }

    public function getDownloadCooldown(): string
    {
        $node = $this->dom->find('div#dl span.red p')[0];

        if (!$node) {
            return '';
        }

        if (preg_match('/you can wait (.*) to launch a new download/', $node->text, $matches)) {
            return $matches[1];
        }

        return '';
    }

    public function getFileError(): string
    {
        $node = $this->dom->find('div#dl span.red p')[0];

        if (!$node) {
            return '';
        }

        $message = trim(str_replace('&nbsp;', ' ', $node->text));

        if (str_starts_with($message, 'You need a PREMIUM account to download new files immediately without waiting')) {
            return '';
        }

        return $message;
    }

    public function getPremiumDownloadLink(): string
    {
        $node = $this->dom->find('div#dl div center a.big-button-green')[0];

        if (!$node) {
            return '';
        }

        return $node->href;
    }

    public function getAnonymousDownloadLink(): string
    {
        $node = $this->dom->find('div#dl table.comparison-table a.big-button-green')[1];

        if (!$node) {
            return '';
        }

        return $node->href;
    }

    public function getAnonymousDownloadToken(): string
    {
        $node = $this->dom->find('div#dl table.comparison-table input')[0];

        if (!$node) {
          return '';
        }

        return $node->value;
    }

    public function isAnonymous(): bool
    {
        $nodes = $this->dom->find('div#navbar .navbar-items li');

        return count($nodes) < 6;
    }
}
