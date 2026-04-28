<?php

declare(strict_types=1);

namespace Heybot\Webhook\Events;

use Heybot\Webhook\ValueObjects\Recipient;
use Heybot\Webhook\ValueObjects\Sender;

class ReactionEvent extends IncomingEvent
{
    public function __construct(
        string $id,
        int $timestamp,
        string $channel,
        string $event,
        Sender $from,
        Recipient $to,
        public readonly string $messageId,
        public readonly string $emoji,
    ) {
        parent::__construct($id, $timestamp, $channel, $event, 'reaction', $from, $to);
    }

    public function isRemoval(): bool
    {
        return $this->emoji === '';
    }
}
