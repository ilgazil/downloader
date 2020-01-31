export default class ParsingError implements Error {
  constructor(message: string) {
    this.message = message;
  }

  name: string = 'ParsingError';
  message: string = '';
}