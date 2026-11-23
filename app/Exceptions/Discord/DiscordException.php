<?php

namespace App\Exceptions\Discord;

use Exception;

/**
 * Discord API request failed. Usually logged, not shown.
 */
class DiscordException extends Exception
{
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

    public function getStatusCode(): ?int
    {
        return $this->statusCode;
    }
}
