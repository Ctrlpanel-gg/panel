<?php

namespace App\Exceptions\Pterodactyl;

/**
 * Pterodactyl token lacks permission (HTTP 403).
 */
class PterodactylPermissionException extends PterodactylException
{
    /**
     * @param  string  $message
     * @param  \Throwable|null  $previous
     */
    public function __construct(string $message = 'No permission on Pterodactyl. Check the Pterodactyl token and its permissions.', ?\Throwable $previous = null)
    {
        parent::__construct($message, 403, $previous);
    }

    public function getPublicMessage(): string
    {
        return 'Internal configuration error. Please contact an administrator.';
    }
}
