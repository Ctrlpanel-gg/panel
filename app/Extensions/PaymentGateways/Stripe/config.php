<?php

namespace App\Extensions\PaymentGateways\Stripe;

function getConfig()
{
    return [
        "name" => "Stripe",
        "RoutesIgnoreCsrf" => [
            "payment/StripeWebhooks",
        ],
    ];
}
