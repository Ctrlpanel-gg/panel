<?php

use Illuminate\Support\Facades\Route;
use App\Extensions\PaymentGateways\MercadoPago\MercadoPagoExtension;

Route::middleware(['web', 'auth'])->group(function () {
    Route::get(
        'payment/MercadoPagoChecker',
        function () {
            MercadoPagoExtension::Checker(request());
        }
    )->name('payment.MercadoPagoChecker');
});


Route::post('payment/MercadoPagoWebhook', function () {
    MercadoPagoExtension::Webhook(request());
})->name('payment.MercadoPagoWebhook');
