<?php

declare(strict_types=1);

namespace Heybot\Webhook\ValueObjects;

readonly class Sender
{
    public function __construct(
        public string $id,
        public string $displayName,
        public string $phoneNumber,
        public ?string $username,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'],
            displayName: $data['display_name'],
            phoneNumber: $data['phone_number'],
            username: $data['username'] ?? null,
        );
    }
}
