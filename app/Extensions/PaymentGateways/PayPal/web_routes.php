<?php

use Illuminate\Support\Facades\Route;

include_once(__DIR__ . '/index.php');

Route::middleware(['web', 'auth'])->group(function () {
    Route::get('payment/PaypalPay/{shopProduct}', function () {
        PaypalPay(request());
    })->name('payment.PaypalPay');

    Route::get(
        'payment/PayPalSuccess',
        function () {
            PaypalSuccess(request());
        }
    )->name('payment.PayPalSuccess');
});
