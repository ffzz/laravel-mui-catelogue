import axios, { AxiosError, AxiosResponse } from 'axios';

// Create Axios instance with default config
const apiClient = axios.create({
    baseURL: '/api/v1',
    headers: {
        'Content-Type': 'application/json',
        Accept: 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
    },
    timeout: 30000, // 30 seconds
});

// Request interceptor for API calls
apiClient.interceptors.request.use(
    (config) => {
        // Get CSRF token from meta tag if it exists
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

        if (csrfToken) {
            config.headers['X-CSRF-TOKEN'] = csrfToken;
        }

        // Log request for debugging
        if (process.env.NODE_ENV !== 'production') {
            console.debug('API Request:', {
                url: config.url,
                method: config.method,
                data: config.data,
                params: config.params,
            });
        }

        return config;
    },
    (error) => {
        console.error('API Request Error:', error);
        return Promise.reject(error);
    },
);

// Response interceptor for API calls
apiClient.interceptors.response.use(
    (response: AxiosResponse) => {
        // Log response for debugging
        if (process.env.NODE_ENV !== 'production') {
            console.debug('API Response:', {
                url: response.config.url,
                status: response.status,
                data: response.data,
            });
        }

        return response;
    },
    (error: AxiosError) => {
        // Log error response for debugging
        console.error('API Response Error:', {
            url: error.config?.url,
            status: error.response?.status,
            statusText: error.response?.statusText,
            data: error.response?.data,
        });

        // Handle specific error cases
        if (error.response?.status === 401) {
            // Redirect to login if unauthorized
            //   window.location.href = '/login';
            console.error('Unauthorized');
        }

        // Handle validation errors (422)
        if (error.response?.status === 422) {
            // Return with validation errors for form handling
            return Promise.reject(error);
        }

        // Handle server errors (500)
        if (error.response?.status && error.response?.status >= 500) {
            // Could show a toast notification here
            console.error('Server error occurred. Please try again later.');
        }

        return Promise.reject(error);
    },
);

export default apiClient;
