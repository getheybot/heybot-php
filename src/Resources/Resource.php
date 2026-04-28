<?php

declare(strict_types=1);

namespace Heybot\Resources;

use Heybot\HeybotObject;
use Heybot\HttpClient\HttpClient;

abstract class Resource
{
    public function __construct(
        protected readonly HttpClient $client,
    ) {}

    /**
     * Perform a GET request and return a HeybotObject.
     */
    protected function get(string $path, array $params = []): HeybotObject
    {
        return HeybotObject::fromArray(
            $this->client->get($path, $params)
        );
    }

    /**
     * Perform a POST request and return a HeybotObject.
     */
    protected function post(string $path, array $payload = []): HeybotObject
    {
        return HeybotObject::fromArray(
            $this->client->post($path, $payload)
        );
    }

    /**
     * Perform a DELETE request and return a HeybotObject.
     */
    protected function delete(string $path): HeybotObject
    {
        return HeybotObject::fromArray(
            $this->client->delete($path)
        );
    }
}
