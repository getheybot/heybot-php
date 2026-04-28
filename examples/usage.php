<?php

declare(strict_types=1);

require_once __DIR__.'/vendor/autoload.php';

use Heybot\Client;
use Heybot\Exceptions\AuthenticationException;
use Heybot\Exceptions\InvalidRequestException;
use Heybot\Exceptions\RateLimitException;

// ── 1. Instantiate the client ──────────────────────────────────────────────────

$whatsapp = new Client('YOUR_API_KEY');

// ── 2. Send a plain text message ───────────────────────────────────────────────

$message = $whatsapp->message->send([
    'to' => '+521234567890',
    'type' => 'text',
    'text' => [
        'body' => 'Hello from Heybot! 👋',
        'preview_url' => false,
    ],
]);

// ── 3. Error handling ──────────────────────────────────────────────────────────

try {
    $whatsapp->message->send([
        'to' => '+521234567890',
        'type' => 'text',
        'text' => ['body' => 'Hello!'],
    ]);
} catch (AuthenticationException $e) {
    echo 'Invalid API key: '.$e->getMessage();
} catch (RateLimitException $e) {
    echo 'Rate limited — retry after a moment.';
} catch (InvalidRequestException $e) {
    echo 'Bad request ('.$e->getErrorCode().'): '.$e->getMessage();
}
