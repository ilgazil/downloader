<h1 align="center">Host Downloader</h1>

<p align="center">
    Download files hosted on the web
</p>

---

## Installation

```shell
mkdir ~/.host-downloader
touch ~/.host-downloader/database.sqlite
host-downloader migrate
```

## Usage

### Managing hosts

#### host:auth

Connects to your host account using your credentials. It stores your cookies to database in order to benefits premium features while downloading.

Note it will also save your credentials in order to reconnect in case of outdated cookie. Stored data remain only in your local database.

```shell
host-downloader host:auth host login password
```

* *host* - Host ID, see supported hosts
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

* *host* - Host ID, see supported hosts

```shell
$ host-downloader host:revoke SomeHost
Disconnected of SomeHost
```

#### host:status

Print the connection status hosts or of one host in particular.

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
host-downloader url:info url
```

* *url* - URL to retrieve information from

```shell
$ host-downloader url:info https://some-host.com/hashcode
Host: SomeHost
File name: Some.file.name[1080p]
Size: 370.40 MB
State: Ready
```

#### url:download

Print information about a link behind an url.

```shell
host-downloader url:download url [target]
```

* *url* - URL to download from
* *target* - Optional - Local path for the download. It can be a directory or a filename. If not specified, it will download in current folder, with the default file name

```shell
$ host-downloader url:download https://some-host.com/hashcode /home/user/videos
Host: SomeHost
Name: Some.file.name[1080p]
File: /home/user/videos/Some.file.name[1080p].mkv
Size: 370.40 MB
[▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓░░░░░░░░░░░]  63% - 2 mins left
```
