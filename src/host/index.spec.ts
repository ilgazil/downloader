import { ReadStream } from 'fs';
import { use, getHost, info, download } from './';
import * as uptobox from './uptobox';
import InvalidArgumentError from '../errors/InvalidArgumentError';
import { Host, Info, DownloadOptions } from '../types';

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
  info(url: string): Promise<Info> {
    return Promise.resolve(infoStub);
  },
  download(url: string, options: DownloadOptions): Promise<ReadStream> {
    return Promise.resolve(new MockedReadStream());
  },
};

describe('getHost', () => {
  it('should match uptobox', () => {
    expect(getHost('http://uptobox.com/randomhash')).toBe(uptobox.identifier);
    expect(getHost('https://uptobox.com/randomhash')).toBe(uptobox.identifier);
  });

  it('should fail with unknown host', () => {
    expect(getHost('http://some-unknown-host/randomhash')).toBe('');
  });
});

describe('use', () => {
  it('should allow to add hosts', () => {
    use(hostStub);
    expect(getHost(infoStub.url)).toBe(hostStub.identifier);
  });
});

describe('info', () => {
  it('should retrieve the proper host and get info from it', async () => {
    use(hostStub);
    const response: Info = await info(infoStub.url);
    expect(response).toBe(infoStub);
  });

  it('should fail with unknown host', () => {
    expect.assertions(1);

    info(`${infoStub.url}ed`).catch((e) => expect(e).toMatchObject(new InvalidArgumentError(`No matching host for url ${infoStub.url}ed`)));
  });
});

describe('download', () => {
  it('should retrieve the proper host and get stream from it', async () => {
    use(hostStub);
    const response: ReadStream = await download(infoStub.url);
    expect(response).toBeInstanceOf(MockedReadStream);
  });

  it('should fail with unknown host', () => {
    download(`${infoStub.url}ed`).catch((e) => expect(e).toMatchObject(new InvalidArgumentError(`No matching host for url ${infoStub.url}ed`)));
  });
});
