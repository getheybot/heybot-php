<?php

declare(strict_types=1);

namespace Heybot\Exceptions;

// ── Base ───────────────────────────────────────────────────────────────────────

class HeybotException extends \RuntimeException
{
    public function __construct(
        string $message,
        int $code = 0,
        private readonly ?string $errorCode = null,
        ?\Throwable $previous = null,
    ) {
        parent::__construct($message, $code, $previous);
    }

    public function getErrorCode(): ?string
    {
        return $this->errorCode;
    }
}

// ── Concrete exceptions ────────────────────────────────────────────────────────

class ApiException extends HeybotException {}

class AuthenticationException extends HeybotException {}

class RateLimitException extends HeybotException {}

class InvalidRequestException extends HeybotException {}
