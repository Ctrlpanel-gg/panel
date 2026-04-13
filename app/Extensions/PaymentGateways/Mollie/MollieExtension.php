<?php

namespace App\Extensions\PaymentGateways\Mollie;

use App\Classes\PaymentExtension;
use App\Enums\PaymentStatus;
use App\Models\Payment;
use App\Models\ShopProduct;
use App\Traits\HandlesGatewayPayments;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
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

    static function success(Request $request): RedirectResponse
    {
        $payment = Payment::findOrFail($request->input('payment_id'));
        self::ensureAuthenticatedPaymentOwner($payment);

        if ($payment->status === PaymentStatus::PAID) {
            return Redirect::route('home')->with('success', 'Your payment has already been processed!');
        }

        self::setPaymentProcessing($payment->id);

        return Redirect::route('home')->with('success', 'Your payment is being processed');
    }

    static function webhook(Request $request): JsonResponse
    {
        $settings = new MollieSettings();
        $incomingWebhookToken = (string) $request->query('token', '');
        if (empty($settings->webhook_secret) || !hash_equals((string) $settings->webhook_secret, $incomingWebhookToken)) {
            Log::warning('Mollie webhook rejected due to invalid token.');
            return response()->json(['success' => false], 403);
        }

        $molliePaymentId = (string) $request->input('id', '');
        if (empty($molliePaymentId)) {
            return response()->json(['success' => false], 400);
        }

        $url = 'https://api.mollie.com/v2/payments/' . $molliePaymentId;

        try {
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer ' . $settings->api_key,
            ])->get($url);

            if (!$response->successful()) {
                Log::error('Mollie Payment Webhook: ' . $response->body());
                return response()->json(['success' => false], 502);
            }

            $status = $response->json('status');
            $paymentId = $response->json('metadata.payment_id');
            if (empty($paymentId)) {
                return response()->json(['success' => false], 422);
            }

            $payment = Payment::find($paymentId);
            if (!$payment || $payment->payment_method !== 'Mollie') {
                Log::warning('Mollie webhook payment lookup failed.', [
                    'payment_id' => $paymentId,
                    'mollie_payment_id' => $response->json('id'),
                ]);

                return response()->json(['success' => true], 200);
            }

            if (!self::matchesMollieAmountAndCurrency(
                $payment,
                (string) $response->json('amount.value', ''),
                (string) $response->json('amount.currency', '')
            )) {
                Log::warning('Mollie webhook amount/currency mismatch.', [
                    'payment_id' => $payment->id,
                    'mollie_payment_id' => $response->json('id'),
                ]);

                self::setPaymentCanceled($payment->id, (string) $response->json('id'));
                return response()->json(['success' => true], 200);
            }

            if ($status === 'paid') {
                self::completePayment($payment->id, (string) $response->json('id'));
            } elseif (in_array($status, ['failed', 'expired', 'canceled'], true)) {
                self::setPaymentCanceled($payment->id, (string) $response->json('id'));
            } elseif (in_array($status, ['authorized', 'pending', 'open'], true)) {
                self::setPaymentProcessing($payment->id, (string) $response->json('id'));
            }
        } catch (Exception $ex) {
            Log::error('Mollie Payment Webhook: ' . $ex->getMessage());
            return response()->json(['success' => false], 500);
        }

        return response()->json(['success' => true], 200);
    }

    protected static function matchesMollieAmountAndCurrency(Payment $payment, string $amount, string $currency): bool
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
