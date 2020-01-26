import axios from 'axios';
import MockAdapter from 'axios-mock-adapter';
import { info } from './info';
import { InfoResult } from '../../types';
import template from './___tests/info-cooldown.html';

describe('Uptobox info', () => {
  describe('::info', () => {
    const mock = new MockAdapter(axios);

    afterEach(() => {
      mock.restore();
    });

    it('should extract infos from html', async () => {
      const url = 'https://uptobox.com/h5uefckv1gsm';

      mock
        .onGet(url)
        .reply(200, template)
      ;

      const infos: InfoResult = await info(url);

      expect(infos.isOk()).toBe(true);
      expect(infos._unsafeUnwrap()).toEqual({
        url,
        filename: 'Kimetsu.no.Yaiba.E01.VOSTFR.HD.x264-Time2Watch.mp4',
        size: '361.68 MB',
        cooldown: 96310,
      });
    });
  });
});
