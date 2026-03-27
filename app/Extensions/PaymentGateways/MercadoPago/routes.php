<?php

use Illuminate\Support\Facades\Route;
use App\Extensions\PaymentGateways\MercadoPago\MercadoPagoExtension;

Route::middleware(['web'])->group(function () {
    Route::get(
        'payment/MercadoPagoSuccess',
        function () {
            return MercadoPagoExtension::Success(request());
        }
    )->name('payment.MercadoPagoSuccess');

    Route::get(
        'payment/MercadoPagoPending',
        function () {
            return MercadoPagoExtension::Pending(request());
        }
    )->name('payment.MercadoPagoPending');
});


Route::post('payment/MercadoPagoWebhook', function () {
    return MercadoPagoExtension::Webhook(request());
})->name('payment.MercadoPagoWebhook');
