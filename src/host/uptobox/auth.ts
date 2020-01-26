import axios, {AxiosError} from 'axios';
import cookie from 'cookie';
import moment from 'moment';
import { err, ok } from 'neverthrow';
import { Credentials, AuthResult } from '../../types';
import LoginError from '../../errors/LoginError';

const LOGIN_URL: string = 'https://uptobox.com/login';
const expirationDateFormat = 'ddd, DD-MMM-YY HH:mm:ss zz';
let cachedCookies: string[] = [];

export function isCookieValid(definition: string): boolean {
  const parts = cookie.parse(definition);

  if ('Max-Age' in parts) {
    return !!parts['Max-Age'] && moment().add(parts['Max-Age'], 'seconds').valueOf() > moment().valueOf();
  }

  if (parts['expires']) {
    return moment(parts['expires'], expirationDateFormat).valueOf() > moment().valueOf();
  }

  return true;
}

export function createCookieJar(cookies: string[]): string[] {
  return cookies.map((cookie: string): string => {
    const parts = cookie.substr(0, cookie.indexOf(';')).split('=');
    return `${parts[0]}=${parts[1]}`;
  }, {});
}

export function authenticate(credentials: Credentials): Promise<AuthResult> {
  return new Promise<AuthResult>((resolve) => {
    if (cachedCookies.length) {
      if (!cachedCookies.every(isCookieValid)) {
        return resolve(ok({
          cookies: createCookieJar(cachedCookies),
        }));
      }
    }

    const body = new URLSearchParams();
    body.append('login', credentials.user);
    body.append('password', credentials.password);

    axios
      .post(
        LOGIN_URL,
        body,
        {
          headers: {'Content-Type': 'application/x-www-form-urlencoded' },
          maxRedirects: 0,
          validateStatus(status: number): boolean {
            return status === 302;
          },
        }
      )

      .then((response) => {
        cachedCookies = (response.headers['set-cookie'] as string[]).filter(isCookieValid);

        return resolve(ok({
          cookies: createCookieJar(cachedCookies),
        }));
      })

      // @todo handle credential errors and other errors?
      .catch((e: AxiosError) => resolve(err(new LoginError('Invalid credentials'))))
    ;
  });
}