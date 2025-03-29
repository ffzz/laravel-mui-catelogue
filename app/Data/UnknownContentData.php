<?php

namespace App\Data;

class UnknownContentData extends Content
{
    public function __construct(
        // Base class properties
        int $id,
        string $fullname,
        string $summary,
        ?string $image,
        string $contentType,
        string $url,
        ?string $badge,
        ?string $completionStatus,
        ?array $programs,
        ?array $category,
        ?array $tags,
        ?array $customfields,
        float $cost,
        string $duration,
        string $timeCreated,
        string $timeModified,
        string $contentStatus,
        mixed $paymentCost,
        array $originalData = [],
    ) {
        parent::__construct(
            $id,
            $fullname,
            $summary,
            $image,
            $contentType,
            $url,
            $badge,
            $completionStatus,
            $programs,
            $category,
            $tags,
            $customfields,
            $cost,
            $duration,
            $timeCreated,
            $timeModified,
            $contentStatus,
            $paymentCost,
            $originalData
        );
    }

    /**
     * Factory method to create an instance from API response
     */
    public static function fromApiResponse(array $data, ?string $apiVersion = null): self
    {
        // Add API version to original data
        $dataCopy = $data;
        if ($apiVersion !== null) {
            $dataCopy['api_version'] = $apiVersion;
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
            timeCreated: $dataCopy['timecreated'] ?? '',
            timeModified: $dataCopy['timemodified'] ?? '',
            contentStatus: $dataCopy['contentstatus'] ?? '',
            paymentCost: $dataCopy['paymentCost'] ?? 0,
            originalData: $dataCopy,
        );
    }
}
