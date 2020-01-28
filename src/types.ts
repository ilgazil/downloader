import { ReadStream } from 'fs';
import { Result } from 'neverthrow';

export interface Host {
  identifier: string;
  match(url: string): boolean,
  info(url: string): Promise<InfoResult>,
  download(url: string, options?: DownloadOptions): Promise<DownloadResult>,
}

export interface Credentials {
  user: string;
  password: string;
}

export interface DownloadOptions {
  credentials?: Credentials;
  target?: string;
}

export interface Stream<T> extends Promise<T> {
  cancel(): void;
}

export interface Auth {
  cookies: string[];
}

export interface Info {
  url: string;
  filename: string;
  size: string;
  cooldown: number;
}

export type AuthResult = Result<Auth, Error>;
export type InfoResult = Result<Info, Error>;
export type DownloadResult = Result<ReadStream, Error>;
