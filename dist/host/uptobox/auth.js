"use strict";
var __importDefault = (this && this.__importDefault) || function (mod) {
    return (mod && mod.__esModule) ? mod : { "default": mod };
};
Object.defineProperty(exports, "__esModule", { value: true });
const axios_1 = __importDefault(require("axios"));
const cookie_1 = __importDefault(require("cookie"));
const moment_1 = __importDefault(require("moment"));
const neverthrow_1 = require("neverthrow");
const LoginError_1 = __importDefault(require("../../errors/LoginError"));
const LOGIN_URL = 'https://uptobox.com/login';
const expirationDateFormat = 'ddd, DD-MMM-YY HH:mm:ss zz';
let cachedCookies = [];
function isCookieValid(definition) {
    const parts = cookie_1.default.parse(definition);
    if ('Max-Age' in parts) {
        return !!parts['Max-Age'] && moment_1.default().add(parts['Max-Age'], 'seconds').valueOf() > moment_1.default().valueOf();
    }
    if (parts['expires']) {
        return moment_1.default(parts['expires'], expirationDateFormat).valueOf() > moment_1.default().valueOf();
    }
    return true;
}
exports.isCookieValid = isCookieValid;
function createCookieJar(cookies) {
    return cookies.map((cookie) => {
        const parts = cookie.substr(0, cookie.indexOf(';')).split('=');
        return `${parts[0]}=${parts[1]}`;
    }, {});
}
exports.createCookieJar = createCookieJar;
function authenticate(credentials) {
    return new Promise((resolve) => {
        if (cachedCookies.length) {
            if (!cachedCookies.every(isCookieValid)) {
                return resolve(neverthrow_1.ok({
                    cookies: createCookieJar(cachedCookies),
                }));
            }
        }
        const body = new URLSearchParams();
        body.append('login', credentials.user);
        body.append('password', credentials.password);
        axios_1.default
            .post(LOGIN_URL, body, {
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            maxRedirects: 0,
            validateStatus(status) {
                return status === 302;
            },
        })
            .then((response) => {
            cachedCookies = response.headers['set-cookie'].filter(isCookieValid);
            return resolve(neverthrow_1.ok({
                cookies: createCookieJar(cachedCookies),
            }));
        })
            // @todo handle credential errors and other errors?
            .catch((e) => resolve(neverthrow_1.err(new LoginError_1.default('Invalid credentials'))));
    });
}
exports.authenticate = authenticate;
//# sourceMappingURL=auth.js.map