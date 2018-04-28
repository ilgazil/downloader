const cheerio = require('cheerio');
const humanFormat = require('human-format');
const request = require('request-promise-native');
const winston = require('winston');

const downloader = require('../downloader/index');

const processes = [];

/**
 * Get the file real url and name
 *
 * @param {string} url
 * @param {Object} config
 *
 * @returns {Promise}
 * 
 * @todo Do not impose premium account
 * @todo See how to handle sessions
 */
function downloadInfo (url, config) {
    if (!config || !config.premium) {
        return Promise.reject('Only premium users can download on this host');
    }

    const options = {
        method: 'POST',
        url: 'https://uptobox.com/?op=login',
        jar: true,
        followAllRedirects: true,
        formData: {
            'login': config.premium.login,
            'password': config.premium.password
        }
    };

    return new Promise((resolve, reject) => {
        const process = {
            url: url,
            request: request(options)
                .then((response) => {
                    const options = {
                        method: 'GET',
                        url: url,
                        jar: true,
                        transform: body => {
                            return cheerio.load(body);
                        }
                    };

                    process.request = request(options)
                        .then($ => {
                            const url = $('#dl a.big-button-green-flat').attr('href');

                            if (!url) {
                                throw 'Unable to find link in page';
                            }

                            const title = $('#dl h1').text();
                            const size = title.substring(title.lastIndexOf('(') + 1, title.lastIndexOf(')'));

                            resolve({
                                url,
                                name : decodeURIComponent(url.substr(url.lastIndexOf('/') + 1)),
                                size: humanFormat.parse(size)
                            });
                        })
                        .catch(error => reject(error));
                })
                .catch(error => reject(error))
        };

        processes.push(process);
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
        return url.indexOf('http://uptobox.com') === 0;
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
            .then($ => {
                const title = $('#dl h1').text();

                const name = title.substring(0, title.lastIndexOf(' ('));
                let size = title.substring(title.lastIndexOf('(') + 1, title.lastIndexOf(')'));

                return {
                    name,
                    size: humanFormat.parse(size)
                };
            })
            .catch(error => {
                winston.error(error);
            });
    },

    /**
     * Process the download
     *
     * @param {string} url
     * @param {string} destination
     * @param {Object} config
     *
     * @returns {Promise}
     */
    download (url, destination, config) {

        return downloadInfo(url, config)
            .then(({ url, name }) => {
                return downloader.http(url, destination + '/' + name);
            })
    }
};
