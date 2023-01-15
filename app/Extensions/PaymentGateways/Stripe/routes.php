<?php

use Illuminate\Support\Facades\Route;

include_once(__DIR__ . '/index.php');

Route::get('payment/StripePay/{shopProduct}', function () {
    StripePay(request());
})->name('payment.StripePay');

Route::get(
    'payment/StripeSuccess',
    function () {
        StripeSuccess(request());
    }
)->name('payment.StripeSuccess');


// Stripe WebhookRoute -> validation in Route Handler
Route::post('payment/StripeWebhooks', function () {
    StripeWebhooks(request());
})->name('payment.StripeWebhooks');
