<?php

namespace App\Exceptions\Pterodactyl;

use Exception;

/**
 * Base exception for Pterodactyl API failures. Raw detail goes to logs;
 * clients only see getPublicMessage().
 */
class PterodactylException extends Exception
{
    protected ?int $statusCode;

    protected ?string $publicMessage = null;

    /**
     * @param  string  $message  internal detail, logs only
     * @param  int|null  $statusCode  HTTP status from Pterodactyl, if any
     * @param  \Throwable|null  $previous
     */
    public function __construct(string $message = '', ?int $statusCode = null, ?\Throwable $previous = null)
    {
        parent::__construct($message, 0, $previous);

        $this->statusCode = $statusCode;
    }

    public function getStatusCode(): ?int
    {
        return $this->statusCode;
    }

    public function getPublicMessage(): string
    {
        return $this->publicMessage ?? 'Failed to complete the request. Please try again later.';
    }
}
