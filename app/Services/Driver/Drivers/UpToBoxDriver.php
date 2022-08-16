<?php

namespace App\Services\Driver\Drivers;

use anlutro\cURL\cURL;
use App\Exceptions\DriverExceptions\AuthException;
use App\Exceptions\DriverExceptions\DriverException;
use App\Exceptions\FileExceptions\DownloadCooldownException;
use App\Exceptions\FileExceptions\DownloadException;
use App\Models\Driver as DriverModel;
use App\Services\Driver\DriverInterface;
use App\Services\File\Download;
use App\Services\File\Metadata;
use PHPHtmlParser\Dom;
use PHPHtmlParser\Exceptions\ChildNotFoundException;
use PHPHtmlParser\Exceptions\CircularException;
use PHPHtmlParser\Exceptions\ContentLengthException;
use PHPHtmlParser\Exceptions\LogicalException;
use PHPHtmlParser\Exceptions\NotLoadedException;
use PHPHtmlParser\Exceptions\StrictException;

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

    /**
     * @throws AuthException
     * @throws ChildNotFoundException
     * @throws ContentLengthException
     * @throws CircularException
     * @throws LogicalException
     * @throws NotLoadedException
     * @throws StrictException
     */
    public function authenticate(string $login, string $password): void
    {
        $this->login($login, $password);
    }

    public function unauthenticate(): void
    {
        DriverModel::find($this->getName())?->delete();
    }

    /**
     * @throws ChildNotFoundException
     * @throws CircularException
     * @throws ContentLengthException
     * @throws DriverException
     * @throws LogicalException
     * @throws StrictException
     */
    public function getMetadata(string $url): Metadata
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

    /**
     * @throws ChildNotFoundException
     * @throws CircularException
     * @throws ContentLengthException
     * @throws DriverException
     * @throws DownloadCooldownException
     * @throws DownloadException
     * @throws LogicalException
     * @throws StrictException
     */
    public function getDownload(string $url): Download
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

        $download = new Download();
        $download->setDriver($this);
        $download->setFileName($parser->getFileName());
        $download->setFileSize($parser->getFileSize());

        if ($parser->getAnonymousDownloadToken()) {
            $this->postAnonymousDownloadToken($url, $parser->getAnonymousDownloadToken());
        }

        $downloadLink = $parser->getPremiumDownloadLink() ?: $parser->getAnonymousDownloadLink();

        if (!$downloadLink) {
            throw new DownloadException('Unable to get download link');
        }

        $download->setUrl($downloadLink);

        if ($this->getCookie()) {
            $download->setHeader('Cookie', $this->getCookie());
        }

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

    /**
     * @throws AuthException
     * @throws ChildNotFoundException
     * @throws CircularException
     * @throws ContentLengthException
     * @throws LogicalException
     * @throws NotLoadedException
     * @throws StrictException
     */
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

    /**
     * @throws DriverException
     */
    protected function validateUrl(string $url): void
    {
        if (!$this->match($url)) {
            throw new DriverException('Wrong host for querying info : ' . $this->getName() . ' cannot handle ' . $url);
        }
    }

    /**
     * @throws DriverException
     * @throws ChildNotFoundException
     * @throws CircularException
     * @throws ContentLengthException
     * @throws LogicalException
     * @throws StrictException
     */
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
