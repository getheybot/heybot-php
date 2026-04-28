<?php

declare(strict_types=1);

namespace Heybot\Webhook\Events;

use Heybot\Webhook\ValueObjects\Recipient;
use Heybot\Webhook\ValueObjects\Sender;

class StickerEvent extends IncomingEvent
{
    public function __construct(
        string $id,
        int $timestamp,
        string $channel,
        string $event,
        Sender $from,
        Recipient $to,
        public readonly string $mimeType,
        public readonly string $sha256,
        public readonly string $url,
        public readonly bool $animated,
    ) {
        parent::__construct($id, $timestamp, $channel, $event, 'sticker', $from, $to);
    }
}
