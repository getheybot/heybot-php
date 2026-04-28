<?php

declare(strict_types=1);

namespace Heybot\Webhook\ValueObjects;

readonly class ForwardContext
{
    public function __construct(
        public bool $forwarded,
        public bool $frequentlyForwarded,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            forwarded: $data['forwarded'] ?? false,
            frequentlyForwarded: $data['frequently_forwarded'] ?? false,
        );
    }
}
