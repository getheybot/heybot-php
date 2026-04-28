<?php

declare(strict_types=1);

namespace Heybot\Webhook\ValueObjects;

readonly class ContactOrg
{
    public function __construct(
        public ?string $company,
        public ?string $department,
        public ?string $title,
    ) {}

    public static function fromArray(array $d): self
    {
        return new self(
            company: $d['company'] ?? null,
            department: $d['department'] ?? null,
            title: $d['title'] ?? null,
        );
    }
}
