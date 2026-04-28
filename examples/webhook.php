<?php

declare(strict_types=1);

require __DIR__.'/../vendor/autoload.php';

use Heybot\Webhook\Events\AudioEvent;
use Heybot\Webhook\Events\ButtonEvent;
use Heybot\Webhook\Events\ContactEvent;
use Heybot\Webhook\Events\DocumentEvent;
use Heybot\Webhook\Events\ImageEvent;
use Heybot\Webhook\Events\InteractiveEvent;
use Heybot\Webhook\Events\LocationEvent;
use Heybot\Webhook\Events\ReactionEvent;
use Heybot\Webhook\Events\StickerEvent;
use Heybot\Webhook\Events\TextEvent;
use Heybot\Webhook\Events\UnknownEvent;
use Heybot\Webhook\Events\VideoEvent;
use Heybot\Webhook\WebhookParser;

// ── Simulated incoming payloads ────────────────────────────────────────────────

$payloads = [

    'text' => '{
        "id": "wamid.abc1",
        "timestamp": 1750275992,
        "channel": "whatsapp",
        "event": "message",
        "type": "text",
        "text": { "body": "Hello, I need help with my order." },
        "from": { "id": "16505551234", "display_name": "Alice", "phone_number": "16505551234", "username": null },
        "to": { "phone_number": "15550783881", "phone_number_id": "106540352242922" }
    }',

    'audio' => '{
        "id": "wamid.abc2",
        "timestamp": 1750275993,
        "channel": "whatsapp",
        "event": "message",
        "type": "audio",
        "audio": { "mime_type": "audio/ogg; codecs=opus", "sha256": "abc123", "url": "https://cdn.example.com/audio.ogg", "voice": true },
        "from": { "id": "16505551234", "display_name": "Alice", "phone_number": "16505551234", "username": null },
        "to": { "phone_number": "15550783881", "phone_number_id": "106540352242922" }
    }',

    'button' => '{
        "id": "wamid.abc3",
        "timestamp": 1750275994,
        "channel": "whatsapp",
        "event": "message",
        "type": "button",
        "button": { "payload": "YES_CONFIRM", "text": "Yes, confirm" },
        "from": { "id": "16505551234", "display_name": "Alice", "phone_number": "16505551234", "username": null },
        "to": { "phone_number": "15550783881", "phone_number_id": "106540352242922" }
    }',

    'contacts' => '{
        "id": "wamid.abc4c",
        "timestamp": 1750275994,
        "channel": "whatsapp",
        "event": "message",
        "type": "contacts",
        "contacts": [
            {
                "name": { "formatted_name": "John Smith", "first_name": "John", "last_name": "Smith", "middle_name": null, "prefix": null, "suffix": null },
                "phones": [{ "phone": "+16505559999", "wa_id": "16505559999", "type": "MOBILE" }],
                "emails": [{ "email": "john@example.com", "type": "WORK" }],
                "addresses": [{ "street": "1 Hacker Way", "city": "Menlo Park", "state": "CA", "zip": "94025", "country": "United States", "country_code": "US", "type": "WORK" }],
                "org": { "company": "Acme Inc", "department": "Engineering", "title": "Senior Engineer" },
                "urls": [{ "url": "https://example.com", "type": "WORK" }],
                "birthday": "1990-01-15"
            }
        ],
        "from": { "id": "16505551234", "display_name": "Alice", "phone_number": "16505551234", "username": null },
        "to": { "phone_number": "15550783881", "phone_number_id": "106540352242922" }
    }',

    'document' => '{
        "id": "wamid.abc4",
        "timestamp": 1750275995,
        "channel": "whatsapp",
        "event": "message",
        "type": "document",
        "document": { "caption": "My invoice", "filename": "invoice.pdf", "mime_type": "application/pdf", "sha256": "def456", "url": "https://cdn.example.com/invoice.pdf" },
        "from": { "id": "16505551234", "display_name": "Alice", "phone_number": "16505551234", "username": null },
        "to": { "phone_number": "15550783881", "phone_number_id": "106540352242922" }
    }',

    'image' => '{
        "id": "wamid.abc5",
        "timestamp": 1750275996,
        "channel": "whatsapp",
        "event": "message",
        "type": "image",
        "image": { "caption": "Check this out!", "mime_type": "image/jpeg", "sha256": "ghi789", "url": "https://cdn.example.com/photo.jpg" },
        "context": { "forwarded": true, "frequently_forwarded": false },
        "from": { "id": "16505551234", "display_name": "Alice", "phone_number": "16505551234", "username": null },
        "to": { "phone_number": "15550783881", "phone_number_id": "106540352242922" }
    }',

    'interactive list_reply' => '{
        "id": "wamid.abc6",
        "timestamp": 1750275997,
        "channel": "whatsapp",
        "event": "message",
        "type": "interactive",
        "interactive": {
            "type": "list_reply",
            "list_reply": { "id": "row_1", "title": "Express Shipping", "description": "Arrives in 1-2 days" }
        },
        "from": { "id": "16505551234", "display_name": "Alice", "phone_number": "16505551234", "username": null },
        "to": { "phone_number": "15550783881", "phone_number_id": "106540352242922" }
    }',

    'interactive button_reply' => '{
        "id": "wamid.abc7",
        "timestamp": 1750275998,
        "channel": "whatsapp",
        "event": "message",
        "type": "interactive",
        "interactive": {
            "type": "button_reply",
            "button_reply": { "id": "change", "title": "Change" }
        },
        "from": { "id": "16505551234", "display_name": "Alice", "phone_number": "16505551234", "username": null },
        "to": { "phone_number": "15550783881", "phone_number_id": "106540352242922" }
    }',

    'location' => '{
        "id": "wamid.abc8",
        "timestamp": 1750275999,
        "channel": "whatsapp",
        "event": "message",
        "type": "location",
        "location": { "address": "1 Hacker Way, Menlo Park, CA", "latitude": 37.4845, "longitude": -122.1477, "name": "Meta HQ", "url": "https://maps.example.com/q=meta-hq" },
        "from": { "id": "16505551234", "display_name": "Alice", "phone_number": "16505551234", "username": null },
        "to": { "phone_number": "15550783881", "phone_number_id": "106540352242922" }
    }',

    'reaction' => '{
        "id": "wamid.abc9",
        "timestamp": 1750276000,
        "channel": "whatsapp",
        "event": "message",
        "type": "reaction",
        "reaction": { "message_id": "wamid.original123", "emoji": "👍" },
        "from": { "id": "16505551234", "display_name": "Alice", "phone_number": "16505551234", "username": null },
        "to": { "phone_number": "15550783881", "phone_number_id": "106540352242922" }
    }',

    'sticker' => '{
        "id": "wamid.abc10",
        "timestamp": 1750276001,
        "channel": "whatsapp",
        "event": "message",
        "type": "sticker",
        "sticker": { "mime_type": "image/webp", "sha256": "jkl012", "url": "https://cdn.example.com/sticker.webp", "animated": false },
        "from": { "id": "16505551234", "display_name": "Alice", "phone_number": "16505551234", "username": null },
        "to": { "phone_number": "15550783881", "phone_number_id": "106540352242922" }
    }',

    'video' => '{
        "id": "wamid.abc11",
        "timestamp": 1750276002,
        "channel": "whatsapp",
        "event": "message",
        "type": "video",
        "video": { "mime_type": "video/mp4", "sha256": "mno345", "url": "https://cdn.example.com/clip.mp4" },
        "from": { "id": "16505551234", "display_name": "Alice", "phone_number": "16505551234", "username": null },
        "to": { "phone_number": "15550783881", "phone_number_id": "106540352242922" }
    }',

];

// ── Parse & handle each event ──────────────────────────────────────────────────

foreach ($payloads as $label => $json) {
    $event = WebhookParser::fromJson($json);

    echo "── {$label} ──".PHP_EOL;
    echo "  From : {$event->from->displayName} ({$event->from->phoneNumber})".PHP_EOL;

    match (true) {
        $event instanceof TextEvent => print ("  Text : {$event->body}".PHP_EOL),
        $event instanceof AudioEvent => print ("  Audio: {$event->url} | voice={$event->voice}".PHP_EOL),
        $event instanceof ButtonEvent => print ("  Button payload={$event->payload} text={$event->text}".PHP_EOL),
        $event instanceof ContactEvent => (function () use ($event) {
            foreach ($event->contacts as $c) {
                $phone = $c->phones[0]?->phone ?? 'n/a';
                echo "  Contact: {$c->name->formattedName} | {$phone} | org={$c->org?->company}".PHP_EOL;
            }
        })(),
        $event instanceof DocumentEvent => print ("  Doc  : {$event->filename} ({$event->mimeType}) caption={$event->caption}".PHP_EOL),
        $event instanceof ImageEvent => (function () use ($event) {
            $fwd = $event->context?->forwarded ? 'forwarded' : 'not forwarded';
            echo "  Image: {$event->url} | {$fwd}".PHP_EOL;
        })(),
        $event instanceof InteractiveEvent => (function () use ($event) {
            $label = $event->isListReply() ? 'list' : 'button';
            echo "  Interactive({$label}): [{$event->replyId}] {$event->replyTitle}".PHP_EOL;
        })(),
        $event instanceof LocationEvent => print ("  Loc  : {$event->name} ({$event->latitude}, {$event->longitude})".PHP_EOL),
        $event instanceof ReactionEvent => (function () use ($event) {
            $action = $event->isRemoval() ? 'removed reaction from' : "reacted {$event->emoji} to";
            echo "  Reaction: {$action} msg {$event->messageId}".PHP_EOL;
        })(),
        $event instanceof StickerEvent => print ("  Sticker: animated={$event->animated} {$event->url}".PHP_EOL),
        $event instanceof VideoEvent => print ("  Video: {$event->url}".PHP_EOL),
        $event instanceof UnknownEvent => print ("  Unknown type '{$event->type}'".PHP_EOL),
        default => null,
    };

    echo PHP_EOL;
}
