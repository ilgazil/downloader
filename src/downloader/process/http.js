const fs = require('fs');
const http = require('http');
const winston = require('winston');

/**
 * Start a download
 *
 * @param {string} url
 * @param {string} target
 * @param {Function} onStarted
 *
 * @return {{target: string, promise: Promise, cancel: (function())}}
 *
 * @todo Handle https. Use native https or request module?
 */
module.exports = (url, target, onStarted) => {
    let isCanceled = false;
    let delegate;

    winston.debug('Download of ' + url + ' in ' + target);

    return {
        target,

        promise: new Promise((resolve, reject) => {
            const file = fs.createWriteStream(target);

            delegate = http.get(url, response => {
                onStarted && onStarted({
                    size: +response.headers['content-length']
                });

                response.pipe(file);

                response.on('end', () => {
                    if (isCanceled) {
                        reject('Download has been canceled');
                    }

                    resolve(target);
                });

                response.on('error', error => {
                    reject(error);
                });
            });
        }),

        cancel () {
            winston.debug('Canceling download of ' + url);

            isCanceled = true
            delegate && delegate.abort();
        }
    };
};
