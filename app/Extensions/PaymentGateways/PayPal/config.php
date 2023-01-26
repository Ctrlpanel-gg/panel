<?php

namespace App\Extensions\PaymentGateways\PayPal;

function getConfig()
{
    return [
        "name" => "PayPal",
        "description" => "PayPal payment gateway",
        "RoutesIgnoreCsrf" => [],
    ];
}
