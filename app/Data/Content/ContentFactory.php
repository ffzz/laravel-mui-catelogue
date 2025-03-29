<?php

namespace App\Data\Content;

class ContentFactory
{
    /**
     * Create content instance based on content type
     */
    public static function createFromData(array $data): Content
    {
        $contentType = $data['contenttype'] ?? 'unknown';

        return match (strtolower($contentType)) {
            'course' => new Course($data),
            'live learning' => new LiveLearning($data),
            'program' => new Program($data),
            'resource' => new Resource($data),
            'video' => new Video($data),
            'page' => new Page($data),
            'partnered content' => new PartnerContent($data),
            default => new UnknownContent($data),
        };
    }

    /**
     * Create a collection of content instances from API data
     */
    public static function createCollectionFromData(array $items): array
    {
        $collection = [];

        foreach ($items as $item) {
            $collection[] = self::createFromData($item);
        }

        return $collection;
    }
}
