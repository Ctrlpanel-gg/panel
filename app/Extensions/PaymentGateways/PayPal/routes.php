<?php

use Illuminate\Support\Facades\Route;
use App\Extensions\PaymentGateways\PayPal\PayPalExtension;

Route::middleware(['web'])->group(function () {
    Route::get(
        'payment/PayPalSuccess',
        function () {
            return PayPalExtension::PaypalSuccess(request());
        }
    )->name('payment.PayPalSuccess');
});
