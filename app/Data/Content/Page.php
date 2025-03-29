<?php

namespace App\Data\Content;

class Page extends Content
{
    protected ?string $content;

    /**
     * Initialize page specific properties
     */
    protected function init(array $data): void
    {
        $this->content = $data['content'] ?? null;
    }

    /**
     * Get the page content
     */
    public function getContent(): ?string
    {
        return $this->content;
    }

    /**
     * Get all properties as an array
     */
    public function toArray(): array
    {
        return array_merge(parent::toArray(), [
            'content' => $this->content,
        ]);
    }
}
