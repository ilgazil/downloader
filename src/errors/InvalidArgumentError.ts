export default class InvalidArgumentError implements Error {
  constructor(message: string) {
    this.message = message;
  }

  name: string = 'InvalidArgumentError';
  message: string = '';
}