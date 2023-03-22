<?php

use Illuminate\Support\Facades\Route;
use App\Extensions\PaymentGateways\Stripe\StripeExtension;

Route::middleware(['web', 'auth'])->group(function () {
    Route::get('payment/StripePay/{shopProduct}', function () {
        StripeExtension::StripePay(request());
    })->name('payment.StripePay');

    Route::get(
        'payment/StripeSuccess',
        function () {
            StripeExtension::StripeSuccess(request());
        }
    )->name('payment.StripeSuccess');
});


// Stripe WebhookRoute -> validation in Route Handler
Route::post('payment/StripeWebhooks', function () {
    StripeExtension::StripeWebhooks(request());
})->name('payment.StripeWebhooks');
