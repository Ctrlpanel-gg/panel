<?php

use App\Support\RecaptchaV2;

if (! function_exists('htmlScriptTagJsApi')) {
    function htmlScriptTagJsApi()
    {
        return RecaptchaV2::scriptTag();
    }
}

if (! function_exists('htmlFormSnippet')) {
    function htmlFormSnippet()
    {
        return RecaptchaV2::widget();
    }
}
