import { AxiosError, AxiosRequestConfig, AxiosResponse } from 'axios';
import apiClient from './axios';

export interface ApiResponse<T> {
    data: T | null;
    error: string | null;
    errors?: Record<string, string[]> | null;
    status: number;
    success: boolean;
}

interface ApiErrorResponse {
    message?: string;
    error?: string;
    errors?: Record<string, string[]>;
}

/**
 * Generic API request function
 * @param config - Axios request configuration
 * @returns ApiResponse with formatted data and errors
 */
export const apiRequest = async <T>(config: AxiosRequestConfig): Promise<ApiResponse<T>> => {
    try {
        const response: AxiosResponse = await apiClient(config);

        return {
            data: response.data,
            error: null,
            errors: null,
            status: response.status,
            success: true,
        };
    } catch (err) {
        const error = err as AxiosError;
        const responseData = error.response?.data as ApiErrorResponse;

        return {
            data: null,
            error: responseData?.message || responseData?.error || error.message || 'An unknown error occurred',
            errors: responseData?.errors || null,
            status: error.response?.status || 500,
            success: false,
        };
    }
};

/**
 * GET request helper
 * @param url - API endpoint
 * @param params - Query parameters
 * @param config - Additional Axios config
 * @returns ApiResponse with formatted data and errors
 */
export const get = <T>(
    url: string,
    params?: Record<string, unknown>,
    config?: Omit<AxiosRequestConfig, 'url' | 'params' | 'method'>,
): Promise<ApiResponse<T>> => {
    return apiRequest<T>({
        url,
        method: 'GET',
        params,
        ...config,
    });
};

/**
 * POST request helper
 * @param url - API endpoint
 * @param data - Request body data
 * @param config - Additional Axios config
 * @returns ApiResponse with formatted data and errors
 */
export const post = <T>(url: string, data?: unknown, config?: Omit<AxiosRequestConfig, 'url' | 'data' | 'method'>): Promise<ApiResponse<T>> => {
    return apiRequest<T>({
        url,
        method: 'POST',
        data,
        ...config,
    });
};

/**
 * PUT request helper
 * @param url - API endpoint
 * @param data - Request body data
 * @param config - Additional Axios config
 * @returns ApiResponse with formatted data and errors
 */
export const put = <T>(url: string, data?: unknown, config?: Omit<AxiosRequestConfig, 'url' | 'data' | 'method'>): Promise<ApiResponse<T>> => {
    return apiRequest<T>({
        url,
        method: 'PUT',
        data,
        ...config,
    });
};

/**
 * PATCH request helper
 * @param url - API endpoint
 * @param data - Request body data
 * @param config - Additional Axios config
 * @returns ApiResponse with formatted data and errors
 */
export const patch = <T>(url: string, data?: unknown, config?: Omit<AxiosRequestConfig, 'url' | 'data' | 'method'>): Promise<ApiResponse<T>> => {
    return apiRequest<T>({
        url,
        method: 'PATCH',
        data,
        ...config,
    });
};

/**
 * DELETE request helper
 * @param url - API endpoint
 * @param config - Additional Axios config
 * @returns ApiResponse with formatted data and errors
 */
export const del = <T>(url: string, config?: Omit<AxiosRequestConfig, 'url' | 'method'>): Promise<ApiResponse<T>> => {
    return apiRequest<T>({
        url,
        method: 'DELETE',
        ...config,
    });
};
