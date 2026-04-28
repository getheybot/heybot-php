<?php

declare(strict_types=1);

namespace Heybot\Webhook\Events;

use Heybot\Webhook\ValueObjects\Recipient;
use Heybot\Webhook\ValueObjects\Sender;

abstract class IncomingEvent
{
    public function __construct(
        public readonly string $id,
        public readonly int $timestamp,
        public readonly string $channel,
        public readonly string $event,
        public readonly string $type,
        public readonly Sender $from,
        public readonly Recipient $to,
    ) {}
}
