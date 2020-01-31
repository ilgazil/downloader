import axios, { AxiosError } from 'axios';
import { Parser } from 'htmlparser2';
import { Info } from '../../types';
import ParsingError from '../../errors/ParsingError';
import ServerError from '../../errors/ServerError';

interface FileInfo {
  filename: string,
  size: string,
}

export function parseFileInfo(html: string): FileInfo | ParsingError {
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
    return new ParsingError('Title not found');
  }

  return {
    filename: parse[1],
    size: parse[2],
  };
}

export function parseCooldownInfo(html: string): number {
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
    return 0;
  }

  return (
    (~~parse[2] || 0) * 60 * 60 * 24 + // Days part
    (~~parse[4] || 0) * 60 * 60 + // Hours part
    (~~parse[6] || 0) * 60 + // Minutes part
    (~~parse[8] || 0) // Seconds part
  );
}

export function info(url: string): Promise<Info> {
  return axios
    .get(url)

    .then((response) => {
      const infos: Info = {
        url,
        filename: '',
        size: '',
        cooldown: 0,
      };

      const fileInfoResult = parseFileInfo(response.data);

      if (fileInfoResult instanceof ParsingError) {
        throw fileInfoResult;
      } else {
        Object.assign(infos, fileInfoResult);
      }

      infos.cooldown = parseCooldownInfo(response.data);

      return infos;
    })

    // @todo Add stack
    .catch((e: AxiosError) => {
      throw new ServerError('Unable to get infos');
    })
  ;
}
