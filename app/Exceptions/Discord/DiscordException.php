<?php

namespace App\Exceptions\Discord;

use Exception;

/**
 * Thrown when a Discord API request fails.
 *
 * These failures are typically non-fatal (logged and swallowed), but a typed
 * exception makes the intent explicit and testable.
 */
class DiscordException extends Exception
{
    /**
     * The HTTP status code returned by Discord, if any.
     *
     * @var int|null
     */
    protected ?int $statusCode;

    /**
     * @param  string  $message
     * @param  int|null  $statusCode
     * @param  \Throwable|null  $previous
     */
    public function __construct(string $message = '', ?int $statusCode = null, ?\Throwable $previous = null)
    {
        parent::__construct($message, 0, $previous);

        $this->statusCode = $statusCode;
    }

    /**
     * Get the HTTP status code returned by Discord, if any.
     *
     * @return int|null
     */
    public function getStatusCode(): ?int
    {
        return $this->statusCode;
    }
}
