"use strict";
function __export(m) {
    for (var p in m) if (!exports.hasOwnProperty(p)) exports[p] = m[p];
}
Object.defineProperty(exports, "__esModule", { value: true });
__export(require("./auth"));
__export(require("./info"));
__export(require("./download"));
exports.identifier = 'uptobox';
function match(url) {
    return url.indexOf('//uptobox.com/') > 0;
}
exports.match = match;
//# sourceMappingURL=index.js.map