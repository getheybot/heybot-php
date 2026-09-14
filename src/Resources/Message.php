<?php

declare(strict_types=1);

namespace Heybot\Resources;

use Heybot\Exceptions\InvalidRequestException;
use Heybot\HeybotObject;

class Message extends Resource
{
    /**
     * Send a WhatsApp message.
     *
     * Exactly one of `to` or `recipient` must be provided:
     * - `to`: recipient's phone number, without a leading "+" (e.g. "521234567890")
     * - `recipient`: recipient's Business-Scoped User ID (BSUID)
     *
     * @param  array{
     *     to?:        string,
     *     recipient?: string,
     *     type:       'text'|'image'|'document'|'template',
     *     text?:      array{body: string, preview_url?: bool},
     *     image?:     array{url: string, caption?: string},
     *     template?:  array{name: string, language: array{code: string}},
     * } $params
     *
     * @throws InvalidRequestException if both or neither of "to"/"recipient" are given
     *
     * @example
     *   $whatsapp->message->send([
     *       'to'   => '521234567890',
     *       'type' => 'text',
     *       'text' => ['body' => 'Hello from Heybot!'],
     *   ]);
     *
     *   $whatsapp->message->send([
     *       'recipient' => 'BSUID_ABC123',
     *       'type'      => 'text',
     *       'text'      => ['body' => 'Hello from Heybot!'],
     *   ]);
     */
    public function send(array $params): HeybotObject
    {
        $hasTo = ! empty($params['to']);
        $hasRecipient = ! empty($params['recipient']);

        if ($hasTo === $hasRecipient) {
            throw new InvalidRequestException(
                $hasTo
                    ? 'Only one of "to" or "recipient" may be provided, not both.'
                    : 'Either "to" (phone number) or "recipient" (BSUID) is required.'
            );
        }

        return $this->post('/message', $params);
    }
}
