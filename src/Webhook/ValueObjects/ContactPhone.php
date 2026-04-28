<?php

declare(strict_types=1);

namespace Heybot\Webhook\ValueObjects;

readonly class ContactPhone
{
    public function __construct(
        public string $phone,
        public ?string $waId,
        public ?string $type,
    ) {}

    public static function fromArray(array $d): self
    {
        return new self(
            phone: $d['phone'],
            waId: $d['wa_id'] ?? null,
            type: $d['type'] ?? null,
        );
    }
}
