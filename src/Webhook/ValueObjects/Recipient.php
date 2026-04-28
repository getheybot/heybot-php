<?php

declare(strict_types=1);

namespace Heybot\Webhook\ValueObjects;

readonly class Recipient
{
    public function __construct(
        public string $phoneNumber,
        public string $phoneNumberId,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            phoneNumber: $data['phone_number'],
            phoneNumberId: $data['phone_number_id'],
        );
    }
}
