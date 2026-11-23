<?php

namespace App\Exceptions\Server;

use Exception;

/**
 * Base exception for server provisioning and upgrade failures.
 * The status doubles as the exception code for API responses.
 */
class ServerException extends Exception
{
    protected int $statusCode;

    protected ?string $publicMessage = null;

    /**
     * @param  string  $message
     * @param  int  $statusCode
     * @param  \Throwable|null  $previous
     */
    public function __construct(string $message = '', int $statusCode = 500, ?\Throwable $previous = null)
    {
        parent::__construct($message, $statusCode, $previous);

        $this->statusCode = $statusCode;
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    /**
     * Safe text for clients. Falls back to the message.
     */
    public function getPublicMessage(): string
    {
        return $this->publicMessage ?? $this->getMessage();
    }
}
