<?php

namespace App\Classes;

use App\Helpers\CurrencyHelper;
use App\Models\Payment;
use App\Models\ShopProduct;
use Spatie\LaravelSettings\Settings;

abstract class PaymentExtension extends AbstractExtension
{
    protected static function currencyHelper(): CurrencyHelper
    {
        return resolve(CurrencyHelper::class);
    }

    /**
     * Resolves the settings class that belongs to the calling extension.
     * The settings class lives in the same namespace and shares the extension
     * name, but uses the "Settings" suffix instead of "Extension".
     */
    protected static function getSettings(): Settings
    {
        $settingsClass = preg_replace('/Extension$/', 'Settings', static::class);

        return new $settingsClass();
    }

    /**
     * The gateway fee type configured by the admin.
     * One of: none | percent | fixed
     */
    protected static function getFeeType(): string
    {
        return (string) (self::feeValues()['fee_type'] ?? 'none');
    }

    /**
     * The current fee settings (type, percent, fixed, min, max) as managed
     * by the core for every payment gateway.
     * @return array<string, mixed>
     */
    protected static function feeValues(): array
    {
        return GatewayFeeSettings::values(static::getSettings());
    }

    /**
     * Computes the payment processing fee for the given total (in thousandths)
     * and returns it in thousandths.
     *
     * The fee is either a percentage of the payment amount (optionally clamped
     * to a minimum/maximum commission) or a fixed amount.
     */
    public static function getPaymentFee(int $totalPrice, string $currencyCode): int
    {
        $feeValues = self::feeValues();
        $type = self::getFeeType();

        if ($type === 'fixed') {
            return (int) ($feeValues['fee_fixed'] ?? 0);
        }

        if ($type === 'percent') {
            $percent = (float) ($feeValues['fee_percent'] ?? 0);
            $fee = $totalPrice * $percent / 100;

            $min = (int) ($feeValues['fee_min'] ?? 0);
            $max = (int) ($feeValues['fee_max'] ?? 0);

            if ($min > 0 && $fee < $min) {
                $fee = $min;
            }

            if ($max > 0 && $fee > $max) {
                $fee = $max;
            }

            return (int) round($fee);
        }

        return 0;
    }

    /**
     * Returns the fee configuration as an array, intended for use on the
     * client side. Fixed/min/max amounts are expressed in thousandths,
     * the percent value as a number.
     *
     * @return array<string, mixed>
     */
    public static function getFeeConfig(): array
    {
        $feeValues = self::feeValues();

        return [
            'type' => (string) ($feeValues['fee_type'] ?? 'none'),
            'percent' => (float) ($feeValues['fee_percent'] ?? 0),
            'fixed' => (int) ($feeValues['fee_fixed'] ?? 0),
            'min' => (int) ($feeValues['fee_min'] ?? 0),
            'max' => (int) ($feeValues['fee_max'] ?? 0),
        ];
    }

    /**
     * Returns a human readable description of the fee for the given currency,
     * or null when no fee is configured.
     */
    public static function getFeeDescription(string $currencyCode): ?string
    {
        $feeValues = self::feeValues();
        $type = self::getFeeType();

        if ($type === 'fixed') {
            $amount = (int) ($feeValues['fee_fixed'] ?? 0);

            return __('Flat fee of :amount', [
                'amount' => self::currencyHelper()->formatToCurrency($amount, $currencyCode),
            ]);
        }

        if ($type === 'percent') {
            $percent = (float) ($feeValues['fee_percent'] ?? 0);
            $min = (int) ($feeValues['fee_min'] ?? 0);
            $max = (int) ($feeValues['fee_max'] ?? 0);

            $parts = [$percent . '%'];
            if ($min > 0) {
                $parts[] = __('min :amount', ['amount' => self::currencyHelper()->formatToCurrency($min, $currencyCode)]);
            }
            if ($max > 0) {
                $parts[] = __('max :amount', ['amount' => self::currencyHelper()->formatToCurrency($max, $currencyCode)]);
            }

            return implode(', ', $parts);
        }

        return null;
    }

    /**
     * Returns the redirect url of the payment gateway to redirect the user to
     */
    abstract public static function getRedirectUrl(Payment $payment, ShopProduct $shopProduct, int $totalPrice): string;

    /**
     * Returns the list of ISO 4217 currency codes this gateway accepts for checkout,
     * or null to allow every currency.
     *
     * @return array<int, string>|null
     */
    public static function getSupportedCurrencies(): ?array
    {
        return null;
    }

    /**
     * Returns the minimum order value required by this gateway for the given currency,
     * in the currency's display units, or null if this gateway has no minimum.
     */
    public static function getMinimumPrice(string $currencyCode): ?float
    {
        return null;
    }

    /**
     * Determines whether this gateway can be used for a checkout with the given currency
     * and total price (in the currency's display units).
     *
     * @return array{available: bool, reason: string|null}
     */
    public static function isAvailableForCheckout(string $currencyCode, float $totalPrice): array
    {
        $currency = strtoupper($currencyCode);

        $supportedCurrencies = static::getSupportedCurrencies();
        if (is_array($supportedCurrencies) && !in_array($currency, $supportedCurrencies, true)) {
            return [
                'available' => false,
                'reason' => __('This payment gateway does not support the :currency currency', ['currency' => $currency]),
            ];
        }

        $minimum = static::getMinimumPrice($currency);
        if ($minimum !== null && $totalPrice < $minimum) {
            $formattedMinimum = resolve(CurrencyHelper::class)->formatToCurrency((int) round($minimum * 1000), $currency);

            return [
                'available' => false,
                'reason' => __('This payment gateway requires a minimum order of :amount', ['amount' => $formattedMinimum]),
            ];
        }

        return ['available' => true, 'reason' => null];
    }

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
