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

## Sending Messages

### Text

```php
$heybot->message->send([
    'to'   => '+521234567890',
    'type' => 'text',
    'text' => ['body' => 'Hello from Heybot!'],
]);
```

### Image

```php
$heybot->message->send([
    'to'    => '+521234567890',
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
    'to'       => '+521234567890',
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
    'to'       => '+521234567890',
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
| `$event->from->phoneNumber` | `string` | Sender's phone number |
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
$event->url;      // string
$event->mimeType; // string
$event->sha256;   // string
$event->voice;    // bool — true if voice note
```

#### ImageEvent / VideoEvent
```php
$event->url;      // string
$event->mimeType; // string
$event->sha256;   // string
$event->caption;  // ?string  (image only)
$event->context?->forwarded;           // ?bool
$event->context?->frequentlyForwarded; // ?bool
```

#### DocumentEvent
```php
$event->url;      // string
$event->filename; // string
$event->mimeType; // string
$event->sha256;   // string
$event->caption;  // ?string
```

#### StickerEvent
```php
$event->url;      // string
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
