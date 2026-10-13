<?php

namespace App\Exceptions\Payment;

/**
 * Thrown when an invoice cannot be generated or downloaded.
 */
class InvoiceException extends PaymentException
{
    /**
     * @param  string  $message
     * @param  int  $statusCode
     * @param  \Throwable|null  $previous
     */
    public function __construct(string $message = 'Invoice generation failed.', int $statusCode = 500, ?\Throwable $previous = null)
    {
        parent::__construct($message, $statusCode, $previous);
    }

    /**
     * Friendly wording for the admin error page. The technical message
     * stays in the logs via report().
     */
    public function getPublicMessage(): string
    {
        return match ($this->getStatusCode()) {
            404 => 'The requested invoice could not be found.',
            default => 'Something went wrong while preparing the invoice. Please try again later.',
        };
    }
}
