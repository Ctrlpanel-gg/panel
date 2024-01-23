<?php

use Illuminate\Support\Facades\Route;

include_once(__DIR__ . '/index.php');

Route::middleware(['web', 'auth'])->group(function () {
    Route::get('payment/MercadoPagoPay/{shopProduct}', function () {
        MercadoPagoPay(request());
    })->name('payment.MercadoPagoPay');

    Route::get(
        'payment/MercadoPagoChecker',
        function () {
            MercadoPagoChecker(request());
        }
    )->name('payment.MercadoPagoChecker');
});
Route::post('payment/MercadoPagoIPN', function () {
    MercadoPagoIPN(request());
})->name('payment.MercadoPagoIPN');