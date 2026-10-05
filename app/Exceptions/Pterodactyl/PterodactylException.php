<?php

namespace App\Exceptions\Pterodactyl;

use Exception;

/**
 * Base exception for Pterodactyl API failures.
 *
 * Carries the HTTP status returned by Pterodactyl (when available) and a
 * human-readable hint so callers and the exception handler can respond
 * appropriately.
 */
class PterodactylException extends Exception
{
    /**
     * The HTTP status code returned by Pterodactyl, if any.
     *
     * @var int|null
     */
    protected ?int $statusCode;

    /**
     * A safe message intended to be shown to end users. When null, a generic
     * fallback is used so raw client details never leak to the public API/UI.
     *
     * @var string|null
     */
    protected ?string $publicMessage = null;

    /**
     * @param  string  $message
     * @param  int|null  $statusCode
     * @param  \Throwable|null  $previous
     */
    public function __construct(string $message = '', ?int $statusCode = null, ?\Throwable $previous = null)
    {
        parent::__construct($message, 0, $previous);

        $this->statusCode = $statusCode;
    }

    /**
     * Get the HTTP status code returned by Pterodactyl, if any.
     *
     * @return int|null
     */
    public function getStatusCode(): ?int
    {
        return $this->statusCode;
    }

    /**
     * Get a message that is safe to show to end users.
     *
     * The raw message (getMessage()) may contain internal infrastructure
     * details (node host/port, TLS/DNS errors, Guzzle traces) and must not
     * leak through the public API/UI. Subclasses override this to provide
     * their own fixed public wording.
     *
     * @return string
     */
    public function getPublicMessage(): string
    {
        return $this->publicMessage ?? 'Failed to complete the request. Please try again later.';
    }
}
