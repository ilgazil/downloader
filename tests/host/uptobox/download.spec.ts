import axios from 'axios';
import MockAdapter from 'axios-mock-adapter';
import { ReadStream, WriteStream } from 'fs';
import { download } from '../../../src/host/uptobox/download';
import LoginError from '../../../src/errors/LoginError';
import ServerError from '../../../src/errors/ServerError';
import { DownloadOptions } from '../../../src/types';

describe('Uptobox download', () => {
  describe('::download', () => {
    const mock = new MockAdapter(axios);
    const URL = 'https://uptobox.com/randomhash';

    afterEach(() => {
      mock.reset();
    });

    it('should deny guest queries', async () => {
      mock.onGet(URL).reply(200);

      return expect(download(URL)).rejects.toEqual(new LoginError('Downloading as a guest is not supported'));
    });

    it('should fail with page is not found', async () => {
      // @todo Possible to mock auth module?
      mock
        .onPost('https://uptobox.com/login')
        .reply(302, '', {
          'set-cookie': [],
        })
      ;

      mock.onGet(URL).reply(404);

      const options: DownloadOptions = {
        auth: {
          user: 'login',
          password: 'password',
        },
      }

      return expect(download(URL, options)).rejects.toEqual(new ServerError('Unable to download file'));
    });

    // @todo https://github.com/ctimmerm/axios-mock-adapter/issues/153
    // it('should return stream', async () => {
    //   const MockedReadStream = jest.fn<ReadStream, any>();
    //   const mockedReadStream = new MockedReadStream();

    //   // @todo Possible to mock auth module?
    //   mock
    //     .onPost('https://uptobox.com/login')
    //     .reply(302, '', {
    //       'set-cookie': [],
    //     })
    //   ;

    //   mock.onGet(URL).reply(200, mockedReadStream);

    //   expect(await download(URL, { credentials: { user: 'user', password: 'password' } })).toBe(mockedReadStream);
    // });
  });
});
