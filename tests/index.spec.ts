import * as lib from '../src';

describe('Lib entry point', () => {
  it('should provide all api entry points', () => {
    expect(Object.keys(lib)).toEqual(['use', 'getHost', 'info', 'download'])
  });
});
