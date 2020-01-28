import { Result, ok, err } from "neverthrow";
import * as uptobox from './uptobox';
import { Host, DownloadOptions, DownloadResult, InfoResult } from '../types';
import InvalidArgumentError from '../errors/InvalidArgumentError';

const hosts: Host[] = [
  uptobox,
];

export function use(host: Host): void {
  const hostIndex = hosts.findIndex((installedHost: Host) => host.identifier === installedHost.identifier);

  if (hostIndex > -1) {
    hosts.splice(hostIndex, 1);
  }

  hosts.unshift(host);
}

export function getHost(url: string): Result<string, Error> {
  const matchingHost = hosts.find((host: Host) => host.match(url));

  if (!matchingHost) {
    return err(new InvalidArgumentError(`No matching host for url ${url}`));
  }

  return ok(matchingHost.identifier)
}

export async function info(url: string): Promise<InfoResult> {
  const matchingHost = hosts.find((host: Host) => host.match(url));

  if (!matchingHost) {
    return err(new InvalidArgumentError(`No matching host for url ${url}`));
  }

  return matchingHost.info(url);
}

export async function download(url: string, options?: DownloadOptions): Promise<DownloadResult> {
  const matchingHost = hosts.find((host: Host) => host.match(url));

  if (!matchingHost) {
    return err(new InvalidArgumentError(`No matching host for url ${url}`));
  }

  return matchingHost.download(url, options);
}