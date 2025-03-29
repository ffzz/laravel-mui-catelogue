<?php

namespace App\Data\Content;

class Resource extends Content
{
    protected ?string $resourceType;
    protected ?string $fileUrl;

    /**
     * Initialize resource specific properties
     */
    protected function init(array $data): void
    {
        $this->resourceType = $data['resource_type'] ?? null;
        $this->fileUrl = $data['file_url'] ?? null;
    }

    /**
     * Get the resource type
     */
    public function getResourceType(): ?string
    {
        return $this->resourceType;
    }

    /**
     * Get the file URL
     */
    public function getFileUrl(): ?string
    {
        return $this->fileUrl;
    }

    /**
     * Get all properties as an array
     */
    public function toArray(): array
    {
        return array_merge(parent::toArray(), [
            'resource_type' => $this->resourceType,
            'file_url' => $this->fileUrl,
        ]);
    }
}
