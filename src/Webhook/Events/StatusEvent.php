<?php

declare(strict_types=1);

namespace Heybot\Webhook\Events;

use Heybot\Webhook\ValueObjects\Recipient;
use Heybot\Webhook\ValueObjects\StatusError;

/**
 * A delivery-status update for a previously sent message
 * (e.g. sent, delivered, read, failed).
 */
readonly class StatusEvent implements WebhookEvent
{
    use Arrayable;

    /**
     * @param  'sent'|'delivered'|'read'|'failed'  $status
     */
    public function __construct(
        public string $messageId,
        public int $timestamp,
        public string $channel,
        public string $status,
        public string $recipientId,
        public Recipient $to,
        public ?string $conversationId,
        public ?string $conversationOrigin,
        public ?bool $billable,
        public ?string $pricingCategory,
        public ?StatusError $error,
    ) {}

    public function isSent(): bool
    {
        return $this->status === 'sent';
    }

    public function isDelivered(): bool
    {
        return $this->status === 'delivered';
    }

    public function isRead(): bool
    {
        return $this->status === 'read';
    }

    public function isFailed(): bool
    {
        return $this->status === 'failed';
    }

    public function isMessage(): bool
    {
        return false;
    }

    public function isDeliveryStatus(): bool
    {
        return true;
    }
}
