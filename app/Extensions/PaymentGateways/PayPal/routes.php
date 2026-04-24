<?php

use Illuminate\Support\Facades\Route;
use App\Extensions\PaymentGateways\PayPal\PayPalExtension;

Route::middleware(['web', 'auth'])->group(function () {
    Route::get(
        'payment/PayPalSuccess',
        function () {
            return PayPalExtension::PaypalSuccess(request());
        }
    )->name('payment.PayPalSuccess');
});

Route::post('payment/PayPalWebhook', function () {
    return PayPalExtension::webhook(request());
})->name('payment.PayPalWebhook');
