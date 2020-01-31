import axios from 'axios';
import MockAdapter from 'axios-mock-adapter';
import moment from 'moment';
import { isCookieValid, authenticate } from './auth';
import LoginError from '../../errors/LoginError';
import ServerError from '../../errors/ServerError';

describe('Uptobox auth', () => {
  describe('::isCookieValid', () => {
    it('should deny cookies with no expiration info', () => {
      const cookie = `__cfduid=d6e559321306b4556788b8fcabff1defe1579705476; path=/; domain=.uptobox.com; HttpOnly; SameSite=Lax; Secure`;
      expect(isCookieValid(cookie)).toBe(false);
    });

    it('should allow up-to-date cookies through `expires` key', () => {
      const cookie = `__cfduid=d6e559321306b4556788b8fcabff1defe1579705476; expires=${moment().add(1, 'hour').format('ddd, DD-MMM-YY HH:mm:ss')} GMT; path=/; domain=.uptobox.com; HttpOnly; SameSite=Lax; Secure`;
      expect(isCookieValid(cookie)).toBe(true);
    });

    it('should allow up-to-date cookies through `Max-Age` key', () => {
      const cookie = '__cfduid=d6e559321306b4556788b8fcabff1defe1579705476; Max-Age=2000; path=/; domain=.uptobox.com; HttpOnly; SameSite=Lax; Secure';
      expect(isCookieValid(cookie)).toBe(true);
    });

    it('should deny outdated cookies', () => {
      const cookie = `__cfduid=d6e559321306b4556788b8fcabff1defe1579705476; expires=${moment().subtract(1, 'hour').format('ddd, DD-MMM-YY HH:mm:ss')} GMT; path=/; domain=.uptobox.com; HttpOnly; SameSite=Lax; Secure`;
      expect(isCookieValid(cookie)).toBe(false);
    });

    it('should deny revoked cookies through `Max-Age` key', () => {
      const cookie = '__cfduid=d6e559321306b4556788b8fcabff1defe1579705476; Max-Age=0; path=/; domain=.uptobox.com; HttpOnly; SameSite=Lax; Secure';
      expect(isCookieValid(cookie)).toBe(false);
    });
  });

  describe('::authenticate', () => {
    const mock = new MockAdapter(axios);
    const URL: string = 'https://uptobox.com/login';

    afterEach(() => {
      mock.reset();
    });

    it('should retrieve cookies on successful login', async () => {
      mock
        .onPost(URL)
        .reply(302, '', {
          'set-cookie': [
            `__cfduid=d5a3797632f239d82b800cc6a3a34b3951579699755; expires=${moment().add(1, 'month').format('ddd, DD-MMM-YY HH:mm:ss')} GMT; path=/; domain=.uptobox.com; HttpOnly; SameSite=Lax; Secure`,
            'xfss=deleted; expires=Thu, 01-Jan-1970 00:00:01 GMT; Max-Age=0',
            `xfss=agwciiyhxo5c8a9t; expires=${moment().add(31536000, 'seconds').format('ddd, DD-MMM-YY HH:mm:ss')} GMT; Max-Age=31536000; path=/; domain=.uptobox.com; secure`,
          ],
        })
      ;

      return expect(await authenticate({ user: 'login', password: 'password', noCache: true })).toMatchObject({
        cookies: [
          '__cfduid=d5a3797632f239d82b800cc6a3a34b3951579699755',
          'xfss=agwciiyhxo5c8a9t',
        ],
      });
    });

    it('should use a cache while cookies are up-to-date', async () => {
      mock
        .onPost(URL)
        .replyOnce(302, '', {
          'set-cookie': [
            `__cfduid=d5a3797632f239d82b800cc6a3a34b3951579699755; expires=${moment().add(1, 'month').format('ddd, DD-MMM-YY HH:mm:ss')} GMT; path=/; domain=.uptobox.com; HttpOnly; SameSite=Lax; Secure`,
            'xfss=deleted; expires=Thu, 01-Jan-1970 00:00:01 GMT; Max-Age=0',
            `xfss=agwciiyhxo5c8a9t; expires=${moment().add(31536000, 'seconds').format('ddd, DD-MMM-YY HH:mm:ss')} GMT; Max-Age=31536000; path=/; domain=.uptobox.com; secure`,
          ],
        })
      ;

      await authenticate({ user: 'login', password: 'password' });
      await authenticate({ user: 'login', password: 'password' });

      return expect(mock.history.post.length).toBe(1);
    });

    it('should fail with bad credentials', async () => {
      mock.onPost(URL).reply(200);

      return expect(authenticate({ user: 'bad', password: 'login', noCache: true })).rejects.toEqual(new LoginError('Invalid credentials'));
    });

    it('should fail with server error', async () => {
      mock.onPost(URL).reply(404);

      return expect(authenticate({ user: 'login', password: 'password', noCache: true })).rejects.toEqual(new ServerError('Unable to authenticate'));
    });
  });
});
