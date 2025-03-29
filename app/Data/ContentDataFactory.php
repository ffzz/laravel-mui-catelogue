<?php

namespace App\Data;

use Illuminate\Support\Facades\Log;
use Spatie\LaravelData\DataCollection;

class ContentDataFactory
{
    /**
     * Create content data instance from API response data
     */
    public static function createFromData(array $data, ?string $apiVersion = null): Content
    {
        $contentType = strtolower($data['contenttype'] ?? 'unknown');

        try {
            return match ($contentType) {
                'course' => CourseData::fromApiResponse($data, $apiVersion),
                'live learning' => LiveLearningData::fromApiResponse($data, $apiVersion),
                'program' => ProgramData::fromApiResponse($data, $apiVersion),
                'resource' => ResourceData::fromApiResponse($data, $apiVersion),
                'video' => VideoData::fromApiResponse($data, $apiVersion),
                'page' => PageData::fromApiResponse($data, $apiVersion),
                'partnered content' => PartnerContentData::fromApiResponse($data, $apiVersion),
                default => UnknownContentData::fromApiResponse($data, $apiVersion),
            };
        } catch (\Throwable $e) {
            Log::error('Error creating content data: ' . $e->getMessage(), [
                'exception' => $e,
                'content_type' => $contentType,
                'data' => $data,
            ]);

            // Fallback to the basic UnknownContentData
            return UnknownContentData::fromApiResponse($data, $apiVersion);
        }
    }

    /**
     * Create collection of content data instances from array of API response data
     */
    public static function createCollectionFromData(array $items, ?string $apiVersion = null): array
    {
        $collection = [];
        foreach ($items as $item) {
            try {
                if (is_array($item)) {
                    $collection[] = self::createFromData($item, $apiVersion);
                } else {
                    Log::warning('Non-array item found in content collection', ['item' => $item]);
                }
            } catch (\Throwable $e) {
                Log::error('Error adding item to collection: ' . $e->getMessage(), [
                    'exception' => $e,
                ]);
            }
        }
        return $collection;
    }

    /**
     * Create content data collection
     */
    public static function createCollection(array $items, ?string $apiVersion = null): DataCollection
    {
        $collection = [];

        foreach ($items as $item) {
            try {
                if (is_array($item)) {
                    $collection[] = self::createFromData($item, $apiVersion);
                }
            } catch (\Throwable $e) {
                Log::error('Error adding item to data collection: ' . $e->getMessage(), [
                    'exception' => $e,
                ]);
            }
        }

        return new DataCollection(Content::class, $collection);
    }
}
