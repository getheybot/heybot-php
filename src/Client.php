<?php

declare(strict_types=1);

namespace Heybot;

use Heybot\Exceptions\AuthenticationException;
use Heybot\HttpClient\HttpClient;
use Heybot\Resources\Contact;
use Heybot\Resources\Message;
use Heybot\Resources\Template;

/**
 * Heybot PHP Client
 *
 * @property-read Message  $message
 * @property-read Contact  $contact
 * @property-read Template $template
 */
class Client
{
    private const API_BASE = 'https://api.heybot.cloud/v1/whatsapp';

    private HttpClient $httpClient;

    /** @var array<string, object> */
    private array $resourceCache = [];

    private const array RESOURCE_MAP = [
        'message' => Message::class,
        'template' => Template::class,
    ];

    public function __construct(
        private readonly string $apiKey,
        private readonly array $options = [],
    ) {
        if (empty($this->apiKey)) {
            throw new AuthenticationException('API key cannot be empty.');
        }

        $this->httpClient = new HttpClient(
            apiKey: $this->apiKey,
            baseUrl: $this->options['api_base'] ?? self::API_BASE,
            timeout: $this->options['timeout'] ?? 30,
        );
    }

    /**
     * Magic getter — returns lazy-loaded resource instances.
     *
     * Usage:
     *   $whatsapp->message->send([...]);
     *   $whatsapp->contact->retrieve('id_123');
     */
    public function __get(string $name): object
    {
        if (! isset(self::RESOURCE_MAP[$name])) {
            throw new \BadMethodCallException("Unknown resource: \"{$name}\".");
        }

        return $this->resourceCache[$name] ??= new (self::RESOURCE_MAP[$name])($this->httpClient);
    }

    public function getApiKey(): string
    {
        return $this->apiKey;
    }
}
