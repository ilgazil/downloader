module.exports = class CredentialsError extends Error {
    constructor (user, password, serverError) {
        super();

        this.message ='Invalid credentials';
        this.user = user;
        this.password = password;
        this.serverError = serverError;
    }
};
