import { ReadStream } from 'fs';

export interface Host {
  identifier: string;
  match(url: string): boolean,
  info(url: string): Promise<Info>,
  download(url: string, options?: DownloadOptions): Promise<ReadStream>,
}

export interface AuthOptions {
  user?: string;
  password?: string;
  noCache?: boolean;
}

export interface Auth {
  cookies: string[];
}

export interface DownloadOptions {
  auth?: AuthOptions;
  target?: string;
}

export interface Stream<T> extends Promise<T> {
  cancel(): void;
}

export interface Info {
  url: string;
  filename: string;
  size: string;
  cooldown: number;
}
