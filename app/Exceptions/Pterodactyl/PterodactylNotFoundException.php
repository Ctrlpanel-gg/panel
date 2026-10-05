<?php

namespace App\Exceptions\Pterodactyl;

/**
 * Thrown when a requested resource does not exist on Pterodactyl (HTTP 404).
 *
 * This commonly happens when a server was deleted from Pterodactyl but not
 * from the panel.
 */
class PterodactylNotFoundException extends PterodactylException
{
    /**
     * @param  string  $message
     * @param  \Throwable|null  $previous
     */
    public function __construct(string $message = 'Resource does not exist on Pterodactyl. Was a server deleted from Pterodactyl but not from the panel?', ?\Throwable $previous = null)
    {
        parent::__construct($message, 404, $previous);
    }

    /**
     * Get a message that is safe to show to end users.
     *
     * @return string
     */
    public function getPublicMessage(): string
    {
        return 'Resource not found.';
    }
}
