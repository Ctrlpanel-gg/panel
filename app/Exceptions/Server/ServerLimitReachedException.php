<?php

namespace App\Exceptions\Server;

/**
 * Thrown when a user has reached their server limit.
 */
class ServerLimitReachedException extends ServerException
{
    /**
     * @param  string  $message
     * @param  \Throwable|null  $previous
     */
    public function __construct(string $message = 'Server limit reached.', ?\Throwable $previous = null)
    {
        parent::__construct($message, 422, $previous);
    }
}
