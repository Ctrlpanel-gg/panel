<?php

use App\Extensions\PaymentGateways\Mollie\MollieExtension;
use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'auth'])->group(function () {
    Route::get('payment/MolliePay/{shopProduct}', function () {
        MollieExtension::pay(request());
    })->name('payment.MolliePay');

    Route::get(
        'payment/PayPalSuccess',
        function () {
            MollieExtension::success(request());
        }
    )->name('payment.MollieSuccess');
});
