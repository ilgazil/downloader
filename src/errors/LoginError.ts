export default class LoginError implements Error {
  constructor(message: string) {
    this.message = message;
  }

  name: string = 'LoginError';
  message: string = '';
}