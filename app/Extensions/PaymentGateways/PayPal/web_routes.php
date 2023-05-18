<?php

use Illuminate\Support\Facades\Route;
use App\Extensions\PaymentGateways\PayPal\PayPalExtension;

Route::middleware(['web', 'auth'])->group(function () {
    Route::get('payment/PayPalPay/{shopProduct}', function (PayPalExtension $payPalExtension) {
        $payPalExtension->PaypalPay(request());
    })->name('payment.PayPalPay');

    Route::get(
        'payment/PayPalSuccess',
        function () {
            PayPalExtension::PaypalSuccess(request());
        }
    )->name('payment.PayPalSuccess');
});
