module.exports = class HostError extends Error {
    constructor (info) {
        super();

        this.message = 'Host error';
        this.info = info;
    }
};
