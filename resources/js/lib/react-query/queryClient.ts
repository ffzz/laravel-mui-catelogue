import { QueryClient } from '@tanstack/react-query';

const queryClient = new QueryClient({
    defaultOptions: {
        queries: {
            // Cache time of 5 minutes
            gcTime: 1000 * 60 * 5,
            // Stale time of 30 seconds
            staleTime: 1000 * 30,
            // Retry failed queries up to 2 times
            retry: 2,
            // Retry delay with exponential backoff
            retryDelay: (attemptIndex) => Math.min(1000 * 2 ** attemptIndex, 30000),
            // Consider queries fresh for 5 seconds before refetching
            refetchOnMount: true,
            refetchOnWindowFocus: true,
        },
        mutations: {
            // Retry failed mutations up to 1 time
            retry: 1,
            retryDelay: 1000,
        },
    },
});

export default queryClient;
