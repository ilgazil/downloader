"use strict";
var __awaiter = (this && this.__awaiter) || function (thisArg, _arguments, P, generator) {
    function adopt(value) { return value instanceof P ? value : new P(function (resolve) { resolve(value); }); }
    return new (P || (P = Promise))(function (resolve, reject) {
        function fulfilled(value) { try { step(generator.next(value)); } catch (e) { reject(e); } }
        function rejected(value) { try { step(generator["throw"](value)); } catch (e) { reject(e); } }
        function step(result) { result.done ? resolve(result.value) : adopt(result.value).then(fulfilled, rejected); }
        step((generator = generator.apply(thisArg, _arguments || [])).next());
    });
};
var __importDefault = (this && this.__importDefault) || function (mod) {
    return (mod && mod.__esModule) ? mod : { "default": mod };
};
Object.defineProperty(exports, "__esModule", { value: true });
const axios_1 = __importDefault(require("axios"));
const fs_1 = __importDefault(require("fs"));
const neverthrow_1 = require("neverthrow");
const auth_1 = require("./auth");
const LoginError_1 = __importDefault(require("../../errors/LoginError"));
const ServerError_1 = __importDefault(require("../../errors/ServerError"));
function download(url, options) {
    return new Promise((resolve) => __awaiter(this, void 0, void 0, function* () {
        var _a;
        if (!((_a = options) === null || _a === void 0 ? void 0 : _a.credentials)) {
            return resolve(neverthrow_1.err(new LoginError_1.default('Downloading as a guest is not supported')));
        }
        const auth = yield auth_1.authenticate(options.credentials);
        if (auth.isErr()) {
            return resolve(neverthrow_1.err(auth._unsafeUnwrapErr()));
        }
        const headers = {};
        auth.map((auth) => {
            headers.Cookie = auth.cookies.join('; ');
        });
        return axios_1.default
            .get(url, { responseType: 'stream', headers })
            .then((response) => {
            var _a;
            response.data.pipe(fs_1.default.createWriteStream(((_a = options) === null || _a === void 0 ? void 0 : _a.target) || './download'));
            resolve(neverthrow_1.ok(response.data));
        })
            .catch((e) => resolve(neverthrow_1.err(new ServerError_1.default(`Unable to access file: ${e.message}`))));
    }));
}
exports.download = download;
//# sourceMappingURL=download.js.map