import { get, post } from '@/lib/api/api';
import { useApiCreate, useApiQuery } from '@/lib/api/hooks';
import { QueryClient, QueryClientProvider } from '@tanstack/react-query';
import { act, renderHook } from '@testing-library/react';
import React from 'react';
import { beforeEach, describe, expect, it, vi } from 'vitest';

// Mock API modules
vi.mock('@/lib/api/api', () => ({
    get: vi.fn(),
    post: vi.fn(),
}));

// Create a wrapper for React Query
const createQueryWrapper = () => {
    const queryClient = new QueryClient({
        defaultOptions: {
            queries: {
                retry: false,
            },
        },
    });

    return ({ children }: { children: React.ReactNode }) => <QueryClientProvider client={queryClient}>{children}</QueryClientProvider>;
};

describe('Content API Hooks', () => {
    beforeEach(() => {
        vi.resetAllMocks();
    });

    describe('useApiQuery - Content List', () => {
        it('should fetch content list successfully', async () => {
            // Mock successful API response
            const mockResponse = {
                data: {
                    items: [
                        {
                            id: 1,
                            fullname: 'Test Course',
                            contentType: 'course',
                            summary: 'Test description',
                            image: 'image-url.jpg',
                        },
                    ],
                    metadata: {
                        currentPage: 1,
                        totalPages: 1,
                        totalItems: 1,
                    },
                },
                error: null,
                errors: null,
                status: 200,
                success: true,
            };

            // Setup the mock to return our data
            vi.mocked(get).mockResolvedValueOnce(mockResponse);

            // Render the hook with React Query wrapper
            const { result } = renderHook(() => useApiQuery(['content'], '/content', { page: 1, perPage: 10 }), { wrapper: createQueryWrapper() });

            // Wait for the query to complete
            await act(async () => {
                // Wait for the query to complete
                await vi.waitFor(() => expect(result.current.isSuccess).toBe(true));
            });

            // Verify the data is correct
            expect(result.current.data).toEqual(mockResponse);
            expect(get).toHaveBeenCalledWith('/content', { page: 1, perPage: 10 });
        });

        it('should handle error when fetching content list', async () => {
            // Mock error response
            const mockError = new Error('Failed to fetch content');
            vi.mocked(get).mockRejectedValueOnce(mockError);

            // Render the hook
            const { result } = renderHook(() => useApiQuery(['content'], '/content', { contentType: 'invalid' }), { wrapper: createQueryWrapper() });

            // Wait for the query to fail
            await act(async () => {
                await vi.waitFor(() => expect(result.current.isError).toBe(true));
            });

            // Verify the error
            expect(result.current.error).toBeDefined();
            expect(get).toHaveBeenCalledWith('/content', { contentType: 'invalid' });
        });
    });

    describe('useApiCreate - Refresh Cache', () => {
        it('should refresh cache successfully', async () => {
            // Mock successful API response
            const mockResponse = {
                data: {
                    success: true,
                    message: 'Cache refreshed successfully',
                },
                error: null,
                errors: null,
                status: 200,
                success: true,
            };

            // Setup the mock
            vi.mocked(post).mockResolvedValueOnce(mockResponse);

            // Setup success callback
            const onSuccess = vi.fn();

            // Render the hook
            const { result } = renderHook(() => useApiCreate({ onSuccess }), { wrapper: createQueryWrapper() });

            // Call the mutation function
            act(() => {
                result.current.mutate({
                    url: '/content/refresh-cache',
                    data: { contentType: 'course' },
                });
            });

            // Wait for completion
            await act(async () => {
                await vi.waitFor(() => expect(result.current.isSuccess).toBe(true));
            });

            // Verify the mutation ran correctly
            expect(post).toHaveBeenCalledWith('/content/refresh-cache', { contentType: 'course' }, undefined);
            expect(onSuccess).toHaveBeenCalled();
        });

        it('should handle error when refreshing cache', async () => {
            // Mock error response
            const mockError = new Error('Failed to refresh cache');
            vi.mocked(post).mockRejectedValueOnce(mockError);

            // Setup error callback
            const onError = vi.fn();

            // Render the hook
            const { result } = renderHook(() => useApiCreate({ onError }), { wrapper: createQueryWrapper() });

            // Call the mutation function
            act(() => {
                result.current.mutate({
                    url: '/content/refresh-cache',
                });
            });

            // Wait for error
            await act(async () => {
                await vi.waitFor(() => expect(result.current.isError).toBe(true));
            });

            // Verify the error
            expect(result.current.error).toBeDefined();
            expect(onError).toHaveBeenCalled();
            expect(post).toHaveBeenCalledWith('/content/refresh-cache', undefined, undefined);
        });
    });
});
