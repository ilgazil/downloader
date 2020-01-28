import axios from 'axios';
import MockAdapter from 'axios-mock-adapter';
import { ReadStream, WriteStream } from 'fs';
import { ok } from 'neverthrow';
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

    // @todo https://github.com/ctimmerm/axios-mock-adapter/issues/153
    // it('should return stream', async () => {
    //   const MockedReadStream = jest.fn<ReadStream, any>();
    //   const mockedReadStream = new MockedReadStream();

    //   mock
    //     .onPost('https://uptobox.com/login')
    //     .reply(302, '', {
    //       'set-cookie': [],
    //     })
    //   ;

    //   mock
    //     .onGet(URL)
    //     .reply(200, mockedReadStream)
    //   ;

    //   const result: DownloadResult = await download(URL, { credentials: { user: 'foo', password: 'bar' } });

    //   expect(result).toEqual(ok(mockedReadStream));
    // });

    it('should deny guest queries', async () => {
      mock
        .onGet(URL)
        .reply(200, template)
      ;

      const result: DownloadResult = await download(URL);

      expect(result.isErr()).toBe(true);
      expect(result._unsafeUnwrapErr()).toMatchObject(new LoginError('Downloading as a guest is not supported'));
    });
  });
});
