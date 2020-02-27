module.exports = {
  testEnvironment: 'node',
  notify: true,
  testRegex: '(src/.*\\.spec)\\.ts$',
  transform: {
    '^.+\\.ts$': 'ts-jest',
  },
  roots: [
    '<rootDir>/src/',
  ],
  moduleFileExtensions: ['ts', 'js', 'json'],
  collectCoverageFrom: [
    'src/**/*.{js,ts}',
    '!**/node_modules/**',
    '!**/dist/**',
    '!**/vendor/**',
    '!src/**/*.d.ts',
    '!src/**/*.*test.*ts',
  ],
  coverageReporters: ['json', 'lcov'],
  coverageDirectory: 'coverage',
  verbose: true,
  watchPathIgnorePatterns: ['<rootDir>/node_modules/', '<rootDir>/dist/'],
  testPathIgnorePatterns: ['<rootDir>/node_modules/', '<rootDir>/dist/'],
};
