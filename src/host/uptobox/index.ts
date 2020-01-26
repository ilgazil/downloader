export * from './auth';
export * from './info';
export * from './download';

export const identifier: string = 'uptobox';

export function match(url: string): boolean {
  return url.indexOf('//uptobox.com/') > 0;
}
