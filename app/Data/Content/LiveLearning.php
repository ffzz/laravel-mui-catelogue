<?php

namespace App\Data\Content;

class LiveLearning extends Content
{
    protected ?string $startTime;
    protected ?string $endTime;
    protected ?string $location;
    protected ?string $facilitator;
    protected ?int $maxAttendees;

    /**
     * Initialize live learning specific properties
     */
    protected function init(array $data): void
    {
        $this->startTime = $data['start_time'] ?? null;
        $this->endTime = $data['end_time'] ?? null;
        $this->location = $data['location'] ?? null;
        $this->facilitator = $data['facilitator'] ?? null;
        $this->maxAttendees = $data['max_attendees'] ?? null;
    }

    /**
     * Get the live learning start time
     */
    public function getStartTime(): ?string
    {
        return $this->startTime;
    }

    /**
     * Get the live learning end time
     */
    public function getEndTime(): ?string
    {
        return $this->endTime;
    }

    /**
     * Get the live learning location
     */
    public function getLocation(): ?string
    {
        return $this->location;
    }

    /**
     * Get the live learning facilitator
     */
    public function getFacilitator(): ?string
    {
        return $this->facilitator;
    }

    /**
     * Get the live learning maximum attendees
     */
    public function getMaxAttendees(): ?int
    {
        return $this->maxAttendees;
    }

    /**
     * Get all properties as an array
     */
    public function toArray(): array
    {
        return array_merge(parent::toArray(), [
            'start_time' => $this->startTime,
            'end_time' => $this->endTime,
            'location' => $this->location,
            'facilitator' => $this->facilitator,
            'max_attendees' => $this->maxAttendees,
        ]);
    }
}
