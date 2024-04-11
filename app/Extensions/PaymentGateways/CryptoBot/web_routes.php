<?php

use Illuminate\Support\Facades\Route;
use App\Extensions\PaymentGateways\CryptoBot\CryptoBotExtension;

Route::middleware(['web', 'auth'])->group(function () {
    Route::get(
        'payment/CryptoBotSuccess',
        function () {
            CryptoBotExtension::success(request());
        }
    )->name('payment.CryptoBotSuccess');
});


Route::post('payment/CryptoBotWebhook', function () {
    CryptoBotExtension::webhook(request());
})->name('payment.CryptoBotWebhook');
