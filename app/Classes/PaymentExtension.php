<?php

namespace App\Classes;

use App\Helpers\CurrencyHelper;
use App\Models\Payment;
use App\Models\ShopProduct;

abstract class PaymentExtension extends AbstractExtension
{
    protected static function currencyHelper(): CurrencyHelper
    {
        return resolve(CurrencyHelper::class);
    }

    /**
     * Returns the redirect url of the payment gateway to redirect the user to
     */
    abstract public static function getRedirectUrl(Payment $payment, ShopProduct $shopProduct, int $totalPrice): string;

    /**
     * Returns true if the payment gateway supports rechecking the payment status
     */
    public static function supportsRecheck(): bool
    {
        return false;
    }

    /**
     * Recheck the payment status with the payment gateway
     */
    public static function recheckPayment(Payment $payment): void
    {
        throw new \Exception('Recheck not implemented');
    }
}
