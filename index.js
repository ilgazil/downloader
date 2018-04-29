const host = require('./src/host');

module.exports = {
    /**
     * Analyse a link a give information about file to download, depending on host
     *
     * @param {string} url
     *
     * @returns {Promise}
     */
    analyse (url) {
        return host.analyse(url);
    },

    /**
     * Download form url
     *
     * @param {string} url
     * @param {string} destination
     * @param {Object} options
     *
     * @returns {Promise}
     */
    download (url, destination, options) {
        return host.download(url, destination, options);
    }
};
