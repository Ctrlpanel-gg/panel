<?php

namespace App\Classes;

use App\Models\Payment;
use App\Models\ShopProduct;

abstract class PaymentExtension extends AbstractExtension
{
    /**
     * Returns the redirect url of the payment gateway to redirect the user to
     */
    abstract public static function getRedirectUrl(Payment $payment, ShopProduct $shopProduct, string $totalPriceString): string;
}
