<?php

namespace App\Jobs;

use App\Services\Content\ContentService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class RefreshContentCache implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The method to call on ContentService
     */
    protected string $method;

    /**
     * The parameters to pass to the method
     */
    protected array $params;

    /**
     * Create a new job instance.
     */
    public function __construct(string $method, array $params = [])
    {
        $this->method = $method;
        $this->params = $params;
    }

    /**
     * Execute the job.
     */
    public function handle(ContentService $contentService): void
    {
        try {
            Log::info('Refreshing content cache in background job', [
                'method' => $this->method,
                'params' => $this->params,
            ]);

            switch ($this->method) {
                case 'getCatalogue':
                    $page = $this->params['page'] ?? 1;
                    $perPage = $this->params['perPage'] ?? null;
                    $filters = $this->params['filters'] ?? [];

                    // Force bypass cache
                    $filters['no_cache'] = true;

                    $contentService->getCatalogue($page, $perPage, $filters);
                    break;

                case 'getContentItem':
                    $id = $this->params['id'] ?? null;
                    $bypassCache = $this->params['bypassCache'] ?? true;

                    if (is_null($id)) {
                        throw new \InvalidArgumentException('Content ID is required');
                    }

                    $contentService->getContentItem($id, $bypassCache);
                    break;

                case 'getContentByType':
                    $contentType = $this->params['contentType'] ?? null;
                    $page = $this->params['page'] ?? 1;
                    $perPage = $this->params['perPage'] ?? null;
                    $bypassCache = $this->params['bypassCache'] ?? true;

                    if (is_null($contentType)) {
                        throw new \InvalidArgumentException('Content type is required');
                    }

                    $contentService->getContentByType($contentType, $page, $perPage, $bypassCache);
                    break;

                default:
                    throw new \InvalidArgumentException("Unknown method: {$this->method}");
            }

            Log::info('Cache refreshed successfully in background job', [
                'method' => $this->method
            ]);
        } catch (\Throwable $e) {
            Log::error('Failed to refresh cache in background job', [
                'method' => $this->method,
                'params' => $this->params,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }
}
