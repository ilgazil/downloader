import axios, {AxiosError} from 'axios';
import cookie from 'cookie';
import moment from 'moment';
import { AuthOptions, Auth } from '../../types';
import LoginError from '../../errors/LoginError';
import ServerError from '../../errors/ServerError';
import InvalidArgumentError from 'src/errors/InvalidArgumentError';

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

  return false;
}

export function createCookieJar(cookies: string[]): string[] {
  return cookies.map((cookie: string): string => {
    const parts = cookie.substr(0, cookie.indexOf(';')).split('=');
    return `${parts[0]}=${parts[1]}`;
  }, {});
}

export function authenticate(options?: AuthOptions): Promise<Auth> {
  if (!options.noCache && cachedCookies.length && cachedCookies.every(isCookieValid)) {
    return Promise.resolve({
      cookies: createCookieJar(cachedCookies),
    });
  }

  const body = new URLSearchParams();
  body.append('login', options.user);
  body.append('password', options.password);

  return axios
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
      const cookies = (response.headers['set-cookie'] as string[]).filter(isCookieValid);

      if (!options.noCache) {
        cachedCookies = cookies;
      }

      return Promise.resolve({
        cookies: createCookieJar(cookies),
      });
    })

    // @todo Add stack
    .catch((e: AxiosError) => {
      // @todo Refine error on searching through dom
      if (e.response.status === 200) {
        throw new LoginError('Invalid credentials');
      }

      throw new ServerError('Unable to authenticate');
    })
  ;
}