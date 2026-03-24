<?php

use Illuminate\Support\Facades\Route;
use App\Extensions\PaymentGateways\Mollie\MollieExtension;

Route::middleware(['web', 'auth'])->group(function () {
    Route::get(
        'payment/MollieSuccess',
        function () {
            return MollieExtension::success(request());
        }
    )->name('payment.MollieSuccess');
});


Route::post('payment/MollieWebhook', function () {
    return MollieExtension::webhook(request());
})->name('payment.MollieWebhook');
