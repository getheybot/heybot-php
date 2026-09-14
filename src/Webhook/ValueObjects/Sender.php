<?php

declare(strict_types=1);

namespace Heybot\Webhook\ValueObjects;

readonly class Sender
{
    public function __construct(
        public string $id,
        public string $displayName,
        public ?string $phoneNumber,
        public ?string $username,
        /** Business-Scoped User ID — identifies the sender when their phone number is withheld. */
        public ?string $bsuid = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'],
            displayName: $data['display_name'],
            phoneNumber: $data['phone_number'] ?? null,
            username: $data['username'] ?? null,
            bsuid: $data['bsuid'] ?? null,
        );
    }
}
