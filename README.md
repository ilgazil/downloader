# Host Downloader

Hosted files downloader.



## Installation

```shell
git clone https://github.com/ilgazil/host-downloader.git
cd host-downloader
composer install
cp .env.exemple .env
```

Configure your database following the [Laravel documentation](https://laravel.com/docs/8.x/database#configuration) and then run the migrations.

```shell
php artisan migrate
```



## Usage

### Using the cli

#### host:auth

```shell
php artisan host:auth SomeHost user_name p455w0rd
```

```shell
Connected to SomeHost
```



#### host:auth

```shell
php artisan host:revoke SomeHost
```

```shell
Disconnected of SomeHost
```



#### url:info

```shell
php artisan url:info https://some-host.com/hashcode
```

```shell
Host: SomeHost
File name: Some.file.name[1080p]
Size: 370.40 MB
State: ready
```



#### url:download

```shell
php artisan url:download https://some-host.com/hashcode /home/user/videos
```

```shell
Host: SomeHost
File: /home/user/videos/Some.file.name[1080p].mkv
Size: 370.40 MB
```
