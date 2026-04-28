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
use Heybot\Webhook\Events\StickerEvent;
use Heybot\Webhook\Events\TextEvent;
use Heybot\Webhook\Events\UnknownEvent;
use Heybot\Webhook\Events\VideoEvent;
use Heybot\Webhook\ValueObjects\Contact;
use Heybot\Webhook\ValueObjects\ForwardContext;
use Heybot\Webhook\ValueObjects\Recipient;
use Heybot\Webhook\ValueObjects\Sender;

class WebhookParser
{
    public static function parse(array $payload): IncomingEvent
    {
        $from = Sender::fromArray($payload['from']);
        $to = Recipient::fromArray($payload['to']);

        $base = [
            $payload['id'],
            (int) $payload['timestamp'],
            $payload['channel'],
            $payload['event'],
            $from,
            $to,
        ];

        return match ($payload['type']) {
            'text' => new TextEvent(...$base, body: $payload['text']['body']),
            'audio' => self::parseAudio($base, $payload['audio']),
            'button' => new ButtonEvent(...$base,
                payload: $payload['button']['payload'],
                text: $payload['button']['text'],
            ),
            'contacts' => new ContactEvent(...$base,
                contacts: array_map(Contact::fromArray(...), $payload['contacts']),
            ),
            'document' => self::parseDocument($base, $payload['document']),
            'image' => self::parseImage($base, $payload['image'], $payload['context'] ?? null),
            'interactive' => self::parseInteractive($base, $payload['interactive']),
            'location' => self::parseLocation($base, $payload['location']),
            'reaction' => new ReactionEvent(...$base,
                messageId: $payload['reaction']['message_id'],
                emoji: $payload['reaction']['emoji'],
            ),
            'sticker' => self::parseSticker($base, $payload['sticker']),
            'video' => self::parseVideo($base, $payload['video'], $payload['context'] ?? null),
            default => new UnknownEvent(...$base, type: $payload['type'], raw: $payload),
        };
    }

    public static function fromJson(string $json): IncomingEvent
    {
        return self::parse(json_decode($json, true, flags: JSON_THROW_ON_ERROR));
    }

    // ── Private builders ───────────────────────────────────────────────────────

    private static function parseAudio(array $base, array $d): AudioEvent
    {
        return new AudioEvent(...$base,
            mimeType: $d['mime_type'],
            sha256: $d['sha256'],
            url: $d['url'],
            voice: (bool) ($d['voice'] ?? false),
        );
    }

    private static function parseDocument(array $base, array $d): DocumentEvent
    {
        return new DocumentEvent(...$base,
            mimeType: $d['mime_type'],
            sha256: $d['sha256'],
            url: $d['url'],
            filename: $d['filename'],
            caption: $d['caption'] ?? null,
        );
    }

    private static function parseImage(array $base, array $d, ?array $ctx): ImageEvent
    {
        return new ImageEvent(...$base,
            mimeType: $d['mime_type'],
            sha256: $d['sha256'],
            url: $d['url'],
            caption: $d['caption'] ?? null,
            context: $ctx !== null ? ForwardContext::fromArray($ctx) : null,
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
            address: $d['address'],
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
            url: $d['url'],
            animated: (bool) ($d['animated'] ?? false),
        );
    }

    private static function parseVideo(array $base, array $d, ?array $ctx): VideoEvent
    {
        return new VideoEvent(...$base,
            mimeType: $d['mime_type'],
            sha256: $d['sha256'],
            url: $d['url'],
            context: $ctx !== null ? ForwardContext::fromArray($ctx) : null,
        );
    }
}
