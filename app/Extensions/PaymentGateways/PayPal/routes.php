<?php

use Illuminate\Support\Facades\Route;
include_once(__DIR__ . '/index.php');

Route::get('payment/PayPalPay/{shopProduct}', function () {
    PaypalPay(request());
})->name('payment.PayPalPay');

Route::get('payment/PayPalSuccess',  function () {
    PaypalSuccess(request());
}
)->name('payment.PayPalSuccess');
