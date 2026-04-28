<?php

declare(strict_types=1);

namespace Heybot\Webhook\ValueObjects;

readonly class ContactEmail
{
    public function __construct(
        public string $email,
        public ?string $type,
    ) {}

    public static function fromArray(array $d): self
    {
        return new self(
            email: $d['email'],
            type: $d['type'] ?? null,
        );
    }
}
