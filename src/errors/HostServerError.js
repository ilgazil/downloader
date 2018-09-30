module.exports = class HostServerError extends Error {
    constructor () {
        super();

        this.message = 'Host server error';
    }
};
