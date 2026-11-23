<?php

namespace App\Exceptions\Payment;

use Exception;

/**
 * Base exception for payment flow failures.
 */
class PaymentException extends Exception
{
    protected int $statusCode;

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

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    /**
     * Safe text for clients. Falls back to the message.
     */
    public function getPublicMessage(): string
    {
        return $this->publicMessage ?? $this->getMessage();
    }
}
