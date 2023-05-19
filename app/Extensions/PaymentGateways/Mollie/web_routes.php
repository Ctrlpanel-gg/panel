<?php

use Illuminate\Support\Facades\Route;
use App\Extensions\PaymentGateways\Mollie\MollieExtension;

Route::middleware(['web', 'auth'])->group(function () {
    Route::get('payment/MolliePay/{shopProduct}', function (MollieExtension $mollieExtension) {
        $mollieExtension->pay(request());
    })->name('payment.MolliePay');

    Route::get(
        'payment/MollieSuccess',
        function () {
            MollieExtension::success(request());
        }
    )->name('payment.MollieSuccess');
});


Route::post('payment/MollieWebhook', function () {
    MollieExtension::webhook(request());
})->name('payment.MollieWebhook');
