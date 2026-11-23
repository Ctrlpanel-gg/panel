<?php

namespace App\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static bool verify(?string $token, ?string $ip = null)
 * @method static string renderScripts()
 * @method static string renderWidget()
 * @method static bool isEnabled()
 *
 * @see \App\Services\CaptchaService
 */
class Captcha extends Facade
{
    protected static function getFacadeAccessor()
    {
        return \App\Services\CaptchaService::class;
    }
}
