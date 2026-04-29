<?php

namespace App\Services;

use App\Settings\GeneralSettings;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class CaptchaService
{
    protected ?string $version;
    protected ?string $siteKey;
    protected ?string $secretKey;

    public function __construct(GeneralSettings $settings)
    {
        $this->version = $settings->recaptcha_version;
        $this->siteKey = $settings->recaptcha_site_key;
        $this->secretKey = $settings->recaptcha_secret_key;
    }

    /**
     * Verify the captcha token.
     *
     * @param string|null $token
     * @param string|null $ip
     * @return bool
     */
    public function verify(?string $token, ?string $ip = null): bool
    {
        if (!$this->version) {
            return true;
        }

        if (empty($token)) {
            return false;
        }

        try {
            return ($this->version === 'turnstile') 
                ? $this->verifyTurnstile($token, $ip) 
                : $this->verifyReCaptcha($token, $ip);
        } catch (\Exception $e) {
            Log::error("Captcha verification error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Verify Google reCAPTCHA (v2 and v3).
     */
    protected function verifyReCaptcha(string $token, ?string $ip): bool
    {
        $response = Http::asForm()->post('https://www.google.com/recaptcha/api/siteverify', [
            'secret' => $this->secretKey,
            'response' => $token,
            'remoteip' => $ip,
        ]);

        $data = $response->json();

        if (!($data['success'] ?? false)) {
            return false;
        }

        if ($this->version === 'v3') {
            return ($data['score'] ?? 0) >= 0.5;
        }

        return true;
    }

    /**
     * Verify Cloudflare Turnstile.
     */
    protected function verifyTurnstile(string $token, ?string $ip): bool
    {
        $response = Http::asForm()->post('https://challenges.cloudflare.com/turnstile/v0/siteverify', [
            'secret' => $this->secretKey,
            'response' => $token,
            'remoteip' => $ip,
        ]);

        return $response->json()['success'] ?? false;
    }

    /**
     * Render the scripts needed for the active captcha version.
     */
    public function renderScripts(): string
    {
        if (!$this->version || !$this->siteKey) {
            return '';
        }

        switch ($this->version) {
            case 'v2':
                return '<script src="https://www.google.com/recaptcha/api.js" async defer></script>';
            case 'v3':
                return '<script src="https://www.google.com/recaptcha/api.js?render=' . $this->siteKey . '"></script>';
            case 'turnstile':
                return '<script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>';
            default:
                return '';
        }
    }

    /**
     * Render the widget HTML for the active captcha version.
     */
    public function renderWidget(): string
    {
        if (!$this->version || !$this->siteKey) {
            return '';
        }

        switch ($this->version) {
            case 'v2':
                return '<div class="g-recaptcha" data-sitekey="' . $this->siteKey . '" data-theme="dark" data-callback="recaptchaCallback"></div>
                        <input type="hidden" name="captcha" id="captcha">
                        <script>
                            function recaptchaCallback(token) {
                                document.getElementById("captcha").value = token;
                            }
                        </script>';
            case 'v3':
                return '<input type="hidden" name="captcha" id="captcha">
                        <script>
                            grecaptcha.ready(function() {
                                grecaptcha.execute("' . $this->siteKey . '", {action: "submit"}).then(function(token) {
                                    document.getElementById("captcha").value = token;
                                });
                            });
                        </script>';
            case 'turnstile':
                return '<div class="cf-turnstile" data-sitekey="' . $this->siteKey . '" data-theme="dark" data-callback="turnstileCallback"></div>
                        <input type="hidden" name="captcha" id="captcha">
                        <script>
                            function turnstileCallback(token) {
                                document.getElementById("captcha").value = token;
                            }
                        </script>';
            default:
                return '';
        }
    }

    public function isEnabled(): bool
    {
        return !empty($this->version);
    }
}
