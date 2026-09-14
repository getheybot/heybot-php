<?php

declare(strict_types=1);

namespace Heybot\Webhook\Events;

use Heybot\Webhook\ValueObjects\ForwardContext;
use Heybot\Webhook\ValueObjects\Recipient;
use Heybot\Webhook\ValueObjects\Sender;

class ImageEvent extends IncomingEvent
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
        public readonly ?string $url,
        public readonly ?string $caption,
        public readonly ?ForwardContext $context,
        public readonly ?string $mediaId = null,
    ) {
        parent::__construct($id, $timestamp, $channel, $event, 'image', $from, $to);
    }
}
