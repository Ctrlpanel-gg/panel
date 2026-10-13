<?php

namespace App\Extensions\PaymentGateways\MercadoPago;

use App\Classes\PaymentExtension;
use App\Models\Payment;
use App\Models\ShopProduct;
use App\Models\User;
use App\Traits\HandlesGatewayPayments;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use App\Extensions\PaymentGateways\MercadoPago\MercadoPagoSettings;

/**
 * Summary of MercadoPagoExtension
 */
class MercadoPagoExtension extends PaymentExtension
{
    use HandlesGatewayPayments;

    public static function getConfig(): array
    {
        return [
            "name" => "MercadoPago",
            "RoutesIgnoreCsrf" => [
                "payment/MercadoPagoWebhook"
            ],
        ];
    }

    public static function getPermissions(): array
    {
        return [
            'View Mercado Pago Settings' => 'settings.mercadopago.read',
            'Manage Mercado Pago Settings' => 'settings.mercadopago.write',
        ];
    }
    /**
     * Currencies supported by Mercado Pago, matching the countries it operates in.
     *
     * @return array<int, string>
     */
    public static function getSupportedCurrencies(): array
    {
        return [
            'ARS', 'BRL', 'CLP', 'COP', 'MXN', 'PEN', 'UYU',
        ];
    }

    public static function getRedirectUrl(Payment $payment, ShopProduct $shopProduct, int $totalPrice): string
    {
        /**
         * For Mercado Pago to work correctly,
         * it is necessary to use SSL and the app.url must start with "https://",
         * this is necessary so that the webhook receives the information and not an error.
         */
        if (!str_contains(config('app.url'), 'https://')) {
            throw new Exception(__('It is not possible to purchase via MercadoPago: APP_URL does not have HTTPS, required by Mercado Pago.'));
        }

        $totalPriceFormatted = (float) self::currencyHelper()->formatForForm($totalPrice, 2);

        $user = Auth::user();
        $user = User::findOrFail($user->id);
        $url = 'https://api.mercadopago.com/checkout/preferences';
        $settings = new MercadoPagoSettings();
        try {
            $response =  Http::withHeaders([
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer ' . $settings->access_token,
            ])->post($url, [
                'back_urls' => [
                    'success' => route('payment.MercadoPagoSuccess'),
                    'failure' => route('payment.Cancel'),
                    'pending' => route('payment.MercadoPagoSuccess'),
                ],
                'auto_return' => 'approved',
                'notification_url' => route('payment.MercadoPagoWebhook') . '?source_news=webhooks',
                'payer' => [
                    'email' => $user->email,
                ],
                'items' => [
                    [
                        'title' => "Order #{$payment->id} - " . $shopProduct->name,
                        'quantity' => 1,
                        'unit_price' => $totalPriceFormatted,
                        'currency_id' => strtoupper($shopProduct->currency_code),
                    ],
                ],
                'external_reference' => $payment->id,
                'metadata' => [
                    'credit_amount' => $shopProduct->quantity,
                    'user_id' => $user->id,
                    'ctrl_panel_payment_id' => $payment->id,
                    'crtl_panel_payment_id' => $payment->id,
                ],
            ]);

            if ($response->successful()) {
                return $response->json()['init_point'];
            } else {
                Log::error('MercadoPago Payment: ' . $response->body());
                throw new Exception('Payment failed');
            }
        } catch (Exception $ex) {
            Log::error('MercadoPago Payment: ' . $ex->getMessage());
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

        $settings = new MercadoPagoSettings();
        $url = 'https://api.mercadopago.com/v1/payments/' . $payment->payment_id;

        try {
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer ' . $settings->access_token,
            ])->get($url);

            if (!$response->successful()) {
                return;
            }

            $mercado = $response->json();
            $status = $mercado['status'] ?? null;

            if ($status === 'approved') {
                self::completePayment($payment->id, $payment->payment_id);
            } elseif (in_array($status, ['canceled', 'rejected', 'refunded', 'charged_back'], true)) {
                self::setPaymentCanceled($payment->id, $payment->payment_id);
            } else {
                self::setPaymentProcessing($payment->id, $payment->payment_id);
            }
        } catch (Exception $e) {
            Log::error('MercadoPago recheck failed', [
                'payment_id' => $payment->id,
                'gateway_id' => $payment->payment_id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Verify MercadoPago webhook signature using HMAC-SHA256
     * Per documentation: https://developers.mercadopago.com/en/docs/your-integrations/notifications/webhooks
     *
     * @param Request $request The incoming webhook request
     * @param string $xSignature The x-signature header value (format: ts=...,v1=...)
     * @param string $xRequestId The x-request-id header value
     * @param string $secret The webhook secret from settings
     * @return bool True if signature is valid, false otherwise
     */
    public static function verifyMercadoPagoWebhookSignature(Request $request, string $xSignature, string $xRequestId, ?string $secret): bool
    {
        if (empty($secret) || empty($xSignature) || empty($xRequestId)) {
            Log::warning('MercadoPago webhook signature verification missing required headers/secret.', [
                'secret_present' => !empty($secret),
                'x-signature_present' => !empty($xSignature),
                'x-request-id_present' => !empty($xRequestId),
            ]);
            return false;
        }

        try {
            // Parse x-signature header: format is "ts=...,v1=..."
            $signatureParts = explode(',', $xSignature);
            $ts = null;
            $receivedHash = null;

            foreach ($signatureParts as $part) {
                $keyValue = explode('=', trim($part), 2);
                if (count($keyValue) === 2) {
                    $key = trim($keyValue[0]);
                    $value = trim($keyValue[1]);
                    if ($key === 'ts') {
                        $ts = $value;
                    } elseif ($key === 'v1') {
                        $receivedHash = $value;
                    }
                }
            }

            if (empty($ts) || empty($receivedHash)) {
                Log::warning('MercadoPago webhook signature invalid format.', [
                    'ts_found' => !empty($ts),
                    'v1_found' => !empty($receivedHash),
                ]);
                return false;
            }

            // Extract data.id from query params first, per Mercado Pago docs
            $dataId = (string) ($request->query('data.id') ?? $request->input('data.id') ?? $request->input('id') ?? '');
            if (empty($dataId)) {
                Log::warning('MercadoPago webhook signature verification missing data.id.');
                return false;
            }

            $timestamp = null;
            if (is_numeric($ts)) {
                $timestamp = (int) $ts;
                if ($timestamp > 9999999999) {
                    $timestamp = (int) floor($timestamp / 1000);
                }
            }

            if ($timestamp === null) {
                Log::warning('MercadoPago webhook signature invalid timestamp.', [
                    'ts' => $ts,
                ]);
                return false;
            }

            if (abs(time() - $timestamp) > 300) {
                Log::warning('MercadoPago webhook signature timestamp outside allowed tolerance.', [
                    'ts' => $timestamp,
                    'now' => time(),
                ]);
                return false;
            }

            // Build manifest per documentation: id:[data.id_url];request-id:[x-request-id_header];ts:[ts_header];
            $manifest = "id:{$dataId};request-id:{$xRequestId};ts:{$ts};";

            // Calculate HMAC-SHA256
            $calculatedHash = hash_hmac('sha256', $manifest, $secret);

            // Use hash_equals for timing-safe comparison
            return hash_equals($calculatedHash, $receivedHash);
        } catch (Exception $ex) {
            Log::error('MercadoPago webhook signature verification exception... ', [
                'error' => $ex->getMessage(),
            ]);
            return false;
        }
    }
}
