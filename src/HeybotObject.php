<?php

declare(strict_types=1);

namespace Heybot;

/**
 * A dot-accessible, iterable wrapper around API response data.
 *
 * Mirrors Stripe's StripeObject pattern so callers can do:
 *   $response->id
 *   $response->message->status
 *   $response['to']
 */
class HeybotObject implements \ArrayAccess, \JsonSerializable
{
    /** @var array<string, mixed> */
    private array $data;

    private function __construct(array $data)
    {
        $this->data = array_map(
            static fn ($value) => is_array($value) ? new self($value) : $value,
            $data,
        );
    }

    public static function fromArray(array $data): static
    {
        return new static($data);
    }

    // ── Property access ────────────────────────────────────────────────────────

    public function __get(string $key): mixed
    {
        return $this->data[$key] ?? null;
    }

    public function __isset(string $key): bool
    {
        return isset($this->data[$key]);
    }

    // ── Array access ───────────────────────────────────────────────────────────

    public function offsetExists(mixed $offset): bool
    {
        return isset($this->data[$offset]);
    }

    public function offsetGet(mixed $offset): mixed
    {
        return $this->data[$offset] ?? null;
    }

    public function offsetSet(mixed $offset, mixed $value): void
    {
        throw new \RuntimeException('HeybotObject is immutable.');
    }

    public function offsetUnset(mixed $offset): void
    {
        throw new \RuntimeException('HeybotObject is immutable.');
    }

    // ── Serialisation ──────────────────────────────────────────────────────────

    public function toArray(): array
    {
        return array_map(
            static fn ($v) => $v instanceof self ? $v->toArray() : $v,
            $this->data,
        );
    }

    public function jsonSerialize(): array
    {
        return $this->toArray();
    }

    public function __toString(): string
    {
        return json_encode($this->toArray(), JSON_PRETTY_PRINT);
    }
}
