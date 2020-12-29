import axios from 'axios';
import MockAdapter from 'axios-mock-adapter';
import { parseFileInfo, parseCooldownInfo, info } from '../../../src/host/uptobox/info';
import ServerError from '../../../src/errors/ServerError';
import ParsingError from '../../../src/errors/ParsingError';

describe('Uptobox info', () => {
  describe('::parseFileInfo', () => {
    it('should parse infos from html', () => {
      const html = '<html><body><div><h1 class="file-title">My.Winter.Holidays.Video.mp4 (289.60 MB)</h1></div></body></html>';

      expect(parseFileInfo(html)).toMatchObject({
        filename: 'My.Winter.Holidays.Video.mp4',
        size: '289.60 MB',
      });
    });

    it('should fail if title is not found', () => {
      const html = '<html><body><div><p class="file-title">My.Winter.Holidays.Video.mp4 (289.60 MB)</h1></div></body></html>';

      expect(parseFileInfo(html)).toEqual(new ParsingError('Title not found'));
    });
  });

  describe('::parseCooldownInfo', () => {
    it('should parse cooldown from html', () => {
      const html = '<html><body><div><span class="red"><i class="fa fa-times"></i>You need a PREMIUM account to download new files immediatly without waiting.<br />or you can wait 1 day 2 hours 45 minutes 10 seconds to launch a new download</span></div></body></html>';

      expect(parseCooldownInfo(html)).toBe(96310);
    });

    it('should return no cooldown if no cooldown if in html', () => {
      const html = '<html><body><div><span class="red"></span></div></body></html>';

      expect(parseCooldownInfo(html)).toBe(0);
    });
  });

  describe('::info', () => {
    const URL = 'https://uptobox.com/randomhash';
    const mock = new MockAdapter(axios);

    afterEach(() => {
      mock.reset();
    });

    it('should extract infos from html', async () => {
      const html = '<html><body><div><h1 class="file-title">My.Winter.Holidays.Video.mp4 (289.60 MB)</h1><span class="red"><i class="fa fa-times"></i>You need a PREMIUM account to download new files immediatly without waiting.<br />or you can wait 1 day 2 hours 45 minutes 10 seconds to launch a new download</span></div></body></html>';
      mock.onGet(URL).reply(200, html);

      expect(await info(URL)).toMatchObject({
        url: URL,
        filename: 'My.Winter.Holidays.Video.mp4',
        size: '289.60 MB',
        cooldown: 96310,
      });
    });

    it('should fail when page is not found', async () => {
      mock.onGet(URL).reply(404);

      return expect(info(URL)).rejects.toEqual(new ServerError('Unable to get infos'));
    });

    it('should fail when page is not complient', async () => {
      mock.onGet(URL).reply(200);

      return expect(info(URL)).rejects.toEqual(new ServerError('Unable to get infos'));
    });
  });
});
