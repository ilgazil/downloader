<?php

namespace App\Services\Driver\Drivers;

use PHPHtmlParser\Dom;

class UnFichierParser
{
    protected Dom $dom;

    public function __construct(Dom $dom)
    {
        $this->dom = $dom;
    }

    public function getFileName(): string
    {
        $node = $this->dom->find('table.premium tr')[0]?->find('td')[2];

        if (!$node) {
            return '';
        }

        return $node->text;
    }

    public function getFileSize(): string
    {
        $node = $this->dom->find('table.premium tr')[2]?->find('td')[1];

        if (!$node) {
            return '';
        }

        return $node->text;
    }

    public function getFileError(): string
    {
        $node = $this->dom->find('div.ct_warn')[0];

        if (!$node) {
            return '';
        }

        return trim(str_replace(['&nbsp;', '<br/>'], ' ', $node->text));
    }

    public function getAnonymousDownloadLink(): string
    {
        $node = $this->dom->find('a.btn-orange')[0];

        if (!$node) {
            return '';
        }

        return $node->href;
    }

    public function getAnonymousDownloadToken(): string
    {
        $node = $this->dom->find('input[name="adz"]')[0];

        if (!$node) {
          return '';
        }

        return $node->value;
    }
}
