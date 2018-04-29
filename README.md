# oyster

Analyse urls and retrieve actual file download url from supported hosts (listed below).
If url does not match any host, oyster will try to download the url directly.

## Install

NPM
```
npm i -S git+https://git@github.com/ilgazil/oyster.git
```

## Usage

### As module

```javascript
import oyster from 'oyster'
 
oyster
  .analyse('https://uptobox.com/somerandomhash')
  .then(analyse => {
    console.log(analyse.host) // uptobox
    console.log(analyse.name) // Prints My.Winter.Holidays.Video
    console.log(analyse.size) // Prints 365220000
  })
```

```javascript
import oyster from 'oyster'
 
oyster
  .download(
    'https://uptobox.com/somerandomhash', 
    '/home/user/dl',
    {
      config: {
        premium: {
          login: 'premiumuser',
          password: 'premiumpass'
        }
      }
    }
  )
  .then(({ target, promise, cancel }) => {
    // /home/user/dl/My.Winter.Holidays.Video.mkv
    console.log('File downloading in', target)

    promise
      .then(() => {
        console.log('File downloaded!')
      })
      .catch(error => {
        console.log('Error occurred:', error)
      })

    cancel() // Cancel the download (will trigger promise.resolve)
  })
```

## Supported hosts

 :closed_lock_with_key: Supports premium account

* [uptobox](http://uptobox.com/)  :closed_lock_with_key: