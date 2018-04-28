
const fs = require('fs');
const path = require('path');
const winston = require('winston');

const http = require('./process/http');

/**
 * Recursively create a folder
 *
 * @param {string} destination
 */
function mkdir (destination) {
    if (fs.existsSync(destination)) {
        return;
    }

    const parent = path.dirname(destination);
    if (!fs.existsSync(parent)) {

        mkdir(parent);
    }

    winston.debug('Creating folder ' + destination);
    fs.mkdirSync(destination);
}

module.exports = {
    /**
     * Use http process to download
     *
     * @param {string} url
     * @param {string} destination
     * @param {Function} [onStarted]
     *
     * @return {{target: string, promise: Promise, cancel: (function())}}
     */
    http (url, destination, onStarted) {
        return http(url, destination, onStarted);
    },

    /**
     * Use default process to download, set in config file as downloader.client
     *
     * @param {string} url
     * @param {string} target
     * @param {Function} [onStarted]
     *
     * @return {{target: string, promise: Promise, cancel: (function())}}
     */
    process (url, target, onStarted) {
        if (fs.existsSync(target)) {
            fs.unlinkSync(target);
        }

        mkdir(path.dirname(target));

        return this[config.client](url, target, onStarted);
    }
};
