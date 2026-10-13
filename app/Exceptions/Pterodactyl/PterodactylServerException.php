<?php

namespace App\Exceptions\Pterodactyl;

/**
 * Pterodactyl 5xx.
 */
class PterodactylServerException extends PterodactylException
{
    /**
     * @param  string  $message
     * @param  int  $statusCode
     * @param  \Throwable|null  $previous
     */
    public function __construct(string $message = 'Pterodactyl server error.', int $statusCode = 500, ?\Throwable $previous = null)
    {
        parent::__construct($message, $statusCode, $previous);
    }

    public function getPublicMessage(): string
    {
        return 'Service temporarily unavailable. Please try again later.';
    }
}
