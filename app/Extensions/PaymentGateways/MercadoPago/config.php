<?php

namespace App\Extensions\PaymentGateways\MercadoPago;

function getConfig()
{
    return [
        "name" => "MercadoPago",
        "description" => "Mercado Pago payment gateway",
        "RoutesIgnoreCsrf" => [
            "payment/MercadoPagoIPN",
        ],
        "enabled" => (config('SETTINGS::PAYMENTS:MPAGO:ACCESS_TOKEN') && env("APP_ENV") === "local"),
    ];
}
