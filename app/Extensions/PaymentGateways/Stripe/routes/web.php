<?php

use App\Extensions\PaymentGateways\Stripe\Http\Controllers\StripeController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'auth'])->group(function () {
    Route::get('payment/StripeSuccess', [StripeController::class, 'success'])->name('payment.StripeSuccess');
});

// Stripe WebhookRoute -> validation in Route Handler
Route::post('payment/StripeWebhooks', [StripeController::class, 'webhook'])->name('payment.StripeWebhooks');
