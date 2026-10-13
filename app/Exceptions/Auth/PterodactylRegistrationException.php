<?php

namespace App\Exceptions\Auth;

use Exception;

/**
 * Pterodactyl account creation failed during registration.
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
