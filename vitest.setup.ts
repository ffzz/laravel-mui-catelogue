import { expect, afterEach } from 'vitest';
import { cleanup } from '@testing-library/react';
import * as matchers from '@testing-library/jest-dom/matchers';

// Extend Vitest's assertion matchers
expect.extend(matchers);

// Automatically clean up after each test
afterEach(() => {
  cleanup();
});

// Mock matchMedia
window.matchMedia = window.matchMedia || function() {
    return {
        matches: false,
        addListener: () => {},
        removeListener: () => {}
    };
};