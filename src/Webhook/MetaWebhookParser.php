<?php

declare(strict_types=1);

namespace Heybot\Webhook;

use Heybot\Webhook\Events\AudioEvent;
use Heybot\Webhook\Events\ButtonEvent;
use Heybot\Webhook\Events\ContactEvent;
use Heybot\Webhook\Events\DocumentEvent;
use Heybot\Webhook\Events\ImageEvent;
use Heybot\Webhook\Events\IncomingEvent;
use Heybot\Webhook\Events\InteractiveEvent;
use Heybot\Webhook\Events\LocationEvent;
use Heybot\Webhook\Events\ReactionEvent;
use Heybot\Webhook\Events\StatusEvent;
use Heybot\Webhook\Events\StickerEvent;
use Heybot\Webhook\Events\TextEvent;
use Heybot\Webhook\Events\UnknownEvent;
use Heybot\Webhook\Events\VideoEvent;
use Heybot\Webhook\Events\WebhookEvent;
use Heybot\Webhook\ValueObjects\Contact;
use Heybot\Webhook\ValueObjects\ForwardContext;
use Heybot\Webhook\ValueObjects\Recipient;
use Heybot\Webhook\ValueObjects\Sender;
use Heybot\Webhook\ValueObjects\StatusError;

/**
 * Parses the raw WhatsApp Cloud API webhook payload sent directly by Meta
 * (the `entry[].changes[].value` structure), as opposed to WebhookParser,
 * which parses Heybot's already-normalized payload.
 *
 * Accepts either the full webhook envelope (`{"entry": [...]}`) or a single
 * `change` fragment (`{"value": {...}}`).
 *
 * A single Meta webhook call can batch several messages and/or statuses,
 * so parsing returns an array rather than a single event.
 */
class MetaWebhookParser
{
    /**
     * @return array<WebhookEvent>
     */
    public static function parse(array $payload): array
    {
        if (isset($payload['entry'])) {
            $events = [];

            foreach ($payload['entry'] as $entry) {
                foreach ($entry['changes'] ?? [] as $change) {
                    array_push($events, ...self::parseValue($change['value'] ?? []));
                }
            }

            return $events;
        }

        return self::parseValue($payload['value'] ?? []);
    }

    /**
     * @return array<WebhookEvent>
     */
    public static function fromJson(string $json): array
    {
        return self::parse(json_decode($json, true, flags: JSON_THROW_ON_ERROR));
    }

    // ── Private builders ───────────────────────────────────────────────────────

    /**
     * @return array<WebhookEvent>
     */
    private static function parseValue(array $value): array
    {
        $to = new Recipient(
            phoneNumber: $value['metadata']['display_phone_number'] ?? '',
            phoneNumberId: $value['metadata']['phone_number_id'] ?? '',
        );

        $namesByWaId = [];
        $namesByBsuid = [];
        foreach ($value['contacts'] ?? [] as $contact) {
            $name = $contact['profile']['name'] ?? null;
            if (isset($contact['wa_id'])) {
                $namesByWaId[$contact['wa_id']] = $name;
            }
            if (isset($contact['user_id'])) {
                $namesByBsuid[$contact['user_id']] = $name;
            }
        }

        $events = [];

        foreach ($value['messages'] ?? [] as $message) {
            $events[] = self::parseMessage($message, $to, $namesByWaId, $namesByBsuid);
        }

        foreach ($value['statuses'] ?? [] as $status) {
            $events[] = self::parseStatus($status, $to);
        }

        return $events;
    }

    private static function parseMessage(array $m, Recipient $to, array $namesByWaId, array $namesByBsuid): IncomingEvent
    {
        $phone = $m['from'] ?? null;
        $bsuid = $m['from_user_id'] ?? null;
        $displayName = ($phone !== null ? $namesByWaId[$phone] ?? null : null)
            ?? ($bsuid !== null ? $namesByBsuid[$bsuid] ?? null : null)
            ?? $phone
            ?? $bsuid
            ?? '';

        $from = new Sender(
            id: $phone ?? $bsuid ?? '',
            displayName: $displayName,
            phoneNumber: $phone,
            username: null,
            bsuid: $bsuid,
        );

        $base = [
            $m['id'],
            (int) $m['timestamp'],
            'whatsapp',
            'message',
            $from,
            $to,
        ];

        return match ($m['type']) {
            'text' => new TextEvent(...$base, body: $m['text']['body']),
            'audio' => self::parseAudio($base, $m['audio']),
            'button' => new ButtonEvent(...$base,
                payload: $m['button']['payload'],
                text: $m['button']['text'],
            ),
            'contacts' => new ContactEvent(...$base,
                contacts: array_map(Contact::fromArray(...), $m['contacts'] ?? []),
            ),
            'document' => self::parseDocument($base, $m['document']),
            'image' => self::parseImage($base, $m['image'], $m['context'] ?? null),
            'interactive' => self::parseInteractive($base, $m['interactive']),
            'location' => self::parseLocation($base, $m['location']),
            'reaction' => new ReactionEvent(...$base,
                messageId: $m['reaction']['message_id'],
                emoji: $m['reaction']['emoji'] ?? '',
            ),
            'sticker' => self::parseSticker($base, $m['sticker']),
            'video' => self::parseVideo($base, $m['video'], $m['context'] ?? null),
            default => new UnknownEvent(...$base, type: $m['type'], raw: $m),
        };
    }

    private static function parseAudio(array $base, array $d): AudioEvent
    {
        return new AudioEvent(...$base,
            mimeType: $d['mime_type'],
            sha256: $d['sha256'],
            url: null,
            voice: (bool) ($d['voice'] ?? false),
            mediaId: $d['id'],
        );
    }

    private static function parseDocument(array $base, array $d): DocumentEvent
    {
        return new DocumentEvent(...$base,
            mimeType: $d['mime_type'],
            sha256: $d['sha256'],
            url: null,
            filename: $d['filename'] ?? '',
            caption: $d['caption'] ?? null,
            mediaId: $d['id'],
        );
    }

    private static function parseImage(array $base, array $d, ?array $ctx): ImageEvent
    {
        return new ImageEvent(...$base,
            mimeType: $d['mime_type'],
            sha256: $d['sha256'],
            url: null,
            caption: $d['caption'] ?? null,
            context: $ctx !== null ? ForwardContext::fromArray($ctx) : null,
            mediaId: $d['id'],
        );
    }

    private static function parseInteractive(array $base, array $d): InteractiveEvent
    {
        $type = $d['type'];
        $reply = $d[$type];

        return new InteractiveEvent(...$base,
            interactiveType: $type,
            replyId: $reply['id'],
            replyTitle: $reply['title'],
            replyDescription: $reply['description'] ?? null,
        );
    }

    private static function parseLocation(array $base, array $d): LocationEvent
    {
        return new LocationEvent(...$base,
            address: $d['address'] ?? '',
            latitude: (float) $d['latitude'],
            longitude: (float) $d['longitude'],
            name: $d['name'] ?? null,
            url: $d['url'] ?? null,
        );
    }

    private static function parseSticker(array $base, array $d): StickerEvent
    {
        return new StickerEvent(...$base,
            mimeType: $d['mime_type'],
            sha256: $d['sha256'],
            url: null,
            animated: (bool) ($d['animated'] ?? false),
            mediaId: $d['id'],
        );
    }

    private static function parseVideo(array $base, array $d, ?array $ctx): VideoEvent
    {
        return new VideoEvent(...$base,
            mimeType: $d['mime_type'],
            sha256: $d['sha256'],
            url: null,
            context: $ctx !== null ? ForwardContext::fromArray($ctx) : null,
            mediaId: $d['id'],
        );
    }

    private static function parseStatus(array $s, Recipient $to): StatusEvent
    {
        $conversation = $s['conversation'] ?? null;
        $pricing = $s['pricing'] ?? null;

        return new StatusEvent(
            messageId: $s['id'],
            timestamp: (int) $s['timestamp'],
            channel: 'whatsapp',
            status: $s['status'],
            recipientId: $s['recipient_id'],
            to: $to,
            conversationId: $conversation['id'] ?? null,
            conversationOrigin: $conversation['origin']['type'] ?? null,
            billable: isset($pricing['billable']) ? (bool) $pricing['billable'] : null,
            pricingCategory: $pricing['category'] ?? null,
            error: isset($s['errors'][0]) ? StatusError::fromArray($s['errors'][0]) : null,
        );
    }
}
