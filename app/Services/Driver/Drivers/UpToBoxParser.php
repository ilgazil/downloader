<?php

namespace App\Services\Driver\Drivers;

use anlutro\cURL\cURL;
use PHPHtmlParser\Dom;

use App\Services\Driver\Exceptions\DriverException;

// @todo cache infos
class UpToBoxParser
{
    protected UpToBoxDriver $driver;
    protected string $url;

    protected Dom $domCache;

    public function __construct(string $url)
    {
        $this->driver = new UpToBoxDriver();
        $this->url = $url;
        $this->domCache = new Dom();
    }

    protected function getDom(): Dom
    {
        if (!$this->domCache->root) {
            $curl = new cURL();

            $request = $curl->newRequest('get', $this->url);

            if ($this->driver->getCookie()) {
                $request->setHeader('Cookie', $this->driver->getCookie());
            }

            $response = $request->send();

            if ($response->statusCode !== 200) {
                throw new DriverException('Unable to reach ' . $this->url . ' (received ' . $response->statusText . ')');
            }

            $this->domCache = new Dom();
            $this->domCache->loadStr($response->body);
        }

        return $this->domCache;
    }

    public function getUrl(): string
    {
        return $this->url;
    }

    public function getFileName(): string
    {
        $node = $this->getDom()->find('div#dl h1.file-title');

        if (count($node) && preg_match('/(.+)\.\w+\s\(([\d\.]+\s\w+)\)/', $node->text, $matches)) {
            return trim($matches[1]);
        }

        return '';
    }

    public function getFileSize(): string
    {
        $node = $this->getDom()->find('div#dl h1.file-title');

        if (count($node) && preg_match('/(.+)\.\w+\s\(([\d\.]+\s\w+)\)/', $node->text, $matches)) {
            return trim($matches[2]);
        }

        return '';
    }

    public function getDownloadCooldown(): string
    {
        $node = $this->getDom()->find('div#dl table.comparison-table span.time-remaining');

        if (!count($node)) {
            return '';
        }

        return trim(str_replace('&nbsp;', ' ', $node->text));
    }

    public function getFileError(): string
    {
        $node = $this->getDom()->find('div#dl span.red p');

        if (!count($node)) {
            return '';
        }

        return trim(str_replace('&nbsp;', ' ', $node->text));
    }

    public function getDownloadLink(): string
    {
        if ($this->getAuthenticatedDownloadLink()) {
            return $this->getAuthenticatedDownloadLink();
        }

        if (!$this->getAnonymousDownloadLink() && !$this->getDownloadCooldown()) {
            $this->submitDownloadToken();
        }

        return $this->getAnonymousDownloadLink();
    }

    protected function getAuthenticatedDownloadLink(): string
    {
        $node = $this->getDom()->find('div#dl div center a.big-button-green-flat');

        if (!count($node)) {
            return '';
        }

        return $node->href;
    }

    protected function getAnonymousDownloadLink(): string
    {
        $nodes = $this->getDom()->find('div#dl table.comparison-table a.big-button-green-flat');

        if (count($nodes) !== 2) {
            return '';
        }

        return $nodes->offsetGet(1)->href;
    }

    protected function submitDownloadToken(): void
    {
        $node = $this->getDom()->find('div#dl table.comparison-table input');

        if (!count($node)) {
          throw new DriverException('No anonymous token found');
        }

        $response = (new cURL())->post($this->url, [
            'waitingToken' => $node->value,
        ]);

        if ($response->statusCode !== 200) {
            throw new DriverException('Error occured while submitting download token');
        }

        $this->domCache->loadStr($response->body);
    }
}
