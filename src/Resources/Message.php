<?php

declare(strict_types=1);

namespace Heybot\Resources;

use Heybot\HeybotObject;

class Message extends Resource
{
    /**
     * Send a WhatsApp message.
     *
     * @param  array{
     *     to:      string,
     *     type:    'text'|'image'|'document'|'template',
     *     text?:   array{body: string, preview_url?: bool},
     *     image?:  array{url: string, caption?: string},
     *     template?: array{name: string, language: array{code: string}},
     * } $params
     *
     * @example
     *   $whatsapp->message->send([
     *       'to'   => '+521234567890',
     *       'type' => 'text',
     *       'text' => ['body' => 'Hello from Heybot!'],
     *   ]);
     */
    public function send(array $params): HeybotObject
    {
        return $this->post('/message', $params);
    }
}
