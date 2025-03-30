import '@testing-library/jest-dom';
import * as matchers from '@testing-library/jest-dom/matchers';
import { cleanup } from '@testing-library/react';
import { afterEach, expect, vi } from 'vitest';

// Extend Vitest's assertion matchers
expect.extend(matchers);

// Mock IntersectionObserver which is not available in the test environment
if (typeof window !== 'undefined') {
    Object.defineProperty(window, 'IntersectionObserver', {
        writable: true,
        value: vi.fn().mockImplementation(() => ({
            observe: vi.fn(),
            unobserve: vi.fn(),
            disconnect: vi.fn(),
        })),
    });

    // Mock matchMedia for responsive design testing
    window.matchMedia =
        window.matchMedia ||
        function () {
            return {
                matches: false,
                addListener: () => {},
                removeListener: () => {},
            };
        };
}

// Clean up after each test
afterEach(() => {
    cleanup();
});
