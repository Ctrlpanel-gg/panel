<?php

namespace App\Extensions\PaymentGateways\Mollie;

use App\Classes\PaymentExtension;
use App\Models\Payment;
use App\Models\ShopProduct;
use App\Traits\HandlesGatewayPayments;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

/**
 * Summary of PayPalExtension
 */
class MollieExtension extends PaymentExtension
{
    use HandlesGatewayPayments;

    public static function getConfig(): array
    {
        return [
            "name" => "Mollie",
            "RoutesIgnoreCsrf" => [
                "payment/MollieWebhook"
            ],
        ];
    }

    public static function getPermissions(): array
    {
        return [
            'View Mollie Settings' => 'settings.mollie.read',
            'Manage Mollie Settings' => 'settings.mollie.write',
        ];
    }
    /**
     * Currencies Mollie accepts for card payments, PayPal and other payment methods.
     *
     * @return array<int, string>
     */
    public static function getSupportedCurrencies(): array
    {
        return [
            'AED', 'AUD', 'BRL', 'CAD', 'CHF', 'CZK', 'DKK', 'EUR', 'GBP', 'HKD',
            'HUF', 'ILS', 'ISK', 'JPY', 'MXN', 'MYR', 'NOK', 'NZD', 'PHP', 'PLN',
            'RON', 'RUB', 'SEK', 'SGD', 'THB', 'TWD', 'USD', 'ZAR',
        ];
    }

    public static function getRedirectUrl(Payment $payment, ShopProduct $shopProduct, int $totalPrice): string
    {
        // Mollie expects a decimal value string like "10.00".
        $totalPriceFormatted = self::currencyHelper()->formatForForm($totalPrice, 2);

        $url = 'https://api.mollie.com/v2/payments';
        $settings = new MollieSettings();
        try {
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer ' . $settings->api_key,
            ])->post($url, [
                'amount' => [
                    'currency' => strtoupper($shopProduct->currency_code),
                    'value' => $totalPriceFormatted,
                ],
                'description' => "Order #{$payment->id} - " . $shopProduct->name,
                'redirectUrl' => route('payment.MollieSuccess', ['payment_id' => $payment->id]),
                'cancelUrl' => route('payment.Cancel'),
                'webhookUrl' => route('payment.MollieWebhook', ['token' => $settings->webhook_secret]),
                'metadata' => [
                    'payment_id' => $payment->id,
                ],
            ]);

            if ($response->status() != 201) {
                Log::error('Mollie Payment: ' . $response->body());
                throw new Exception('Payment failed');
            }

            return $response->json()['_links']['checkout']['href'];
        } catch (Exception $ex) {
            Log::error('Mollie Payment: ' . $ex->getMessage());
            throw new Exception('Payment failed');
        }
    }

    public static function supportsRecheck(): bool
    {
        return true;
    }

    // Recheck the payment status
    public static function recheckPayment(Payment $payment): void
    {
        if (empty($payment->payment_id)) {
            return;
        }

        $settings = new MollieSettings();
        $url = 'https://api.mollie.com/v2/payments/' . $payment->payment_id;

        try {
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer ' . $settings->api_key,
            ])->get($url);

            if (!$response->successful()) {
                return;
            }

            $status = $response->json('status');

            if ($status === 'paid') {
                if (self::matchesMollieAmountAndCurrency(
                    $payment,
                    (string) $response->json('amount.value', ''),
                    (string) $response->json('amount.currency', '')
                )) {
                    self::completePayment($payment->id, $payment->payment_id);
                }
            } elseif (in_array($status, ['failed', 'expired', 'canceled'], true)) {
                self::setPaymentCanceled($payment->id, $payment->payment_id);
            } elseif (in_array($status, ['authorized', 'pending', 'open'], true)) {
                self::setPaymentProcessing($payment->id, $payment->payment_id);
            }
        } catch (Exception $e) {
            Log::error('Mollie recheck failed', [
                'payment_id' => $payment->id,
                'gateway_id' => $payment->payment_id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    public static function matchesMollieAmountAndCurrency(Payment $payment, string $amount, string $currency): bool
    {
        if (!is_numeric($amount) || $currency === '') {
            return false;
        }

        $expectedAmount = (float) self::currencyHelper()->formatForForm($payment->total_price, 2);
        if (abs((float) $amount - $expectedAmount) > 0.0001) {
            return false;
        }

        return strtoupper($currency) === strtoupper($payment->currency_code);
    }
}
