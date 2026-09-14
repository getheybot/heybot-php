<?php

declare(strict_types=1);

namespace Heybot\Webhook\ValueObjects;

readonly class StatusError
{
    public function __construct(
        public int $code,
        public string $title,
        public ?string $message,
        public ?string $details,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            code: (int) $data['code'],
            title: $data['title'],
            message: $data['message'] ?? null,
            details: $data['error_data']['details'] ?? null,
        );
    }
}
