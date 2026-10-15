<?php

namespace App\Exceptions\Server;

/**
 * Thrown when a node does not have enough free resources for an action.
 */
class InsufficientResourcesException extends ServerException
{
    /**
     * @param  string  $message
     * @param  \Throwable|null  $previous
     */
    public function __construct(string $message = 'Insufficient resources on the node.', ?\Throwable $previous = null)
    {
        parent::__construct($message, 422, $previous);
    }
}
