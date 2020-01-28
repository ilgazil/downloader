import { ReadStream } from 'fs';
import { ok } from 'neverthrow';
import { use, getHost, info, download } from './';
import * as uptobox from './uptobox';
import InvalidArgumentError from '../errors/InvalidArgumentError';
import { Host, Info, InfoResult, DownloadOptions, DownloadResult } from '../types';

class DummyReadStream extends ReadStream {};
const MockedReadStream = jest.fn<DummyReadStream, any>();

const infoStub: Info = {
  url: 'http://stub',
  filename: 'Some file',
  size: '700 MB',
  cooldown: 0,
};

const hostStub: Host = {
  identifier: 'stub',
  match(url: string): boolean {
    return url === infoStub.url;
  },
  info(url: string): Promise<InfoResult> {
    return Promise.resolve(ok(infoStub));
  },
  download(url: string, options: DownloadOptions): Promise<DownloadResult> {
    return Promise.resolve(ok(new MockedReadStream()));
  },
};

describe('getHost', () => {
  it('should match uptobox', () => {
    expect(getHost('http://uptobox.com/h5uefckv1gsm').isOk()).toBe(true);
    expect(getHost('http://uptobox.com/h5uefckv1gsm')._unsafeUnwrap()).toBe(uptobox.identifier);

    expect(getHost('https://uptobox.com/h5uefckv1gsm').isOk()).toBe(true);
    expect(getHost('https://uptobox.com/h5uefckv1gsm')._unsafeUnwrap()).toBe(uptobox.identifier);
  });

  it('should fail with unknown host', () => {
    expect(getHost('http://some-unknown-host/h5uefckv1gsm').isErr()).toBe(true);
    expect(getHost('http://some-unknown-host/h5uefckv1gsm')._unsafeUnwrapErr())
      .toEqual(new InvalidArgumentError(`No matching host for url http://some-unknown-host/h5uefckv1gsm`));
  });
});

describe('use', () => {
  it('should allow to add hosts', () => {
    use(hostStub);
    expect(getHost(infoStub.url).isOk()).toBe(true);
    expect(getHost(infoStub.url)._unsafeUnwrap()).toBe(hostStub.identifier);
  });
});

describe('info', () => {
  it('should retrieve the proper host and get info from it', async () => {
    use(hostStub);
    const response: InfoResult = await info(infoStub.url);
    expect(response.isOk()).toBe(true);
    expect(response._unsafeUnwrap()).toBe(infoStub);
  });

  it('should fail with unknown host', async () => {
    const response: InfoResult = await info(`${infoStub.url}ed`);
    expect(response.isErr()).toBe(true);
    expect(response._unsafeUnwrapErr()).toMatchObject(new InvalidArgumentError(`No matching host for url ${infoStub.url}ed`));
  });
});

describe('download', () => {
  it('should retrieve the proper host and get stream from it', async () => {
    use(hostStub);
    const response: DownloadResult = await download(infoStub.url);
    expect(response.isOk()).toBe(true);
    expect(response._unsafeUnwrap()).toBeInstanceOf(MockedReadStream);
  });

  it('should fail with unknown host', async () => {
    const response: DownloadResult = await download(`${infoStub.url}ed`);
    expect(response.isErr()).toBe(true);
    expect(response._unsafeUnwrapErr()).toMatchObject(new InvalidArgumentError(`No matching host for url ${infoStub.url}ed`));
  });
});
