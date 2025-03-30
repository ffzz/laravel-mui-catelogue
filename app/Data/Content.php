<?php

namespace App\Data;

use Spatie\LaravelData\Data;
use Spatie\LaravelData\Attributes\WithCast;
use Spatie\LaravelData\Casts\DateTimeInterfaceCast;
use Spatie\LaravelData\Attributes\Validation\IntegerType;
use Spatie\LaravelData\Attributes\Validation\StringType;
use Spatie\LaravelData\Attributes\Validation\Min;
use Spatie\LaravelData\Attributes\Validation\Max;
use Spatie\LaravelData\Attributes\Validation\Url;
use Spatie\LaravelData\Attributes\Validation\ArrayType;
use Spatie\LaravelData\Attributes\Validation\Nullable;
use Spatie\LaravelData\Attributes\Validation\Required;
use Illuminate\Support\Facades\Log;

class Content extends Data
{
    public function __construct(
        #[Required, IntegerType, Min(0)]
        public readonly int $id,

        #[Required, StringType, Max(255)]
        public readonly string $fullname,

        #[Required, StringType]
        public readonly string $summary,

        #[Nullable, StringType, Url]
        public readonly ?string $image,

        #[Required, StringType, Max(50)]
        public readonly string $contentType,

        #[Required, StringType, Url]
        public readonly string $url,

        #[Nullable, StringType, Url]
        public readonly ?string $badge,

        #[Nullable, StringType, Max(50)]
        public readonly ?string $completionStatus,

        #[Nullable, ArrayType]
        public readonly ?array $programs,

        #[Nullable, ArrayType]
        public readonly ?array $category,

        #[Nullable, ArrayType]
        public readonly ?array $tags,

        #[Nullable, ArrayType]
        public readonly ?array $customfields,

        #[Required, Min(0)]
        public readonly float $cost,

        #[Required, StringType, Max(100)]
        public readonly string $duration,

        #[Required, StringType]
        #[WithCast(DateTimeInterfaceCast::class)]
        public readonly string $timeCreated,

        #[Required, StringType]
        #[WithCast(DateTimeInterfaceCast::class)]
        public readonly string $timeModified,

        #[Required, StringType, Max(50)]
        public readonly string $contentStatus,

        public readonly mixed $paymentCost,

        #[ArrayType]
        public readonly array $originalData = [],

        #[Nullable, StringType, Max(20)]
        public readonly ?string $apiVersion = null,
    ) {}

    /**
     * Factory method to create an instance from API response
     */
    public static function fromApiResponse(array $data, ?string $apiVersion = null): self
    {
        try {
            // Add API version to original data
            $dataCopy = $data;
            if ($apiVersion !== null) {
                $dataCopy['api_version'] = $apiVersion;
            }

            // Log incoming data for debugging
            Log::debug('Creating Content from API response', [
                'content_type' => $dataCopy['contenttype'] ?? 'unknown',
                'id' => $dataCopy['contentid'] ?? 0,
                'data_keys' => array_keys($dataCopy),
            ]);

            // Ensure required fields have default values
            $defaultValues = [
                'contentid' => 0,
                'fullname' => 'Unknown Content',
                'summary' => 'No summary available',
                'contenttype' => 'unknown',
                'url' => 'https://example.com', // Default URL for validation
                'cost' => 0,
                'duration' => 'Unknown',
                'timecreated' => date('Y-m-d'),
                'timemodified' => date('Y-m-d'),
                'contentstatus' => 'unknown',
            ];

            // Merge defaults with actual data
            foreach ($defaultValues as $key => $value) {
                if (!isset($dataCopy[$key]) || empty($dataCopy[$key])) {
                    $dataCopy[$key] = $value;
                }
            }

            return new self(
                id: $dataCopy['contentid'] ?? 0,
                fullname: $dataCopy['fullname'] ?? '',
                summary: $dataCopy['summary'] ?? '',
                image: $dataCopy['imageurl'] ?? null,
                contentType: $dataCopy['contenttype'] ?? 'unknown',
                url: $dataCopy['url'] ?? '',
                badge: $dataCopy['badge'] ?? null,
                completionStatus: $dataCopy['completionstatus'] ?? null,
                programs: $dataCopy['programs'] ?? [],
                category: $dataCopy['category'] ?? null,
                tags: $dataCopy['tags'] ?? [],
                customfields: $dataCopy['customfields'] ?? [],
                cost: $dataCopy['cost'] ?? 0,
                duration: $dataCopy['duration'] ?? '',
                timeCreated: $dataCopy['timecreated'] ?? date('Y-m-d'),
                timeModified: $dataCopy['timemodified'] ?? date('Y-m-d'),
                contentStatus: $dataCopy['contentstatus'] ?? '',
                paymentCost: $dataCopy['paymentCost'] ?? 0,
                originalData: $dataCopy,
                apiVersion: $apiVersion ?? $dataCopy['api_version'] ?? null,
            );
        } catch (\Throwable $e) {
            Log::error('Error creating Content from API response: ' . $e->getMessage(), [
                'exception' => $e,
                'data' => $data,
            ]);

            // Create a fallback content object with minimal data
            return new self(
                id: 0,
                fullname: 'Error Processing Content',
                summary: 'There was an error processing this content item.',
                image: null,
                contentType: 'error',
                url: 'https://example.com',
                badge: null,
                completionStatus: null,
                programs: [],
                category: null,
                tags: [],
                customfields: [],
                cost: 0,
                duration: '',
                timeCreated: date('Y-m-d'),
                timeModified: date('Y-m-d'),
                contentStatus: 'error',
                paymentCost: 0,
                originalData: ['error' => $e->getMessage()],
                apiVersion: $apiVersion,
            );
        }
    }

    /**
     * Get specific metadata value
     */
    public function getMetadata(string $key, $default = null)
    {
        if (!is_array($this->customfields)) {
            return $default;
        }

        return $this->customfields[$key] ?? $default;
    }

    /**
     * Enhanced transformation method
     */
    public function transform(\Spatie\LaravelData\Support\Transformation\TransformationContext|\Spatie\LaravelData\Support\Transformation\TransformationContextFactory|null $transformationContext = null): array
    {
        try {
            // Execute parent transformation
            $transformed = parent::transform($transformationContext);

            // Add custom fields
            $transformed['type'] = $this->contentType;
            $transformed['has_image'] = !empty($this->image);

            // Ensure dates can be processed
            $timeCreated = $this->timeCreated ?? '';
            $timeModified = $this->timeModified ?? '';

            $transformed['formatted_date'] = [
                'created' => is_string($timeCreated) ? date('Y-m-d', strtotime($timeCreated)) : '',
                'modified' => is_string($timeModified) ? date('Y-m-d', strtotime($timeModified)) : '',
            ];

            return $transformed;
        } catch (\Throwable $e) {
            // Fallback to basic representation if transformation fails
            \Illuminate\Support\Facades\Log::error('Error transforming content: ' . $e->getMessage(), [
                'exception' => $e,
                'content_id' => $this->id ?? 'unknown',
            ]);

            return [
                'id' => $this->id ?? 0,
                'fullname' => $this->fullname ?? '',
                'type' => $this->contentType ?? 'unknown',
                'error' => 'Transformation error occurred',
            ];
        }
    }
}
