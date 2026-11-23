<?php

use App\Extensions\PaymentGateways\MercadoPago\Http\Controllers\MercadoPagoController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'auth'])->group(function () {
    Route::get('payment/MercadoPagoSuccess', [MercadoPagoController::class, 'success'])->name('payment.MercadoPagoSuccess');
});

Route::post('payment/MercadoPagoWebhook', [MercadoPagoController::class, 'webhook'])->name('payment.MercadoPagoWebhook');
