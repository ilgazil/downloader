# oyster

![Build on Travis CI](https://travis-ci.org/ilgazil/oyster.svg?branch=master) [![Code coverage with CodeCov](https://codecov.io/gh/ilgazil/oyster/branch/master/graph/badge.svg)](https://codecov.io/gh/ilgazil/oyster)

Analyse urls and retrieve actual file download url from supported hosts (listed below).

**Note: This project is still in development and is not fully ready. API signature can still change!**

## Install

Using npm

```
npm i -S git+https://git@github.com/ilgazil/oyster.git
```

## API definition

### getHost

Identify an host with a url.

```javascript
import oyster from 'oyster'
 
console.log(oyster.getHost('http://uptobox.com/randomhash')); // uptobox
console.log(oyster.getHost('http://no-host.com/randomhash')); // Prints an empty string
```

### use

Register a custom host. If identifier match an existing one, it is replaced.

```javascript
import oyster from 'oyster'

oyster.use({
  identifier: 'stub',
  match(url: string): boolean {
    // ...
  },
  info(url: string): Promise<Info> {
    // ...
  },
  download(url: string, options: DownloadOptions): Promise<ReadStream> {
    // ...
  },
});
```

### info

Get file infos from host.

```javascript
import oyster from 'oyster'

oyster
  .info('http://uptobox.com/randomhash')
  .then((info) => console.log)
;
// {
//   url: 'http://uptobox.com/randomhash',
//   filename: 'My.Winter.Holidays.Video.mp4',
//   size: '289.60 MB',
//   cooldown: 0
// }
```

### download

Get download stream so you can stop or pause it if you need.

```javascript
import oyster from 'oyster'
 
oyster
  .download(
    'https://uptobox.com/randomhash', 
    {
      credentials: {
        user: 'login',
        password: 'pass',
      },
      target: './download.mp4'
    }
  )
  .then((stream) => {
    stream.on('close', () => console.log('File downloaded!'));
  })
;
```

## Supported hosts

:closed_lock_with_key: Supports premium account

* [uptobox](http://uptobox.com/)  :closed_lock_with_key:
