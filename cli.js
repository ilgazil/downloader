#!/usr/bin/env node

const program = require('commander');
const fs = require('fs');
const humanFormat = require('human-format');
const urlValidator = require('valid-url');
const winston = require('winston');

const lib = require('./index');

/**
 * Get urls from text file (space-like chars separated) if path is given
 *
 * @param {string} source
 *
 * @returns {string[]}
 */
function parseUrls (source) {
    let urls = [];

    if (fs.existsSync(source)) {
        urls = fs.readFileSync(source, 'utf8')
            .replace(/\s+/g, '\n')
            .split('\n')
            .filter(url => urlValidator.isUri(url));
    } else {
        urls.push(source);
    }

    winston.debug('Urls found:');
    winston.debug(urls);
    return urls;
}

program.version('1.0.0');

program
    .command('analyse <source>')
    .option('-v, --verbose')
    .action(function (source, cmd) {
        if (cmd.verbose) {
            winston.level = 'debug';
        }

        function analyse (urls) {
            const url = urls.shift();

            if (!url) {
                return;
            }

            winston.info('Analysing ' + url);

            lib.analyse(url)
                .then(result => {
                    winston.info(result);

                    analyse(urls);
                })
                .catch(error => {
                    winston.error('Error occurred: ' + error);
                    winston.error(error)
                });
        }

        analyse(parseUrls(source));
    });

program
    .command('download <source>')
    .option('-d, --destination [path]', 'Destination folder, if not exists, will be created')
    .option('-u, --user [user]', 'Premium account username')
    .option('-p, --password [password]', 'Premium account password (empty string if user specified and no password provided)')
    .option('-v, --verbose')
    .action(function (source, cmd) {
        if (cmd.verbose) {
            winston.level = 'debug';
        }

        const destination = cmd.destination || process.cwd();

        function download (urls) {
            const url = urls.shift();

            if (!url) {
                return;
            }

            const options = {};
            if (cmd.user) {
                options.premium = {
                    user: cmd.user,
                    password: cmd.password || ''
                }
            }

            let handle;
            let speed = 0;
            let lastSize = 0;

            winston.info('Downloading ' + url + ' in ' + destination);

            lib.download(url, destination, options)
                .then(({ target, stream }) => {
                    handle = setInterval(() => {
                        if (!fs.existsSync(target)) {
                            speed = lastSize = 0;
                            return;
                        }

                        const size = fs.statSync(target).size;
                        speed = size - lastSize;
                        lastSize = size;

                        winston.debug('Downloaded ' + humanFormat(size, {unit: 'o'}) + ' at ' + humanFormat(speed, {unit: 'o'}) + '/s');
                    }, 1000);

                    // @todo Use Promise.finally when released
                    stream
                        .then((target) => {
                            winston.info('Download complete: ' + target);

                            handle = clearInterval(handle);
                            download(urls);
                        })
                        .catch(error => {
                            winston.error('Error while downloading: ' + error);

                            handle = clearInterval(handle);
                            download(urls);
                        });
                })
                .catch(error => winston.error(error));
        }

        download(parseUrls(source));
    });

program.parse(process.argv);
