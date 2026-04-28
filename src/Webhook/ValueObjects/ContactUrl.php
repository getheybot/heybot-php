<?php

declare(strict_types=1);

namespace Heybot\Webhook\ValueObjects;

readonly class ContactUrl
{
    public function __construct(
        public string $url,
        public ?string $type,
    ) {}

    public static function fromArray(array $d): self
    {
        return new self(
            url: $d['url'],
            type: $d['type'] ?? null,
        );
    }
}
