<?php

return [

    /*
     * Enable / disable Google2FA.
     */
    'enabled' => true,

    /*
     * Lifetime in minutes.
     *
     * In case you need your users to be asked for a one time password every time they log in
     * (with "remember me" disabled), you can set this to 0 (zero).
     */
    'lifetime' => 0, // Should be 0 since we handle verification persistence ourselves

    /*
     * Keep alive.
     *
     * If this is true, every time a user access a page, the session lifetime is updated.
     */
    'keep_alive' => true,

    /*
     * Auth Guard.
     */
    'auth_guard' => 'web',

    /*
     * Session key.
     */
    'session_var' => 'google2fa',

    /*
     * One Time Password Field Name.
     */
    'otp_input' => 'one_time_password',

    /*
     * One Time Password Window.
     */
    'window' => 1,

    /*
     * Forbid old passwords.
     */
    'forbid_old_passwords' => true,

    /*
     * User's table column for google2fa secret.
     */
    'otp_secret_column' => 'google2fa_secret',

    /*
     * Guard route name.
     */
    'guard_route' => 'login.2fa.totp',

    /*
     * QR Code Image Backend.
     */
    'qr_image_backend' => \PragmaRX\Google2FALaravel\Support\Constants::QRCODE_IMAGE_BACKEND_SVG,

    /*
     * Secret Length.
     */
    'secret_length' => 32,

];
