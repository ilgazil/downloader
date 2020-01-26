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
var __importStar = (this && this.__importStar) || function (mod) {
    if (mod && mod.__esModule) return mod;
    var result = {};
    if (mod != null) for (var k in mod) if (Object.hasOwnProperty.call(mod, k)) result[k] = mod[k];
    result["default"] = mod;
    return result;
};
var __importDefault = (this && this.__importDefault) || function (mod) {
    return (mod && mod.__esModule) ? mod : { "default": mod };
};
Object.defineProperty(exports, "__esModule", { value: true });
const neverthrow_1 = require("neverthrow");
const uptobox = __importStar(require("./uptobox"));
const InvalidArgumentError_1 = __importDefault(require("../errors/InvalidArgumentError"));
const hosts = [
    uptobox,
];
function getHost(url) {
    const matchingHost = hosts.find((host) => host.match(url));
    if (!matchingHost) {
        return neverthrow_1.err(`No matching host for url ${url}`);
    }
    return neverthrow_1.ok(matchingHost.identifier);
}
exports.getHost = getHost;
function info(url) {
    return __awaiter(this, void 0, void 0, function* () {
        const matchingHost = hosts.find((host) => host.match(url));
        if (!matchingHost) {
            return neverthrow_1.err(new InvalidArgumentError_1.default(`No matching host for url ${url}`));
        }
        return matchingHost.info(url);
    });
}
exports.info = info;
function download(url, options) {
    return __awaiter(this, void 0, void 0, function* () {
        const matchingHost = hosts.find((host) => host.match(url));
        if (!matchingHost) {
            return neverthrow_1.err(new InvalidArgumentError_1.default(`No matching host for url ${url}`));
        }
        return matchingHost.download(url, options);
    });
}
exports.download = download;
//# sourceMappingURL=index.js.map