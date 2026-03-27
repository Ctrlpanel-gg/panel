<?php

namespace App\Support;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\HtmlString;

class RecaptchaV2
{
    public static function scriptTag(): HtmlString
    {
        $query = [];
        $language = config('recaptcha.default_language');

        if (is_string($language) && $language !== '') {
            $query['hl'] = $language;
        }

        $src = 'https://' . config('recaptcha.api_domain', 'www.google.com') . '/recaptcha/api.js';
        if ($query !== []) {
            $src .= '?' . http_build_query($query);
        }

        return new HtmlString('<script src="' . e($src) . '" async defer></script>');
    }

    public static function widget(): HtmlString
    {
        $siteKey = (string) config('recaptcha.api_site_key', '');
        if ($siteKey === '') {
            return new HtmlString('');
        }

        $attributes = ['sitekey' => $siteKey];
        foreach ((array) config('recaptcha.tag_attributes', []) as $key => $value) {
            if ($value === null || $value === '') {
                continue;
            }

            $attributes[$key] = $value;
        }

        $serialized = collect($attributes)
            ->map(fn (mixed $value, string $key): string => 'data-' . $key . '="' . e((string) $value) . '"')
            ->implode(' ');

        return new HtmlString('<div class="g-recaptcha" ' . $serialized . '></div>');
    }

    public static function verify(?string $token, ?string $remoteIp = null): bool
    {
        if ($token === null || $token === '') {
            return false;
        }

        $secret = (string) config('recaptcha.api_secret_key', '');
        if ($secret === '') {
            return false;
        }

        if ($remoteIp !== null && in_array($remoteIp, (array) config('recaptcha.skip_ip', []), true)) {
            return true;
        }

        $payload = [
            'secret' => $secret,
            'response' => $token,
        ];

        if ($remoteIp !== null && $remoteIp !== '') {
            $payload['remoteip'] = $remoteIp;
        }

        $response = Http::asForm()
            ->timeout((int) config('recaptcha.curl_timeout', 10))
            ->post(
                'https://' . config('recaptcha.api_domain', 'www.google.com') . '/recaptcha/api/siteverify',
                $payload
            );

        return $response->successful() && $response->json('success') === true;
    }
}
