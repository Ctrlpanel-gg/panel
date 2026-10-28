<?php

namespace App\Exceptions\Server;

/**
 * Thrown when a user does not have enough credits for an action.
 */
class InsufficientCreditsException extends ServerException
{
    /**
     * @param  string  $message
     * @param  \Throwable|null  $previous
     */
    public function __construct(string $message = 'Insufficient credits.', ?\Throwable $previous = null)
    {
        parent::__construct($message, 422, $previous);
    }
}
