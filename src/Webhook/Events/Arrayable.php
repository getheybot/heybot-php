<?php

declare(strict_types=1);

namespace Heybot\Webhook\Events;

/**
 * Recursively converts an object's public properties into a plain array,
 * descending into nested value objects and arrays of them.
 */
trait Arrayable
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return array_map(self::normalizeValue(...), get_object_vars($this));
    }

    private static function normalizeValue(mixed $value): mixed
    {
        if (is_array($value)) {
            return array_map(self::normalizeValue(...), $value);
        }

        if ($value instanceof \BackedEnum) {
            return $value->value;
        }

        if (is_object($value)) {
            return method_exists($value, 'toArray')
                ? $value->toArray()
                : array_map(self::normalizeValue(...), get_object_vars($value));
        }

        return $value;
    }
}
