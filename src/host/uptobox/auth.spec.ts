import axios from 'axios';
import moment from 'moment';
import MockAdapter from 'axios-mock-adapter';
import { isCookieValid, authenticate } from './auth';
import { Auth, AuthResult } from '../../types';
import LoginError from '../../errors/LoginError';

describe('Uptobox auth', () => {
  describe('::isCookieValid', () => {
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

    afterEach(() => {
      mock.restore();
    });

    it('should retrieve cookies on successful login', async () => {
      mock
        .onPost('https://uptobox.com/login')
        .reply(302, '', {
          'set-cookie': [
            `__cfduid=d5a3797632f239d82b800cc6a3a34b3951579699755; expires=${moment().add(1, 'month').format('ddd, DD-MMM-YY HH:mm:ss')} GMT; path=/; domain=.uptobox.com; HttpOnly; SameSite=Lax; Secure`,
            'xfss=deleted; expires=Thu, 01-Jan-1970 00:00:01 GMT; Max-Age=0',
            `xfss=agwciiyhxo5c8a9t; expires=${moment().add(31536000, 'seconds').format('ddd, DD-MMM-YY HH:mm:ss')} GMT; Max-Age=31536000; path=/; domain=.uptobox.com; secure`,
          ],
        })
      ;

      const auth: AuthResult = await authenticate({ user: 'bad', password: 'login' });

      expect(auth.isOk()).toBe(true);
      expect(auth._unsafeUnwrap()).toEqual({
        cookies: [
          '__cfduid=d5a3797632f239d82b800cc6a3a34b3951579699755',
          'xfss=agwciiyhxo5c8a9t',
        ],
      });
    });

    it('should fail with bad credentials', async () => {
      mock
        .onPost('https://uptobox.com/login')
        .reply(200, '')
      ;

      const auth: AuthResult = await authenticate({ user: 'bad', password: 'login' });

      expect(auth.isErr()).toBe(true);
      expect(auth._unsafeUnwrapErr()).toBeInstanceOf(LoginError);
      expect(auth._unsafeUnwrapErr().message).toEqual('Invalid credentials');
    });
  });
});
