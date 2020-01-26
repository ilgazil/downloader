import { unlinkSync, ReadStream } from 'fs';

export default class Download {
  readonly path: string;
  readonly filename: string;
  readonly size: string;
  readonly stream: ReadStream;

  constructor(path: string, size: string, stream: ReadStream) {
    this.path = path.substr(0, path.lastIndexOf('/'));
    this.filename = path.substr(path.lastIndexOf('/') + 1);
    this.size = size;
    this.stream = stream;
  }

  pause() {
    if (!this.stream.isPaused()) {
      this.stream.pause();
    }
  }

  resume() {
    if (this.stream.isPaused()) {
      this.stream.resume();
    }
  }

  stop() {
    this.stream.close();
    unlinkSync(`${this.path}/${this.filename}`);
  }
}