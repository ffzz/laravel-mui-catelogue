<?php

namespace App\Services\HttpClient;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class HttpClientService
{
    protected Client $client;
    protected array $config;
    protected array $options;
    protected array $defaultOptions;
    protected bool $enableCache;
    protected int $cacheTtl;
    protected int $retryTimes;
    protected int $retrySleep;

    public function __construct(array $config = [])
    {
        $this->config = $config;
        $this->enableCache = $config['cache']['enabled'] ?? true;
        $this->cacheTtl = $config['cache']['ttl'] ?? 900; // 15 minutes default
        $this->retryTimes = $config['retry']['times'] ?? 3;
        $this->retrySleep = $config['retry']['sleep'] ?? 1000;

        $this->defaultOptions = [
            'http_errors' => false,
            'connect_timeout' => 10,
            'timeout' => 300, // 300 seconds (5 minutes)
            'headers' => [
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
            ],
        ];

        $this->options = array_merge($this->defaultOptions, $config['options'] ?? []);
        $this->client = $this->createClient();
    }

    /**
     * Create a configured Guzzle client
     */
    protected function createClient(): Client
    {
        $stack = HandlerStack::create();

        // Add logging middleware
        $stack->push($this->logMiddleware());

        // Add retry middleware
        $stack->push($this->retryMiddleware());

        return new Client(array_merge($this->options, ['handler' => $stack]));
    }

    /**
     * Create logging middleware
     */
    protected function logMiddleware(): callable
    {
        return function (callable $handler) {
            return function (RequestInterface $request, array $options) use ($handler) {
                Log::debug('API Request', [
                    'method' => $request->getMethod(),
                    'uri' => (string) $request->getUri(),
                    'headers' => $request->getHeaders(),
                    'body' => (string) $request->getBody(),
                ]);

                return $handler($request, $options)->then(
                    function (ResponseInterface $response) {
                        Log::debug('API Response', [
                            'statusCode' => $response->getStatusCode(),
                            'reasonPhrase' => $response->getReasonPhrase(),
                            'headers' => $response->getHeaders(),
                            'body' => (string) $response->getBody(),
                        ]);

                        // Reset the body position pointer back to the beginning
                        $response->getBody()->rewind();

                        return $response;
                    }
                );
            };
        };
    }

    /**
     * Create retry middleware
     */
    protected function retryMiddleware(): callable
    {
        return Middleware::retry(
            function (
                $retries,
                RequestInterface $request,
                ?ResponseInterface $response = null,
                ?GuzzleException $exception = null
            ) {
                // Don't retry if we've reached the max number of retries
                if ($retries >= $this->retryTimes) {
                    return false;
                }

                // Retry on server errors or connection errors
                if ($exception) {
                    Log::warning('API request failed, retrying...', [
                        'retries' => $retries + 1,
                        'exception' => $exception->getMessage(),
                    ]);
                    return true;
                }

                // Retry on server errors (5xx) or too many requests (429)
                if ($response && ($response->getStatusCode() >= 500 || $response->getStatusCode() === 429)) {
                    Log::warning('API request failed, retrying...', [
                        'retries' => $retries + 1,
                        'statusCode' => $response->getStatusCode(),
                    ]);
                    return true;
                }

                return false;
            },
            function ($retries) {
                // Exponential backoff
                return $this->retrySleep * pow(2, $retries - 1);
            }
        );
    }

    /**
     * Get cache key for the request
     */
    protected function getCacheKey(string $method, string $url, array $options): string
    {
        $key = strtolower($method) . '|' . $url;

        if (isset($options['query'])) {
            $key .= '|' . json_encode($options['query']);
        }

        if (isset($options['json'])) {
            $key .= '|' . json_encode($options['json']);
        }

        return 'http_client:' . md5($key);
    }

    /**
     * Make an HTTP request with caching
     */
    public function request(string $method, string $url, array $options = []): array
    {
        $cacheKey = $this->getCacheKey($method, $url, $options);

        // Check if response is cached and caching is enabled
        if ($this->enableCache && Cache::has($cacheKey) && strtoupper($method) === 'GET') {
            $cachedResponse = Cache::get($cacheKey);
            Log::info('Returning cached response', ['url' => $url]);
            return $cachedResponse;
        }

        try {
            $response = $this->client->request($method, $url, $options);
            $contents = $response->getBody()->getContents();
            $statusCode = $response->getStatusCode();
            $data = json_decode($contents, true) ?? $contents;

            $result = [
                'status_code' => $statusCode,
                'data' => $data,
                'headers' => $response->getHeaders(),
            ];

            // Cache successful GET requests
            if ($this->enableCache && strtoupper($method) === 'GET' && $statusCode >= 200 && $statusCode < 300) {
                Cache::put($cacheKey, $result, $this->cacheTtl);
                Log::info('Response cached', ['url' => $url, 'ttl' => $this->cacheTtl]);
            }

            return $result;
        } catch (GuzzleException $e) {
            Log::error('HTTP request failed', [
                'method' => $method,
                'url' => $url,
                'exception' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return [
                'status_code' => $e->getCode() ?: 500,
                'data' => ['error' => $e->getMessage()],
                'headers' => [],
            ];
        }
    }

    /**
     * Make a GET request
     */
    public function get(string $url, array $query = [], array $options = []): array
    {
        return $this->request('GET', $url, array_merge(['query' => $query], $options));
    }

    /**
     * Make a POST request
     */
    public function post(string $url, array $data = [], array $options = []): array
    {
        return $this->request('POST', $url, array_merge(['json' => $data], $options));
    }

    /**
     * Make a PUT request
     */
    public function put(string $url, array $data = [], array $options = []): array
    {
        return $this->request('PUT', $url, array_merge(['json' => $data], $options));
    }

    /**
     * Make a PATCH request
     */
    public function patch(string $url, array $data = [], array $options = []): array
    {
        return $this->request('PATCH', $url, array_merge(['json' => $data], $options));
    }

    /**
     * Make a DELETE request
     */
    public function delete(string $url, array $options = []): array
    {
        return $this->request('DELETE', $url, $options);
    }
}
