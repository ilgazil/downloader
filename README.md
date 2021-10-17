<h1 align="center">Host Downloader</h1>

<p align="center">
    Download files hosted on the web
</p>

---

## Usage

### Managing hosts

#### host:auth

Used to locally store your credentials into application.
It allows to unlock all your premium features while downloading.

```shell
host-downloader host:auth SomeHost user_name p455w0rd
```

```
Connected to SomeHost
```

#### host:revoke

```shell
host-downloader host:revoke SomeHost
```

```
Disconnected of SomeHost
```

#### host:status

```shell
host-downloader host:status
```

```
Host: SomeHost
Login: Not configured
```

### Retrieving infos and downloading files

#### url:info

```shell
host-downloader url:info https://some-host.com/hashcode
```

```
Host: SomeHost
File name: Some.file.name[1080p]
Size: 370.40 MB
State: Ready
```

#### url:download

```shell
host-downloader url:download https://some-host.com/hashcode /home/user/videos
```

```
Host: SomeHost
Name: Some.file.name[1080p]
File: /home/user/videos/Some.file.name[1080p].mkv
Size: 370.40 MB
[▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓░░░░░░░░░░░]  63% - 2 mins left
```
