<h1 align="center">Host Downloader</h1>

<p align="center">
    Download files hosted on the web
</p>

---

## Installation

```shell
wget https://github.com/ilgazil/host-downloader/releases/download/untagged-301422919c680099f774/host-downloader.v0.1.0-beta.tar.gz
tar -xvf host-downloader.v0.1.0-beta.tar.gz
rm host-downloader.v0.1.0-beta.tar.gz
sudo mv host-downloader /usr/bin
mkdir ~/.host-downloader
touch ~/.host-downloader/database.sqlite
host-downloader migrate
```

## Usage

Run the application without any parameters to see available commands. You can have more info about each command by invoking them and passing `--help` argument.

### Managing hosts

#### host:auth

Connects to your host account using your credentials. It stores your cookies to database in order to benefits premium features while downloading.

Note it will also save your credentials in order to reconnect in case of outdated cookie. Stored data remain only in your local database.

```shell
host-downloader host:auth host login password
```

* *host* - Host ID, see [supported hosts](#supported-hosts)
* *login* - Host user login
* *password* - Host user password

```shell
$ host-downloader host:auth SomeHost user_name p455w0rd
Connected to SomeHost
```

#### host:revoke

Revoke your host cookies. It also will remove your credentials from local database.

```shell
host-downloader host:revoke host
```

* *host* - Host ID, see [supported hosts](#supported-hosts)

```shell
$ host-downloader host:revoke SomeHost
Disconnected of SomeHost
```

#### host:status

Print the connection status hosts or of one host in particular (see [supported hosts](#supported-hosts)).

```shell
host-downloader host:status [host]
```

* *host* - Optional - Host ID, see supported hosts (if not provided, it shows every host status)

```shell
$ host-downloader host:status SomeHost
Host: SomeHost
Login: Not configured
```

### Retrieving infos and downloading files

#### url:info

Print information about a link behind an url.

```shell
host-downloader url:info ...urls
```

* *urls* - URLs to retrieve headers from any host

```shell
$ host-downloader url:info https://some-host.com/hashcode https://some-host.com/hashcode2
Host: SomeHost
File name: Some.file.name[1080p]
Size: 370.40 MB
State: Ready

Host: SomeHost
File name: Some.other.file.name[1080p]
Size: 372.25 MB
State: Ready
```

#### url:download

Print information about a link behind an url.

```shell
host-downloader url:download [--target] ...urls
```

* *url* - URLs to download from any host
* *target* - Optional - Local path for the download. If not specified, it will download in current folder

```shell
$ host-downloader url:download --target=./videos https://some-host.com/hashcode https://some-host.com/hashcode2
Download 1/2
Host: SomeHost
Name: Some.file.name[1080p]
File: /home/user/videos/Some.file.name[1080p].mkv
Size: 370.40 MB
[▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓]  100% - < 1 sec left

Download 2/2
Host: SomeHost
Name: Some.other.file.name[1080p]
File: /home/user/videos/Some.file.name[1080p].mkv
Size: 372.25 MB
[▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓░░░░░░░░░░░]  63% - 2 mins left
```

### Hidden commands

The following commands are not listed while running the application summary because they deserve installation, updating or debug purpose.

* `migrate` Run the database migrations
* `migrate:fresh` Drop all tables and re-run all migrations
* `migrate:reset` Rollback all database migrations
* `migrate:rollback` Rollback the last database migration
* `migrate:status` Show the status of each migration

## Supported hosts

| Host name     | Handle premium |
| ------------- |:--------------:|
| UpToBox       | ✔ |
