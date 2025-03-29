<?php

namespace App\Data\Content;

class Course extends Content
{
    protected ?array $topics;
    protected ?array $competencies;

    /**
     * Initialize course specific properties
     */
    protected function init(array $data): void
    {
        $this->topics = $data['topics'] ?? null;
        $this->competencies = $data['competencies'] ?? null;
    }

    /**
     * Get the course topics
     */
    public function getTopics(): ?array
    {
        return $this->topics;
    }

    /**
     * Get the course competencies
     */
    public function getCompetencies(): ?array
    {
        return $this->competencies;
    }

    /**
     * Get all properties as an array
     */
    public function toArray(): array
    {
        return array_merge(parent::toArray(), [
            'topics' => $this->topics,
            'competencies' => $this->competencies,
        ]);
    }
}
