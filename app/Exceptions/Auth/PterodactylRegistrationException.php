<?php

namespace App\Exceptions\Auth;

use Exception;

/**
 * Thrown when a user account cannot be created on Pterodactyl during
 * registration.
 */
class PterodactylRegistrationException extends Exception
{
    /**
     * @param  string  $message
     * @param  \Throwable|null  $previous
     */
    public function __construct(string $message = 'Failed to create account on Pterodactyl.', ?\Throwable $previous = null)
    {
        parent::__construct($message, 0, $previous);
    }
}
