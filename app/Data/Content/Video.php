<?php

namespace App\Data\Content;

class Video extends Content
{
    protected ?string $videoUrl;
    protected ?int $videoDuration;
    protected ?string $videoProvider;

    /**
     * Initialize video specific properties
     */
    protected function init(array $data): void
    {
        $this->videoUrl = $data['video_url'] ?? null;
        $this->videoDuration = $data['video_duration'] ?? null;
        $this->videoProvider = $data['video_provider'] ?? null;
    }

    /**
     * Get the video URL
     */
    public function getVideoUrl(): ?string
    {
        return $this->videoUrl;
    }

    /**
     * Get the video duration in seconds
     */
    public function getVideoDuration(): ?int
    {
        return $this->videoDuration;
    }

    /**
     * Get the video provider
     */
    public function getVideoProvider(): ?string
    {
        return $this->videoProvider;
    }

    /**
     * Get all properties as an array
     */
    public function toArray(): array
    {
        return array_merge(parent::toArray(), [
            'video_url' => $this->videoUrl,
            'video_duration' => $this->videoDuration,
            'video_provider' => $this->videoProvider,
        ]);
    }
}
