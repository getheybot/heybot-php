<?php

declare(strict_types=1);

namespace Heybot\Webhook\Events;

use Heybot\Webhook\ValueObjects\Recipient;
use Heybot\Webhook\ValueObjects\Sender;

class InteractiveEvent extends IncomingEvent
{
    public function __construct(
        string $id,
        int $timestamp,
        string $channel,
        string $event,
        Sender $from,
        Recipient $to,
        /** @var 'list_reply'|'button_reply' */
        public readonly string $interactiveType,
        public readonly string $replyId,
        public readonly string $replyTitle,
        public readonly ?string $replyDescription,
    ) {
        parent::__construct($id, $timestamp, $channel, $event, 'interactive', $from, $to);
    }

    public function isListReply(): bool
    {
        return $this->interactiveType === 'list_reply';
    }

    public function isButtonReply(): bool
    {
        return $this->interactiveType === 'button_reply';
    }
}
