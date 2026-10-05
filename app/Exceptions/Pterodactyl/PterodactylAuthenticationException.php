<?php

namespace App\Exceptions\Pterodactyl;

/**
 * Thrown when the Pterodactyl API token is missing or invalid (HTTP 401).
 */
class PterodactylAuthenticationException extends PterodactylException
{
    /**
     * @param  string  $message
     * @param  \Throwable|null  $previous
     */
    public function __construct(string $message = 'No Pterodactyl token set or the token is invalid.', ?\Throwable $previous = null)
    {
        parent::__construct($message, 401, $previous);
    }

    /**
     * Get a message that is safe to show to end users.
     *
     * @return string
     */
    public function getPublicMessage(): string
    {
        return 'Internal configuration error. Please contact an administrator.';
    }
}
