<?php

namespace App\Exceptions\Server;

use Exception;

/**
 * Base exception for server provisioning and upgrade failures.
 *
 * Used by the web UI to flash a friendly message to the user.
 */
class ServerException extends Exception
{
    /**
     * The HTTP status code to return to the client, if applicable.
     *
     * @var int
     */
    protected int $statusCode;

    /** Optional safe message for clients. Falls back to message. */
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

    /**
     * Get the HTTP status code for this exception.
     *
     * @return int
     */
    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    /** Safe message for clients. Falls back to message. */
    public function getPublicMessage(): string
    {
        return $this->publicMessage ?? $this->getMessage();
    }
}
