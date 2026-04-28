<?php

declare(strict_types=1);

namespace Heybot\Webhook\ValueObjects;

readonly class ContactAddress
{
    public function __construct(
        public ?string $street,
        public ?string $city,
        public ?string $state,
        public ?string $zip,
        public ?string $country,
        public ?string $countryCode,
        public ?string $type,
    ) {}

    public static function fromArray(array $d): self
    {
        return new self(
            street: $d['street'] ?? null,
            city: $d['city'] ?? null,
            state: $d['state'] ?? null,
            zip: $d['zip'] ?? null,
            country: $d['country'] ?? null,
            countryCode: $d['country_code'] ?? null,
            type: $d['type'] ?? null,
        );
    }
}
