const download = require('download');
const fs = require('fs');
const path = require('path');
const winston = require('winston');

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

/**
 * Use default process to download, set in config file as downloader.client
 *
 * @param {string} url
 * @param {string} target
 *
 * @return {Object}
 */
function process (url, target) {
    if (fs.existsSync(target)) {
        fs.unlinkSync(target);
    }

    mkdir(path.dirname(target));

    winston.debug('Download of ' + url + ' in ' + target);

    const stream = download(url);

    stream.pipe(fs.createWriteStream(target), error => {
        error && winston.error('Error downloading ' + target + ': ' + error);
    });

    return stream;
}

module.exports = process;
