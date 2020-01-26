"use strict";
var __importDefault = (this && this.__importDefault) || function (mod) {
    return (mod && mod.__esModule) ? mod : { "default": mod };
};
Object.defineProperty(exports, "__esModule", { value: true });
const axios_1 = __importDefault(require("axios"));
const htmlparser2_1 = require("htmlparser2");
const neverthrow_1 = require("neverthrow");
function parseFileInfo(html) {
    let isInTitle = false;
    const contents = [];
    const parser = new htmlparser2_1.Parser({
        onopentag(name, attribs) {
            if (name === 'h1' && 'class' in attribs && attribs.class.split(' ').indexOf('file-title') > -1) {
                isInTitle = true;
            }
        },
        ontext(text) {
            isInTitle && contents.push(text);
        },
        onclosetag(name) {
            if (name === 'h1' && isInTitle) {
                isInTitle = false;
            }
        }
    });
    parser.parseChunk(html);
    const parse = /(.*)\s+\((\d+\.?\d*\s\w+)\)/.exec(contents.join(''));
    if (!parse || parse.length < 3) {
        return neverthrow_1.err(new Error('Unable to parse page title'));
    }
    return neverthrow_1.ok({
        filename: parse[1],
        size: parse[2],
    });
}
function parseCooldownInfo(html) {
    let isInSpan = false;
    const contents = [];
    const parser = new htmlparser2_1.Parser({
        onopentag(name, attribs) {
            if (name === 'span' && 'class' in attribs && attribs.class.split(' ').indexOf('red') > -1) {
                isInSpan = true;
            }
        },
        ontext(text) {
            if (isInSpan) {
                text = text.trim();
                text && contents.push(text);
            }
        },
        onclosetag(name) {
            if (name === 'span' && isInSpan) {
                isInSpan = false;
            }
        }
    });
    parser.parseChunk(html);
    const parse = /you can wait\s((\d+)\sdays?\s?)?((\d+)\shours?\s?)?((\d+)\sminutes?\s?)?((\d+)\sseconds?)?/.exec(contents.join(' '));
    if (!parse) {
        return neverthrow_1.ok(0);
    }
    return neverthrow_1.ok((~~parse[2] || 0) * 60 * 60 * 24 + // Days part
        (~~parse[4] || 0) * 60 * 60 + // Hours part
        (~~parse[6] || 0) * 60 + // Minutes part
        (~~parse[8] || 0) // Seconds part
    );
}
function info(url) {
    return new Promise((resolve) => {
        axios_1.default
            .get(url, { maxRedirects: 0 })
            .then((response) => {
            const infos = {
                url,
                filename: '',
                size: '',
                cooldown: 0,
            };
            const fileInfoResult = parseFileInfo(response.data);
            if (fileInfoResult.isErr()) {
                return resolve(neverthrow_1.err(fileInfoResult._unsafeUnwrapErr()));
            }
            fileInfoResult.map((info) => Object.assign(infos, info));
            parseCooldownInfo(response.data).map((cooldown) => Object.assign(infos, { cooldown }));
            return resolve(neverthrow_1.ok(infos));
        })
            .catch((e) => resolve(neverthrow_1.err(e)));
    });
}
exports.info = info;
//# sourceMappingURL=info.js.map