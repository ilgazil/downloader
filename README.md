<h1 align="center">Host Downloader</h1>

<p align="center">
    Download files hosted on the web
</p>

---

## Installation

```shell
git pull git@github.com:ilgazil/host-downloader.git
cd host-downloader
./host-downloader app:build
sudo mv builds/host-downloader /usr/bin
sudo chmod +x /usr/bin/host-downloader
mkdir ~/.host-downloader
touch ~/.host-downloader/database.sqlite
/usr/bin/host-downloader migrate
```

I also suggest these aliases:

```shell
alias dl="host-downloader url:download"
alias dli="host-downloader url:info"
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

#### url:link

Retrieve and print premium link from a given url.

```shell
host-downloader url:link [--target] ...urls
```

* *url* - URLs to download from any host

```shell
$ host-downloader url:download https://some-host.com/hashcode
https://internal-storage.some-host.com/internal-hashcode/Some.file.name[1080p].mkv
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
