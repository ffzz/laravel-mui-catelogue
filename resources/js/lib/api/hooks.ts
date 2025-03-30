import { useMutation, UseMutationOptions, useQuery, UseQueryOptions } from '@tanstack/react-query';
import { AxiosRequestConfig } from 'axios';
import { ApiResponse, del, get, patch, post, put } from './api';

/**
 * Custom hook for fetching data with React Query
 * @param queryKey - Unique identifier for the query
 * @param url - API endpoint
 * @param params - Query parameters
 * @param options - Additional React Query options
 * @returns Query result with data and state
 */
export const useApiQuery = <T>(
    queryKey: string | string[],
    url: string,
    params?: Record<string, unknown>,
    options?: Omit<UseQueryOptions<ApiResponse<T>, Error, ApiResponse<T>, (string | Record<string, unknown> | undefined)[]>, 'queryKey' | 'queryFn'>,
) => {
    const queryKeyArray = Array.isArray(queryKey) ? queryKey : [queryKey];

    // Include url and params in the query key for proper cache invalidation
    return useQuery<ApiResponse<T>, Error, ApiResponse<T>, (string | Record<string, unknown> | undefined)[]>({
        queryKey: [...queryKeyArray, url, params],
        queryFn: async () => get<T>(url, params),
        ...options,
    });
};

/**
 * Type for mutation function parameter
 */
type MutationParams<T> = {
    url: string;
    data?: T;
    config?: AxiosRequestConfig;
};

/**
 * Custom hook for creating data with React Query
 * @param options - Mutation options
 * @returns Mutation result with state and function to trigger mutation
 */
export const useApiCreate = <TData, TVariables = unknown>(
    options?: Omit<UseMutationOptions<ApiResponse<TData>, Error, MutationParams<TVariables>, unknown>, 'mutationFn'>,
) => {
    return useMutation<ApiResponse<TData>, Error, MutationParams<TVariables>>({
        mutationFn: async ({ url, data, config }) => post<TData>(url, data, config),
        onSuccess: (data, variables, context) => {
            // Invalidate related queries on successful mutations
            if (options?.onSuccess) {
                options.onSuccess(data, variables, context);
            }

            // You can add automatic cache invalidation here if needed
            // Example: queryClient.invalidateQueries(['some-related-query']);
        },
        ...options,
    });
};

/**
 * Custom hook for updating data with React Query
 * @param options - Mutation options
 * @returns Mutation result with state and function to trigger mutation
 */
export const useApiUpdate = <TData, TVariables = unknown>(
    options?: Omit<UseMutationOptions<ApiResponse<TData>, Error, MutationParams<TVariables>, unknown>, 'mutationFn'>,
) => {
    return useMutation<ApiResponse<TData>, Error, MutationParams<TVariables>>({
        mutationFn: async ({ url, data, config }) => put<TData>(url, data, config),
        onSuccess: (data, variables, context) => {
            if (options?.onSuccess) {
                options.onSuccess(data, variables, context);
            }
        },
        ...options,
    });
};

/**
 * Custom hook for patching data with React Query
 * @param options - Mutation options
 * @returns Mutation result with state and function to trigger mutation
 */
export const useApiPatch = <TData, TVariables = unknown>(
    options?: Omit<UseMutationOptions<ApiResponse<TData>, Error, MutationParams<TVariables>, unknown>, 'mutationFn'>,
) => {
    return useMutation<ApiResponse<TData>, Error, MutationParams<TVariables>>({
        mutationFn: async ({ url, data, config }) => patch<TData>(url, data, config),
        onSuccess: (data, variables, context) => {
            if (options?.onSuccess) {
                options.onSuccess(data, variables, context);
            }
        },
        ...options,
    });
};

/**
 * Custom hook for deleting data with React Query
 * @param options - Mutation options
 * @returns Mutation result with state and function to trigger mutation
 */
export const useApiDelete = <TData>(
    options?: Omit<UseMutationOptions<ApiResponse<TData>, Error, { url: string; config?: AxiosRequestConfig }, unknown>, 'mutationFn'>,
) => {
    return useMutation<ApiResponse<TData>, Error, { url: string; config?: AxiosRequestConfig }>({
        mutationFn: async ({ url, config }) => del<TData>(url, config),
        onSuccess: (data, variables, context) => {
            if (options?.onSuccess) {
                options.onSuccess(data, variables, context);
            }
        },
        ...options,
    });
};
