<?php

namespace App\Data\Content;

abstract class Content
{
    protected int $id;
    protected string $fullname;
    protected string $summary;
    protected ?string $image;
    protected string $contentType;
    protected string $url;
    protected ?string $badge;
    protected ?string $completionStatus;
    protected ?array $programs;
    protected ?array $category;
    protected ?array $tags;
    protected ?array $customfields;
    protected float $cost;
    protected string $duration;
    protected string $timeCreated;
    protected string $timeModified;
    protected string $contentStatus;
    protected $paymentCost;
    protected array $originalData;

    public function __construct(array $data)
    {
        $this->id = $data['contentid'] ?? 0;
        $this->fullname = $data['fullname'] ?? '';
        $this->summary = $data['summary'] ?? '';
        $this->image = $data['imageurl'] ?? null;
        $this->contentType = $data['contenttype'] ?? 'unknown';
        $this->url = $data['url'] ?? '';
        $this->badge = $data['badge'] ?? null;
        $this->completionStatus = $data['completionstatus'] ?? null;
        $this->programs = $data['programs'] ?? [];
        $this->category = $data['category'] ?? null;
        $this->tags = $data['tags'] ?? [];
        $this->customfields = $data['customfields'] ?? [];
        $this->cost = $data['cost'] ?? 0;
        $this->duration = $data['duration'] ?? '';
        $this->timeCreated = $data['timecreated'] ?? '';
        $this->timeModified = $data['timemodified'] ?? '';
        $this->contentStatus = $data['contentstatus'] ?? '';
        $this->paymentCost = $data['paymentCost'] ?? 0;
        $this->originalData = $data;

        $this->init($data);
    }

    /**
     * Initialize additional properties for specific content types
     */
    abstract protected function init(array $data): void;

    /**
     * Get the content ID
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * Get the content fullname/title
     */
    public function getFullname(): string
    {
        return $this->fullname;
    }

    /**
     * Get the content summary/description
     */
    public function getSummary(): string
    {
        return $this->summary;
    }

    /**
     * Get the content image URL
     */
    public function getImage(): ?string
    {
        return $this->image;
    }

    /**
     * Get the content type
     */
    public function getContentType(): string
    {
        return $this->contentType;
    }

    /**
     * Get the content URL
     */
    public function getUrl(): string
    {
        return $this->url;
    }

    /**
     * Get the badge URL
     */
    public function getBadge(): ?string
    {
        return $this->badge;
    }

    /**
     * Get the completion status
     */
    public function getCompletionStatus(): ?string
    {
        return $this->completionStatus;
    }

    /**
     * Get programs this content belongs to
     */
    public function getPrograms(): ?array
    {
        return $this->programs;
    }

    /**
     * Get the content category
     */
    public function getCategory(): ?array
    {
        return $this->category;
    }

    /**
     * Get the content tags
     */
    public function getTags(): ?array
    {
        return $this->tags;
    }

    /**
     * Get custom fields
     */
    public function getCustomFields(): ?array
    {
        return $this->customfields;
    }

    /**
     * Get content cost
     */
    public function getCost(): float
    {
        return $this->cost;
    }

    /**
     * Get content duration
     */
    public function getDuration(): string
    {
        return $this->duration;
    }

    /**
     * Get time created
     */
    public function getTimeCreated(): string
    {
        return $this->timeCreated;
    }

    /**
     * Get time modified
     */
    public function getTimeModified(): string
    {
        return $this->timeModified;
    }

    /**
     * Get content status
     */
    public function getContentStatus(): string
    {
        return $this->contentStatus;
    }

    /**
     * Get payment cost information
     */
    public function getPaymentCost()
    {
        return $this->paymentCost;
    }

    /**
     * Get all properties as an array
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'fullname' => $this->fullname,
            'summary' => $this->summary,
            'image' => $this->image,
            'content_type' => $this->contentType,
            'url' => $this->url,
            'badge' => $this->badge,
            'completion_status' => $this->completionStatus,
            'programs' => $this->programs,
            'category' => $this->category,
            'tags' => $this->tags,
            'custom_fields' => $this->customfields,
            'cost' => $this->cost,
            'duration' => $this->duration,
            'time_created' => $this->timeCreated,
            'time_modified' => $this->timeModified,
            'content_status' => $this->contentStatus,
            'payment_cost' => $this->paymentCost
        ];
    }

    /**
     * Get the original data
     */
    public function getOriginalData(): array
    {
        return $this->originalData;
    }
}
