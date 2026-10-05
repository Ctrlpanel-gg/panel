<?php

namespace App\Exceptions\Pterodactyl;

/**
 * Thrown when the panel cannot connect to a Pterodactyl node or the
 * Pterodactyl API is unreachable.
 */
class PterodactylConnectionException extends PterodactylException
{
    /**
     * @param  string  $message
     * @param  \Throwable|null  $previous
     */
    public function __construct(string $message = 'Unable to connect to Pterodactyl node. Please check if the node is online and accessible.', ?\Throwable $previous = null)
    {
        parent::__construct($message, null, $previous);
    }

    /**
     * Get a message that is safe to show to end users.
     *
     * @return string
     */
    public function getPublicMessage(): string
    {
        return 'Unable to connect to the server node. Please try again later.';
    }
}
