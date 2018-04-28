const downloader = require('../downloader/index');

module.exports = {
    name: 'default',

    /**
     * Search for the host corresponding to the url given
     *
     * @param {string} url
     *
     * @return {boolean}
     */
    match (url) {
        return true;
    },

    /**
     * Analyse an url, and try to provide file name and size
     *
     * @param {string} url
     *
     * @returns {Promise}
     */
    analyse (url) {
        return Promise.resolve({
            name: url,
            size: 0
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
     *
     * @todo Get a name from url
     */
    download (url, destination, config) {
        return Promise.resolve(downloader.http(url, destination + '/download-' + (new Date()).getTime()))
    }
};
