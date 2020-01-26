"use strict";
Object.defineProperty(exports, "__esModule", { value: true });
const fs_1 = require("fs");
class Download {
    constructor(path, size, stream) {
        this.path = path.substr(0, path.lastIndexOf('/'));
        this.filename = path.substr(path.lastIndexOf('/') + 1);
        this.size = size;
        this.stream = stream;
    }
    pause() {
        if (!this.stream.isPaused()) {
            this.stream.pause();
        }
    }
    resume() {
        if (this.stream.isPaused()) {
            this.stream.resume();
        }
    }
    stop() {
        this.stream.close();
        fs_1.unlinkSync(`${this.path}/${this.filename}`);
    }
}
exports.default = Download;
//# sourceMappingURL=download.js.map