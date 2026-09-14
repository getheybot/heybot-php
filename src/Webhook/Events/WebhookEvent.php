<?php

declare(strict_types=1);

namespace Heybot\Webhook\Events;

interface WebhookEvent
{
    /**
     * True for inbound messages (TextEvent, ImageEvent, ...).
     */
    public function isMessage(): bool;

    /**
     * True for delivery-status updates (StatusEvent).
     */
    public function isDeliveryStatus(): bool;

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array;
}
