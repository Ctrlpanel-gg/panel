<?php

namespace App\Exceptions\Server;

/**
 * Thrown when a server cannot be created.
 */
class ServerCreationException extends ServerException
{
    /**
     * @param  string  $message
     * @param  int  $statusCode
     * @param  \Throwable|null  $previous
     */
    public function __construct(string $message = 'Server creation failed.', int $statusCode = 500, ?\Throwable $previous = null)
    {
        parent::__construct($message, $statusCode, $previous);
    }
}
