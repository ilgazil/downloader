import axios, { AxiosError, AxiosResponse } from 'axios';
import fs, { ReadStream } from 'fs';
import { ok, err } from 'neverthrow';
import { authenticate } from './auth';
import LoginError from '../../errors/LoginError';
import ServerError from '../../errors/ServerError';
import { Auth, DownloadOptions, DownloadResult } from '../../types';

export function download(url: string, options: DownloadOptions): Promise<DownloadResult> {
  return new Promise<DownloadResult>(async (resolve) => {
    if (!options.credentials) {
      return resolve(err(new LoginError('Downloading as a guest is not supported')));
    }

    const auth = await authenticate(options.credentials);

    if (auth.isErr()) {
      return resolve(err(auth._unsafeUnwrapErr()));
    }

    const headers: any = {};
    auth.map((auth: Auth) => {
      headers.Cookie = auth.cookies.join('; ');
    });

    return axios
      .get(url, { responseType: 'stream', headers })

      .then((response: AxiosResponse<ReadStream>) => {
        console.log(response.headers['Content-Length']);

        response.data.pipe(fs.createWriteStream(options.target));

        resolve(ok(response.data));
      })

      .catch((e: AxiosError) => resolve(err(new ServerError(`Unable to access file: ${e.message}`))))
    ;
  });
}