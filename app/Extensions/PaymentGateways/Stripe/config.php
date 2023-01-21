<?php

namespace App\Extensions\PaymentGateways\Stripe;

function getConfig()
{
    return [
        "name" => "Stripe",
        "description" => "Stripe payment gateway",
        "RoutesIgnoreCsrf" => [
            "payment/StripeWebhooks",
        ],
    ];
}
