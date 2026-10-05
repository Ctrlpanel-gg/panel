<?php

namespace App\Exceptions\Server;

/**
 * Thrown when no node is available for a product in the selected location.
 */
class NoAvailableNodeException extends ServerException
{
    /**
     * @param  string  $message
     * @param  \Throwable|null  $previous
     */
    public function __construct(string $message = 'No available nodes for this product in the selected location.', ?\Throwable $previous = null)
    {
        parent::__construct($message, 422, $previous);
    }
}
