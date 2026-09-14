# WhatsApp Client for PHP

Official PHP client for the [Heybot WhatsApp API](https://heybot.cloud).

## Requirements

- PHP 8.4+
- Composer

## Installation

```bash
composer require getheybot/heybot-php
```

## Quick Start

```php
use Heybot\Client;

$heybot = new Client('your-api-key');
```

---

## Laravel Octane

This package is safe to use with [Laravel Octane](https://laravel.com/docs/octane).
It holds no static or global mutable state — the only static member,
`Client::RESOURCE_MAP`, is a `const` — and `Client`/`HttpClient` instances
carry no per-request data, so they can be safely reused across requests in a
long-running Octane worker.

To avoid recreating the underlying HTTP client on every request, bind
`Client` as a singleton:

```php
// AppServiceProvider::register()
$this->app->singleton(Client::class, fn () => new Client(config('services.heybot.key')));
```

Only avoid baking per-request-varying data (e.g. a per-user API key) into a
singleton at boot — resolve `Client` fresh instead if the API key needs to
change between requests.

---

## Sending Messages

Every message needs exactly one recipient identifier — either:

- `to` — the recipient's phone number, **without** a leading `+` (e.g. `'521234567890'`)
- `recipient` — the recipient's [Business-Scoped User ID (BSUID)](https://developers.facebook.com/documentation/business-messaging/whatsapp/business-scoped-user-ids/)

Providing both, or neither, throws `Heybot\Exceptions\InvalidRequestException`.

### Text

```php
$heybot->message->send([
    'to'   => '521234567890',
    'type' => 'text',
    'text' => ['body' => 'Hello from Heybot!'],
]);

// Or, targeting a BSUID instead of a phone number:
$heybot->message->send([
    'recipient' => 'BSUID_ABC123',
    'type'      => 'text',
    'text'      => ['body' => 'Hello from Heybot!'],
]);
```

### Image

```php
$heybot->message->send([
    'to'    => '521234567890',
    'type'  => 'image',
    'image' => [
        'url'     => 'https://example.com/photo.jpg',
        'caption' => 'Check this out!',
    ],
]);
```

### Document

```php
$heybot->message->send([
    'to'       => '521234567890',
    'type'     => 'document',
    'document' => [
        'url'      => 'https://example.com/file.pdf',
        'filename' => 'invoice.pdf',
        'caption'  => 'Your invoice',
    ],
]);
```

### Template

```php
$heybot->message->send([
    'to'       => '521234567890',
    'type'     => 'template',
    'template' => [
        'name'     => 'hello_world',
        'language' => ['code' => 'en_US'],
    ],
]);
```
---

## Webhooks

Use `WebhookParser` to parse incoming webhook payloads into typed event objects.

```php
use Heybot\Webhook\WebhookParser;

// From a raw JSON string (e.g. request body)
$event = WebhookParser::fromJson($request->getContent());

// Or from an already-decoded array
$event = WebhookParser::parse($payload);
```

### Parsing raw Meta webhooks

If you receive webhooks directly from Meta's WhatsApp Cloud API (rather than
Heybot's normalized format), use `MetaWebhookParser` instead. Meta batches
webhook calls, so parsing returns an array of events — covering both inbound
messages and message status updates (sent/delivered/read/failed).

`parse()`/`fromJson()` accept either the full webhook envelope
(`{"entry": [...]}`) or a single already-unwrapped `change` fragment
(`{"value": {...}}`), if that's what you're handed.

```php
use Heybot\Webhook\MetaWebhookParser;

$events = MetaWebhookParser::fromJson($request->getContent());
// Or: MetaWebhookParser::parse($payload);

foreach ($events as $event) {
    if ($event->isDeliveryStatus()) {
        match (true) {
            $event->isSent()      => handleSent($event->messageId),
            $event->isDelivered() => handleDelivered($event->messageId),
            $event->isRead()      => handleRead($event->messageId),
            $event->isFailed()    => handleFailed($event->messageId, $event->error),
        };
        continue;
    }

    // $event->isMessage() is true here — it's an IncomingEvent
    // (TextEvent, ImageEvent, ...), same types WebhookParser produces,
    // handled the same way (see below).
}
```

> **Note:** Meta's raw webhook only provides a media `id` for
> audio/image/video/document/sticker messages, not a downloadable URL. For
> these events, `$event->mediaId` is set and `$event->url` is `null` — fetch
> the URL yourself via the Media API. `WebhookParser` (Heybot's normalized
> format) continues to populate `$event->url` directly.

### Common event methods

Every event — from `WebhookParser` or `MetaWebhookParser`, message or status —
implements `Heybot\Webhook\Events\WebhookEvent`:

```php
$event->isMessage();        // bool — true for TextEvent, ImageEvent, ... (not StatusEvent)
$event->isDeliveryStatus(); // bool — true only for StatusEvent
$event->toArray();          // array<string, mixed> — recursive plain-array representation
```

### Business-Scoped User IDs (BSUID)

When [Business-Scoped User IDs](https://developers.facebook.com/documentation/business-messaging/whatsapp/business-scoped-user-ids/)
are enabled, Meta may withhold the sender's phone number and identify them
only by a BSUID instead.

```php
$event->from->phoneNumber; // ?string — null when the phone number is withheld
$event->from->bsuid;       // ?string — set when BSUID is in use
```

Always check `bsuid` as a fallback identifier when `phoneNumber` is `null`.

#### StatusEvent

```php
$event->messageId;          // string — ID of the message this status is about
$event->status;             // 'sent'|'delivered'|'read'|'failed'
$event->recipientId;        // string — recipient's WhatsApp ID
$event->conversationId;     // ?string
$event->conversationOrigin; // ?string — e.g. 'user_initiated', 'business_initiated'
$event->billable;           // ?bool
$event->pricingCategory;    // ?string
$event->error;              // ?StatusError — present when status is 'failed'
$event->error?->code;       // int
$event->error?->title;      // string
$event->error?->message;    // ?string
$event->error?->details;    // ?string

$event->isSent();
$event->isDelivered();
$event->isRead();
$event->isFailed();
```

### Handling events

```php
use Heybot\Webhook\Events\TextEvent;
use Heybot\Webhook\Events\ImageEvent;
use Heybot\Webhook\Events\AudioEvent;
use Heybot\Webhook\Events\VideoEvent;
use Heybot\Webhook\Events\DocumentEvent;
use Heybot\Webhook\Events\StickerEvent;
use Heybot\Webhook\Events\ButtonEvent;
use Heybot\Webhook\Events\InteractiveEvent;
use Heybot\Webhook\Events\LocationEvent;
use Heybot\Webhook\Events\ReactionEvent;
use Heybot\Webhook\Events\ContactEvent;

match (true) {
    $event instanceof TextEvent        => handleText($event->body),
    $event instanceof ImageEvent       => handleImage($event->url, $event->caption),
    $event instanceof AudioEvent       => handleAudio($event->url, $event->voice),
    $event instanceof VideoEvent       => handleVideo($event->url),
    $event instanceof DocumentEvent    => handleDoc($event->filename, $event->url),
    $event instanceof StickerEvent     => handleSticker($event->url, $event->animated),
    $event instanceof ButtonEvent      => handleButton($event->payload),
    $event instanceof InteractiveEvent => handleInteractive($event),
    $event instanceof LocationEvent    => handleLocation($event->latitude, $event->longitude),
    $event instanceof ReactionEvent    => handleReaction($event->emoji, $event->messageId),
    $event instanceof ContactEvent     => handleContacts($event->contacts),
    default                            => null,
};
```

All events share these base properties:

| Property | Type | Description |
|---|---|---|
| `$event->id` | `string` | Unique message ID |
| `$event->timestamp` | `int` | Unix timestamp |
| `$event->channel` | `string` | e.g. `"whatsapp"` |
| `$event->type` | `string` | Message type |
| `$event->from->phoneNumber` | `?string` | Sender's phone number — `null` when withheld (see [Business-Scoped User IDs](#business-scoped-user-ids-bsuid)) |
| `$event->from->bsuid` | `?string` | Business-Scoped User ID — identifies the sender when `phoneNumber` is `null` |
| `$event->from->displayName` | `string` | Sender's display name |
| `$event->to->phoneNumber` | `string` | Recipient phone number |
| `$event->to->phoneNumberId` | `string` | Recipient phone number ID |

### Event types

#### TextEvent
```php
$event->body; // string — message text
```

#### AudioEvent
```php
$event->url;      // ?string — null when only $event->mediaId is known (see note above)
$event->mediaId;  // ?string
$event->mimeType; // string
$event->sha256;   // string
$event->voice;    // bool — true if voice note
```

#### ImageEvent / VideoEvent
```php
$event->url;      // ?string — null when only $event->mediaId is known (see note above)
$event->mediaId;  // ?string
$event->mimeType; // string
$event->sha256;   // string
$event->caption;  // ?string  (image only)
$event->context?->forwarded;           // ?bool
$event->context?->frequentlyForwarded; // ?bool
```

#### DocumentEvent
```php
$event->url;      // ?string — null when only $event->mediaId is known (see note above)
$event->mediaId;  // ?string
$event->filename; // string
$event->mimeType; // string
$event->sha256;   // string
$event->caption;  // ?string
```

#### StickerEvent
```php
$event->url;      // ?string — null when only $event->mediaId is known (see note above)
$event->mediaId;  // ?string
$event->mimeType; // string
$event->sha256;   // string
$event->animated; // bool
```

#### ButtonEvent
```php
$event->payload; // string — button ID / payload
$event->text;    // string — button label
```

#### InteractiveEvent
```php
$event->interactiveType;  // 'list_reply' | 'button_reply'
$event->replyId;          // string
$event->replyTitle;       // string
$event->replyDescription; // ?string (list_reply only)

$event->isListReply();   // bool
$event->isButtonReply(); // bool
```

#### LocationEvent
```php
$event->latitude;  // float
$event->longitude; // float
$event->address;   // string
$event->name;      // ?string
$event->url;       // ?string
```

#### ReactionEvent
```php
$event->messageId; // string — ID of the message reacted to
$event->emoji;     // string — empty string means reaction was removed

$event->isRemoval(); // bool
```

#### ContactEvent
```php
foreach ($event->contacts as $contact) {
    $contact->name->formattedName; // string
    $contact->name->firstName;     // ?string
    $contact->name->lastName;      // ?string

    $contact->phones[0]->phone; // string
    $contact->phones[0]->waId;  // ?string
    $contact->phones[0]->type;  // ?string (e.g. 'MOBILE')

    $contact->emails[0]->email; // string
    $contact->emails[0]->type;  // ?string

    $contact->addresses[0]->street;      // ?string
    $contact->addresses[0]->city;        // ?string
    $contact->addresses[0]->state;       // ?string
    $contact->addresses[0]->zip;         // ?string
    $contact->addresses[0]->country;     // ?string
    $contact->addresses[0]->countryCode; // ?string

    $contact->org?->company;    // ?string
    $contact->org?->department; // ?string
    $contact->org?->title;      // ?string

    $contact->urls[0]->url;  // string
    $contact->urls[0]->type; // ?string

    $contact->birthday; // ?string (e.g. '1990-01-15')
}
```

---

## Error Handling

```php
use Heybot\Exceptions\AuthenticationException;
use Heybot\Exceptions\RateLimitException;
use Heybot\Exceptions\InvalidRequestException;
use Heybot\Exceptions\ApiException;

try {
    $heybot->message->send([...]);
} catch (AuthenticationException $e) {
    // 401 — invalid API key
} catch (RateLimitException $e) {
    // 429 — too many requests
} catch (InvalidRequestException $e) {
    // 4xx — bad request
    echo $e->getErrorCode(); // API error code if provided
} catch (ApiException $e) {
    // 5xx or network error
}
```

---

## License

MIT
