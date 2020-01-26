import axios, { AxiosError } from 'axios';
import { Parser } from 'htmlparser2';
import { Result, err, ok } from 'neverthrow';
import { Info, InfoResult } from '../../types';

function parseFileInfo(html: string): Result<{ filename: string, size: string }, Error> {
  let isInTitle = false;
  const contents: string[] = [];

  const parser = new Parser({
    onopentag(name, attribs) {
      if (name === 'h1' && 'class' in attribs && attribs.class.split(' ').indexOf('file-title') > -1) {
        isInTitle = true;
      }
    },
    ontext(text: string) {
      isInTitle && contents.push(text);
    },
    onclosetag(name: string) {
      if (name === 'h1' && isInTitle) {
        isInTitle = false;
      }
    }
  });

  parser.parseChunk(html);

  const parse = /(.*)\s+\((\d+\.?\d*\s\w+)\)/.exec(contents.join(''));

  if (!parse || parse.length < 3) {
    return err(new Error('Unable to parse page title'));
  }

  return ok({
    filename: parse[1],
    size: parse[2],
  })
}

function parseCooldownInfo(html: string): Result<number, Error> {
  let isInSpan = false;
  const contents: string[] = [];

  const parser = new Parser({
    onopentag(name, attribs) {
      if (name === 'span' && 'class' in attribs && attribs.class.split(' ').indexOf('red') > -1) {
        isInSpan = true;
      }
    },
    ontext(text: string) {
      if (isInSpan) {
        text = text.trim();
        text && contents.push(text);
      }
    },
    onclosetag(name: string) {
      if (name === 'span' && isInSpan) {
        isInSpan = false;
      }
    }
  });

  parser.parseChunk(html);

  const parse = /you can wait\s((\d+)\sdays?\s?)?((\d+)\shours?\s?)?((\d+)\sminutes?\s?)?((\d+)\sseconds?)?/.exec(contents.join(' '));

  if (!parse) {
    return ok(0);
  }

  return ok(
    (~~parse[2] || 0) * 60 * 60 * 24 + // Days part
    (~~parse[4] || 0) * 60 * 60 + // Hours part
    (~~parse[6] || 0) * 60 + // Minutes part
    (~~parse[8] || 0) // Seconds part
  );
}

export function info(url: string): Promise<InfoResult> {
  return new Promise<InfoResult>((resolve) => {
    axios
      .get(url, { maxRedirects: 0 })

      .then((response) => {
        const infos: Info = {
          url,
          filename: '',
          size: '',
          cooldown: 0,
        };

        const fileInfoResult = parseFileInfo(response.data);

        if (fileInfoResult.isErr()) {
          return resolve(err(fileInfoResult._unsafeUnwrapErr()));
        }

        fileInfoResult.map((info) => Object.assign(infos, info));

        parseCooldownInfo(response.data).map((cooldown: number) => Object.assign(infos, { cooldown }));

        return resolve(ok(infos));
      })

      .catch((e: AxiosError) => resolve(err(e)))
    ;
  });
}
