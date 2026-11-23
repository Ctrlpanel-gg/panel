<?php

use App\Extensions\PaymentGateways\PayPal\Http\Controllers\PayPalController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'auth'])->group(function () {
    Route::get('payment/PayPalSuccess', [PayPalController::class, 'success'])->name('payment.PayPalSuccess');
});

Route::post('payment/PayPalWebhook', [PayPalController::class, 'webhook'])->name('payment.PayPalWebhook');
