<?php

namespace App\Exceptions\Payment;

use Exception;

/**
 * Base exception for payment flow failures.
 */
class PaymentException extends Exception
{
    /**
     * The HTTP status code to return to the client, if applicable.
     *
     * @var int
     */
    protected int $statusCode;

    /** Optional safe message for clients. Falls back to message. */
    protected ?string $publicMessage = null;

    /**
     * @param  string  $message
     * @param  int  $statusCode
     * @param  \Throwable|null  $previous
     */
    public function __construct(string $message = '', int $statusCode = 500, ?\Throwable $previous = null)
    {
        parent::__construct($message, 0, $previous);

        $this->statusCode = $statusCode;
    }

    /**
     * Get the HTTP status code for this exception.
     *
     * @return int
     */
    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    /** Safe message for clients. Falls back to message. */
    public function getPublicMessage(): string
    {
        return $this->publicMessage ?? $this->getMessage();
    }
}
