<?php

namespace App\Data\Content;

class PartnerContent extends Content
{
    protected ?string $partner;
    protected ?string $externalUrl;

    /**
     * Initialize partner content specific properties
     */
    protected function init(array $data): void
    {
        $this->partner = $data['partner'] ?? null;
        $this->externalUrl = $data['external_url'] ?? null;
    }

    /**
     * Get the partner name
     */
    public function getPartner(): ?string
    {
        return $this->partner;
    }

    /**
     * Get the external URL
     */
    public function getExternalUrl(): ?string
    {
        return $this->externalUrl;
    }

    /**
     * Get all properties as an array
     */
    public function toArray(): array
    {
        return array_merge(parent::toArray(), [
            'partner' => $this->partner,
            'external_url' => $this->externalUrl,
        ]);
    }
}
