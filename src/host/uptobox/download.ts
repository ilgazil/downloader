import axios, { AxiosError, AxiosResponse } from 'axios';
import fs, { ReadStream } from 'fs';
import { authenticate } from './auth';
import LoginError from '../../errors/LoginError';
import ServerError from '../../errors/ServerError';
import { Auth, DownloadOptions } from '../../types';

export function download(url: string, options?: DownloadOptions): Promise<ReadStream> {
  if (!options?.auth) {
    return Promise.reject(new LoginError('Downloading as a guest is not supported'));
  }

  return authenticate(options.auth)
    .then((auth: Auth) => {
      const headers: any = {
        Cookie: auth.cookies.join('; '),
      };

      return axios
        .get(url, { responseType: 'stream', headers })

        .then((response: AxiosResponse<ReadStream>) => {
          response.data.pipe(fs.createWriteStream(options?.target || './download'));

          return response.data;
        })

        // @todo Add stack
        .catch((e: AxiosError) => {
          throw new ServerError('Unable to download file');
        })
      ;
    })
  ;
}
