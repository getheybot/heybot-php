<?php

declare(strict_types=1);

namespace Heybot\Webhook\Events;

use Heybot\Webhook\ValueObjects\Contact;
use Heybot\Webhook\ValueObjects\Recipient;
use Heybot\Webhook\ValueObjects\Sender;

class ContactEvent extends IncomingEvent
{
    /**
     * @param  Contact[]  $contacts
     */
    public function __construct(
        string $id,
        int $timestamp,
        string $channel,
        string $event,
        Sender $from,
        Recipient $to,
        public readonly array $contacts,
    ) {
        parent::__construct($id, $timestamp, $channel, $event, 'contacts', $from, $to);
    }
}
