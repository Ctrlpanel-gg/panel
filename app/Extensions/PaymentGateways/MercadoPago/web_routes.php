<?php

use Illuminate\Support\Facades\Route;
use App\Extensions\PaymentGateways\MercadoPago\MercadoPagoExtension;

Route::middleware(['web', 'auth'])->group(function () {
    Route::get(
        'payment/MercadoPagoSuccess',
        function () {
            MercadoPagoExtension::Success(request());
        }
    )->name('payment.MercadoPagoSuccess');
});


Route::post('payment/MercadoPagoWebhook', function () {
    MercadoPagoExtension::Webhook(request());
})->name('payment.MercadoPagoWebhook');
