<?php

namespace App\Exceptions\Pterodactyl;

/**
 * Thrown when the Pterodactyl API token does not have permission to access
 * the requested resource (HTTP 403).
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
