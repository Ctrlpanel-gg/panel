<?php

namespace App\Services\TwoFactor;

use PragmaRX\Google2FALaravel\Facade as Google2FA;

class TotpService
{
    /**
     * Generate a new 32-character Base32 secret key.
     */
    public function generateSecret(): string
    {
        return Google2FA::generateSecretKey(32);
    }

    /**
     * Generate an inline SVG QR code.
     */
    public function getQrCodeSvg(string $email, string $secret): string
    {
        return Google2FA::getQRCodeInline(
            config('app.name', 'CtrlPanel.gg'),
            $email,
            $secret
        );
    }

    /**
     * Verify a TOTP code.
     */
    public function verifyCode(string $secret, string $code): bool
    {
        return Google2FA::verifyKey($secret, $code);
    }
}
