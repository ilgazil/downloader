import axios from 'axios';
import MockAdapter from 'axios-mock-adapter';
import { download } from './download';
import { DownloadResult } from '../../types';
import LoginError from '../../errors/LoginError';
import template from './___tests/info-cooldown.html';

describe('Uptobox download', () => {
  describe('::download', () => {
    const mock = new MockAdapter(axios);
    const URL = 'https://uptobox.com/hash';

    afterEach(() => {
      mock.restore();
    });

    it('should deny guest queries', async () => {
      mock
        .onGet(URL)
        .reply(200, template)
      ;

      const result: DownloadResult = await download(URL, { target: '/noop' });

      expect(result.isErr()).toBe(true);
      expect(result._unsafeUnwrapErr()).toBeInstanceOf(LoginError);
      expect(result._unsafeUnwrapErr().message).toBe('Downloading as a guest is not supported');
    });
  });
});
