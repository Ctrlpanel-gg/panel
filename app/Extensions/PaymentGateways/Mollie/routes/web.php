<?php

use App\Extensions\PaymentGateways\Mollie\Http\Controllers\MollieController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'auth'])->group(function () {
    Route::get('payment/MollieSuccess', [MollieController::class, 'success'])->name('payment.MollieSuccess');
});

Route::post('payment/MollieWebhook', [MollieController::class, 'webhook'])->name('payment.MollieWebhook');
