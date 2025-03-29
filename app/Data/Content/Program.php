<?php

namespace App\Data\Content;

class Program extends Content
{
    /**
     * Initialize program specific properties
     */
    protected function init(array $data): void
    {
        // Program specific initialization
    }

    /**
     * Get all properties as an array
     */
    public function toArray(): array
    {
        return parent::toArray();
    }
}
