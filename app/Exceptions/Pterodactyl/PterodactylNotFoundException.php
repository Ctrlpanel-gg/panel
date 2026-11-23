<?php

namespace App\Exceptions\Pterodactyl;

/**
 * Resource missing on Pterodactyl (HTTP 404).
 * Usually a server deleted remotely but not locally.
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

    public function getPublicMessage(): string
    {
        return 'Resource not found.';
    }
}
