<?php

declare(strict_types=1);

namespace Heybot\Exceptions;

class HeybotException extends \RuntimeException
{
    public function __construct(string $message, int $code = 0, private readonly ?string $errorCode = null, ?\Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

    public function getErrorCode(): ?string
    {
        return $this->errorCode;
    }
}
