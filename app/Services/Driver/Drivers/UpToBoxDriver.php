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
    static private string $ROOT_URL = 'https://uptobox.com/';

    public function match(string $url): bool {
        return (bool) preg_match('/https?:\/\/uptobox\.com\/[\w\d]+/', $url);
    }

    public function getName(): string
    {
        return 'UpToBox';
    }

    public function authenticate(string $login, string $password): void
    {
        $this->login($login, $password);
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
        $parser = new UpToBoxParser($this->getDom($url));

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
        $parser = new UpToBoxParser($this->getDom($url));

        if ($parser->getFileError()) {
            throw new DownloadException($parser->getFileError());
        }

        // Try to login with stored credentials if any. If it fails for any reason, we continue as anonymous
        if ($parser->isAnonymous()) {
            try {
                $model = DriverModel::find($this->getName());

                if ($model && $model->login) {
                    $this->login($model->login, $model->password);
                    $parser = new UpToBoxParser($this->getDom($url));
                }
            } catch (\Exception $e) {
            }
        }

        if ($parser->getDownloadCooldown()) {
            throw new DownloadCooldownException($parser->getDownloadCooldown());
        }

        if ($parser->getAnonymousDownloadToken()) {
            $this->postAnonymousDownloadToken($url, $parser->getAnonymousDownloadToken());
        }

        $downloadLink = $parser->getPremiumDownloadLink() ?: $parser->getAnonymousDownloadLink();

        if (!$downloadLink) {
            throw new DownloadException('Unable to get download link');
        }

        $model = DownloadModel::findOrNew($url);
        $model->url = $url;
        $model->hostName = $this->getName();
        $model->fileName = $parser->getFileName();
        $model->fileSize = $parser->getFileSize();
        $model->target = $target;
        $model->state = Download::$PENDING;

        // If a path is given, filename is retrieved from download link
        if (
            !preg_match('/.*\/([^\/]+\.[^\/]+)$/', $target) &&
            preg_match('/.*\/([^\/]+\.[^\/]+)$/', $downloadLink, $matches)
        ) {
            $model->target .= DIRECTORY_SEPARATOR . urldecode($matches[1]);
        }

        $model->save();

        $headers = [];
        if ($this->getCookie()) {
            $headers['Cookie'] = $this->getCookie();
        }

        $download = new Download($model, $headers);

        $download->start($downloadLink);

        return $download;
    }

    protected function getCookie(): string
    {
        $model = DriverModel::find($this->getName());

        if (!$model) {
            return '';
        }

        if (preg_match('/(\S+=[^;]+)/', $model->cookie, $matches)) {
            return $matches[1];
        }

        return '';
    }

    protected function login(string $login, string $password): void
    {
        $curl = new cURL();

        $response = $curl->post(self::$ROOT_URL . 'login', [
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

    protected function validateUrl(string $url): void
    {
        if (!$this->match($url)) {
            throw new HostException('Wrong host for querying info : ' . $this->getName() . ' cannot handle ' . $url);
        }
    }

    protected function getDom(string $url): Dom
    {
        $this->validateUrl($url);

        $curl = new cURL();

        $request = $curl->newRequest('get', $url);

        if ($this->getCookie()) {
            $request->setHeader('Cookie', $this->getCookie());
        }

        $response = $request->send();

        if ($response->statusCode !== 200) {
            throw new DriverException('Unable to reach ' . $url . ' (received ' . $response->statusText . ')');
        }

        if (isset($response->headers['set-cookie'])) {
            $model = DriverModel::findOrNew($this->getName());
            $model->name = $this->getName();
            $model->cookie = $response->headers['set-cookie'];
            $model->save();
        }

        $dom = new Dom();
        $dom->loadStr($response->body);

        return $dom;
    }

    protected function postAnonymousDownloadToken(string $url, string $token): void
    {
        $curl = new cURL();

        $response = $curl->post($url, ['waitingToken' => $token]);

        print_r($response->body);
    }
}
