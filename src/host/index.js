const hosts = [
    require('./uptobox'),
    require('./default'),
];

module.exports = {
    /**
     * Search for the host corresponding to the url given
     *
     * @param {string} url
     *
     * @return {boolean}
     */
    getHost (url) {
        return hosts.find(host => host.match(url));
    },

    /**
     * Analyse an url, and try to provide file name and size
     *
     * @param {string} url
     *
     * @returns {Promise}
     */
    analyse (url) {
        const host = this.getHost(url);

        if (!host) {
            return Promise.reject('No host found for ' + url);
        }

        return host.analyse(url)
            .then(analyse => {
                return {
                    host: host.name,
                    ...analyse
                }
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
     */
    download (url, destination, options) {
        const host = this.getHost(url);

        if (!host) {
            return Promise.reject('No host found for ' + url);
        }

        return host.download(url, destination, options);
    }
};
