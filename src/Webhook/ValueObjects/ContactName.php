<?php

declare(strict_types=1);

namespace Heybot\Webhook\ValueObjects;

readonly class ContactName
{
    public function __construct(
        public string $formattedName,
        public ?string $firstName,
        public ?string $lastName,
        public ?string $middleName,
        public ?string $prefix,
        public ?string $suffix,
    ) {}

    public static function fromArray(array $d): self
    {
        return new self(
            formattedName: $d['formatted_name'],
            firstName: $d['first_name'] ?? null,
            lastName: $d['last_name'] ?? null,
            middleName: $d['middle_name'] ?? null,
            prefix: $d['prefix'] ?? null,
            suffix: $d['suffix'] ?? null,
        );
    }
}
