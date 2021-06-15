<?php

namespace App\Services\Driver\Drivers;

use anlutro\cURL\cURL;
use PHPHtmlParser\Dom;

use App\Models\Driver as DriverModel;
use App\Models\Download as DownloadModel;
use App\Services\File\Download;
use App\Services\File\Exceptions\DownloadCooldownException;
use App\Services\File\Exceptions\DownloadException;
use App\Services\File\Metadata;
use App\Services\Driver\DriverInterface;

class UpToBoxDriver extends DriverInterface
{
    static private string $LOGIN_URL = 'https://uptobox.com/login';

    public function match(string $url): bool {
        return (bool) preg_match('/https?:\/\/uptobox\.com\/[\w\d]+/', $url);
    }

    public function getName(): string
    {
        return 'UpToBox';
    }

    public function getRawCookie(): string
    {
        $model = DriverModel::find($this->getName());

        if (!$model) {
            return '';
        }

        return $model->cookie;
    }

    public function getCookie(): string
    {
        if (preg_match('/(\S+=[^;]+)/', $this->getRawCookie(), $matches)) {
            return $matches[1];
        }

        return '';
    }

    public function authenticate(string $login, string $password): void
    {
        $curl = new cURL();

        $response = $curl->post(self::$LOGIN_URL, [
            'login' => $login,
            'password' => $password,
        ]);

        if ($response->statusCode !== 302) {
            $dom = new Dom;
            $dom->loadStr($response->body);

            throw new AuthException('Error while authenticating on ' . $this->getName() . ': ' . $dom->find('form li.errors')->text);
        }

        if (empty($response->headers['set-cookie'])) {
            throw new AuthException('No cookie in response headers');
        }

        $model = DriverModel::findOrNew($this->getName());
        $model->name = $this->getName();
        $model->login = $login;
        $model->password = $password;
        $model->cookie = $response->headers['set-cookie'];
        $model->save();
    }

    public function unauthenticate(): void
    {
        $model = DriverModel::find($this->getName());

        if ($model) {
            $model->delete();
        }
    }

    public function infos(string $url): Metadata
    {
        $this->validateUrl($url);

        $parser = new UpToBoxParser($url);

        $metadata = new Metadata();
        $metadata->setDriverName($this->getName());
        $metadata->setFileName($parser->getFileName());
        $metadata->setFileSize($parser->getFileSize());
        $metadata->setFileError($parser->getFileError());
        $metadata->setDownloadCooldown($parser->getDownloadCooldown());

        return $metadata;
    }

    public function download(string $url, string $target): Download
    {
        $this->validateUrl($url);

        $parser = new UpToBoxParser($url);

        if ($parser->getFileError()) {
            throw new DownloadException($parser->getFileError());
        }

        if ($parser->getDownloadCooldown()) {
            throw new DownloadCooldownException($parser->getDownloadCooldown());
        }

        $download = new Download($url, $target);

        $headers = [];
        if ($this->getCookie()) {
            $headers['Cookie'] = $this->getCookie();
        }

        $download->setId($url);
        $download->setUrl($parser->getDownloadLink());

        DownloadModel::updateOrCreate(
            ['url' => $url],
            [
                'hostName' => $this->getName(),
                'fileName' => $parser->getFileName(),
                'fileSize' => $parser->getFileSize(),
                'target' => $target,
                'state' => Download::$PENDING,
            ]
        );

        $download->start($headers);

        return $download;
    }

    protected function validateUrl(string $url): void
    {
        if (!$this->match($url)) {
            throw new HostException('Wrong host for querying info : ' . $this->getName() . ' cannot handle ' . $url);
        }
    }
}
