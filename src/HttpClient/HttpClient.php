<?php

declare(strict_types=1);

namespace Heybot\HttpClient;

use Heybot\Exceptions\ApiException;
use Heybot\Exceptions\AuthenticationException;
use Heybot\Exceptions\InvalidRequestException;
use Heybot\Exceptions\RateLimitException;
use Symfony\Component\HttpClient\HttpClient as SymfonyHttpClient;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class HttpClient
{
    private HttpClientInterface $client;

    public function __construct(
        private readonly string $apiKey,
        private readonly string $baseUrl,
        private readonly int $timeout = 30,
    ) {
        $this->client = SymfonyHttpClient::create([
            'headers' => [
                'Authorization' => 'Bearer '.$this->apiKey,
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
                'User-Agent' => 'heybot-php/1.0',
            ],
            'timeout' => $this->timeout,
        ]);
    }

    // ── Public verbs ───────────────────────────────────────────────────────────

    public function get(string $path, array $params = []): array
    {
        return $this->request('GET', $path, query: $params);
    }

    public function post(string $path, array $payload = []): array
    {
        return $this->request('POST', $path, body: $payload);
    }

    public function delete(string $path): array
    {
        return $this->request('DELETE', $path);
    }

    // ── Core request ───────────────────────────────────────────────────────────

    private function request(string $method, string $path, array $body = [], array $query = []): array
    {
        $options = [];

        if (! empty($body)) {
            $options['json'] = $body;
        }

        if (! empty($query)) {
            $options['query'] = $query;
        }

        try {
            $response = $this->client->request($method, $this->baseUrl.$path, $options);
            $statusCode = $response->getStatusCode();
            $decoded = $response->toArray(throw: false);
        } catch (\Throwable $e) {
            throw new ApiException($e->getMessage());
        }

        $this->throwOnError($statusCode, $decoded);

        return $decoded;
    }

    // ── Error mapping ──────────────────────────────────────────────────────────

    private function throwOnError(int $status, array $body): void
    {
        if ($status >= 200 && $status < 300) {
            return;
        }

        $message = $body['error']['message'] ?? 'Unknown API error.';
        $code = $body['error']['code'] ?? null;

        match (true) {
            $status === 401 => throw new AuthenticationException($message, $status),
            $status === 429 => throw new RateLimitException($message, $status),
            $status >= 400 && $status < 500 => throw new InvalidRequestException($message, $status, $code),
            default => throw new ApiException($message, $status),
        };
    }
}
