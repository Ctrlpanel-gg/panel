<?php

namespace App\Rules;

use App\Facades\Captcha;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Request;

class CaptchaRule implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        // Find the first non-empty token from the request
        $token = collect([
            $value,
            Request::input('captcha'),
            Request::input('cf-turnstile-response'),
            Request::input('g-recaptcha-response'),
        ])->filter()->first();

        if (!Captcha::verify($token, Request::ip())) {
            $fail(__('The CAPTCHA verification failed. Please try again.'));
        }
    }
}
