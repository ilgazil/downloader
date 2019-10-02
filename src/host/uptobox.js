const cheerio = require('cheerio');
const { URLSearchParams } = require('url');
const humanFormat = require('human-format');
const request = require('request-promise-native');
const winston = require('winston');
const fetch = require('node-fetch');

const download = require('../download');

const CredentialsError = require('../errors/CredentialsError');
const CooldownError = require('../errors/CooldownError');
const FileNotFoundError = require('../errors/FileNotFoundError');
const HostServerError = require('../errors/HostServerError');

const processes = [];

/**
 * Parse link page for name, size and errors
 *
 * @param {Function} $
 * @returns {Object}
 */
function parse ($) {
    const title = $('#dl h1').text();

    if (title.includes('File not found')) {
        throw new FileNotFoundError();
    }

    if (title.includes('Bad gateway')) {
        throw new HostServerError();
    }

    const $error = $('#dl + .red');

    if ($error.length) {
        throw new HostError($error.text());
    }

    const name = title.substring(0, title.lastIndexOf(' ('));
    let size = title.substring(title.lastIndexOf('(') + 1, title.lastIndexOf(')'));

    return {
        $,
        analyse: {
            name,
            size: humanFormat.parse(size)
        }
    };
}

/**
 * @param {Object} config
 * @param {string} config.user
 * @param {string} config.password
 *
 * @returns {Promise}
 */
function login ({ user, password }, url) {
    const hash = url.substr(url.lastIndexOf('/') + 1);

    console.log('https://uptobox.com/login?referer=' + hash, 'login=' + user + '&password=' + password);


    const params = new URLSearchParams();
    params.append('login', user);
    params.append('password', password);

    return fetch(
        'https://uptobox.com/login?referer=' + hash,
        {
            method: 'POST',
            body: params,
            redirect: 'manual',
        }
    ).then((response) => {
        if (response.status !== 302) {
            throw new CredentialsError(user, password);
        }

        const cookies = response.headers.raw()['set-cookie'].reduce((cookies, cookie) => {
            cookies[cookie.substr(0, cookie.indexOf('='))] = cookie.substring(cookie.indexOf('=') + 1, cookie.indexOf(';'));
            return cookies;
        }, {});

        return fetch(
            url,
            {
                method: 'GET',
                headers: { 'Cookie': Object.keys(cookies).map((name) => name + '=' + cookies[name]).join('; ') },
            }
        ).then((response) => response.url);
    });
}

module.exports = {
    name: 'uptobox',

    /**
     * Search for the host corresponding to the url given
     *
     * @param {string} url
     *
     * @return {boolean}
     */
    match (url) {
        return url.indexOf('//uptobox.com/') > 0;
    },

    /**
     * Analyse an url, and try to provide file name and size
     *
     * @param {string} url
     *
     * @returns {Promise}
     */
    analyse (url) {
        const options = {
            method: 'GET',
            url: url,
            transform: body => {
                return cheerio.load(body);
            }
        };

        winston.debug('Analysing ' + url);

        return request(options)
            .then(parse)
            .then(({ analyse }) => analyse)
            .catch(error => {
                error.path = url;

                throw error;
            });
    },

    /**
     * Process the download
     *
     * @param {string} url
     * @param {string} destination
     * @param {Object} options
     *
     * @returns {Promise}
     *
     * @todo See how to handle sessions
     */
    download (url, destination, options) {
        return (new Promise((resolve, reject) => {
            if (!options.premium) {
                reject('Only premium is supported');
                return;
            }

            const process = {
                url: url,
                request: login(options.premium || {}, url)
                    .then((url) => {
                        resolve({
                            name: url.substr(url.lastIndexOf('/') + 1),
                            size: 0,
                            url
                        });
                    })
                    .catch(error => {
                        error.path = url;
                        reject(error);
                    })
            };

            processes.push(process);

            // Regular user part (not premium)
            // process.request = request({
            //     method: 'GET',
            //     url: url,
            //     jar: true,
            //     transform: body => {
            //         return cheerio.load(body);
            //     }
            // })
            //     .then(($) => {
            //
            //     })
            //     .then(parse)
            //     .then(({ $, analyse }) => {
            //         const $link = $('#dl a.big-button-green-flat');
            //
            //         if ($link.hasClass('disabled')) {
            //             const cooldown = /[^\d]+(\d+\sh[^\d]+)?(\d+\smin[^\d]+)?(\d+\ssec)?/
            //                 .exec($link.siblings('div.countdown').text())
            //                 .reduce((reduced, current) => {
            //                     if (!current || current === $link.siblings('div.countdown').text()) {
            //                         return reduced;
            //                     }
            //
            //                     if (current.includes('h')) {
            //                         reduced.hours = parseInt(current);
            //                     } else if (current.includes('min')) {
            //                         reduced.minutes = parseInt(current);
            //                     } else if (current.includes('sec')) {
            //                         reduced.seconds = parseInt(current);
            //                     }
            //
            //                     return reduced;
            //                 }, {})
            //             ;
            //
            //             throw new CooldownError(cooldown);
            //         }
            //
            //         const url = $link.attr('href');
            //
            //         if (!url) {
            //             throw 'Unable to find link in page';
            //         }
            //
            //         resolve({
            //             ...analyse,
            //             url
            //         });
            //     })
            //     .catch(error => {
            //         error.path = url;
            //         reject(error);
            //     });
        }))
        .then(({ url, name }) => {
            console.log('processing download of ' + url);
            return {
                target: destination + '/' + name,
                stream: download(url, destination + '/' + name)
            };
        });
    }
};
