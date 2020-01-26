export default class ServerError implements Error {
  constructor(message: string) {
    this.message = message;
  }

  name: string = 'ServerError';
  message: string = '';
}