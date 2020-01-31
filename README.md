# oyster

![Build on Travis CI](https://travis-ci.org/ilgazil/oyster.svg?branch=master) [![Code coverage with CodeCov](https://codecov.io/gh/ilgazil/oyster/branch/master/graph/badge.svg)](https://codecov.io/gh/ilgazil/oyster)

Analyse urls and retrieve actual file download url from supported hosts (listed below).

**Note: This project is still in development and is not fully ready. API signature can still change!**

## Install

Using npm

```
npm i -S git+https://git@github.com/ilgazil/oyster.git
```

## Usage

```javascript
import oyster from 'oyster'
 
oyster.getHost('http://uptobox.com/randomhash').map(console.log); // uptobox
oyster.getHost('http://no-host.com/randomhash').mapErr(console.log); // Prints a stringified InvalidArgumentError
```

```javascript
import oyster from 'oyster'

oyster
  .info('http://uptobox.com/randomhash')
  .then((info) => info.map(console.log))
;
// {
//   url: 'http://uptobox.com/randomhash',
//   filename: 'My.Winter.Holidays.Video.mp4',
//   size: '289.60 MB',
//   cooldown: 0
// }
```

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
  .then((download) => {
    if (download.isOk()) {
      download.map((stream) => stream.on('close', () => console.log('File downloaded!')));
    } else {
      download.mapErr(console.log);
    }
  })
;
```

## Supported hosts

:closed_lock_with_key: Supports premium account

* [uptobox](http://uptobox.com/)  :closed_lock_with_key:
