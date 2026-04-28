<?php

declare(strict_types=1);

namespace Heybot\Webhook\Events;

use Heybot\Webhook\ValueObjects\Recipient;
use Heybot\Webhook\ValueObjects\Sender;

class ButtonEvent extends IncomingEvent
{
    public function __construct(
        string $id,
        int $timestamp,
        string $channel,
        string $event,
        Sender $from,
        Recipient $to,
        public readonly string $payload,
        public readonly string $text,
    ) {
        parent::__construct($id, $timestamp, $channel, $event, 'button', $from, $to);
    }
}
