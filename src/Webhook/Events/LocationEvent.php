<?php

declare(strict_types=1);

namespace Heybot\Webhook\Events;

use Heybot\Webhook\ValueObjects\Recipient;
use Heybot\Webhook\ValueObjects\Sender;

class LocationEvent extends IncomingEvent
{
    public function __construct(
        string $id,
        int $timestamp,
        string $channel,
        string $event,
        Sender $from,
        Recipient $to,
        public readonly string $address,
        public readonly float $latitude,
        public readonly float $longitude,
        public readonly ?string $name,
        public readonly ?string $url,
    ) {
        parent::__construct($id, $timestamp, $channel, $event, 'location', $from, $to);
    }
}
