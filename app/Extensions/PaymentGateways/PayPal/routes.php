<?php

use Illuminate\Support\Facades\Route;

Route::get('payment/PayPalPay/{shopProduct}', [PaymentController::class, 'PaypalPay'])->name('payment.PayPalPay');
Route::get('payment/PayPalSuccess', [PaymentController::class, 'PaypalSuccess'])->name('payment.PaypalSuccess');
