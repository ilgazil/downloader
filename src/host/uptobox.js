const cheerio = require('cheerio');
const humanFormat = require('human-format');
const request = require('request-promise-native');
const winston = require('winston');

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
 * Login to host if credentials are given
 *
 * @param {Object} [config]
 * @param {string} [config.user]
 * @param {string} [config.password]
 *
 * @returns {Promise}
 */
function login ({ user, password }) {
    if (!user) {
        return Promise.resolve();
    }

    return request({
        method: 'POST',
        url: 'https://uptobox.com/?op=login',
        jar: true,
        followAllRedirects: true,
        formData: {
            login: user,
            password
        }
    }).catch(error => {
        throw new CredentialsError(user, password, error);
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
        return new Promise((resolve, reject) => {
            const process = {
                url: url,
                request: login(options.premium || {})
                    .then(() => {
                        process.request = request({
                            method: 'GET',
                            url: url,
                            jar: true,
                            transform: body => {
                                return cheerio.load(body);
                            }
                        })
                            .then(parse)
                            .then(({ $, analyse }) => {
                                const $link = $('#dl a.big-button-green-flat');

                                if ($link.hasClass('disabled')) {
                                    const cooldown = /[^\d]+(\d+\sh[^\d]+)?(\d+\smin[^\d]+)?(\d+\ssec)?/
                                        .exec($link.siblings('div.countdown').text())
                                        .reduce((reduced, current) => {
                                            if (!current || current === $link.siblings('div.countdown').text()) {
                                                return reduced;
                                            }

                                            if (current.includes('h')) {
                                                reduced.hours = parseInt(current);
                                            } else if (current.includes('min')) {
                                                reduced.minutes = parseInt(current);
                                            } else if (current.includes('sec')) {
                                                reduced.seconds = parseInt(current);
                                            }

                                            return reduced;
                                        }, {})
                                    ;

                                    throw new CooldownError(cooldown);
                                }

                                const url = $link.attr('href');

                                if (!url) {
                                    throw 'Unable to find link in page';
                                }

                                resolve({
                                    ...analyse,
                                    url
                                });
                            })
                            .catch(error => {
                                error.path = url;
                                reject(error);
                            });
                    })
                    .catch(error => {
                        error.path = url;
                        reject(error);
                    })
            };

            processes.push(process);
        })
        .then(({ url, name }) => {
            return {
                target: destination + '/' + name,
                stream: download(url, destination + '/' + name)
            };
        });
    }
};
