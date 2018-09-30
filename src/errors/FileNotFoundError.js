module.exports = class FileNotFoundError extends Error {
    constructor (path) {
        super();

        this.message = 'File not found';
        this.path = path;
    }
};
