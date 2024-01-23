<?php

namespace App\Extensions\PaymentGateways\MercadoPago;

function getConfig()
{
    return [
        "name" => "mpago",
        "description" => "Mercado Pago payment gateway",
        "RoutesIgnoreCsrf" => [
            "payment/MercadoPagoIPN",
        ],
        "enabled" => config('SETTINGS::PAYMENTS:MPAGO:ACCESS_TOKEN'),
    ];
}
