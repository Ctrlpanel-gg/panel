<?php

namespace App\Extensions\PaymentGateways\MercadoPago;

use App\Classes\PaymentExtension;
use App\Enums\PaymentStatus;
use App\Models\Payment;
use App\Models\ShopProduct;
use App\Models\User;
use App\Traits\HandlesGatewayPayments;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use App\Extensions\PaymentGateways\MercadoPago\MercadoPagoSettings;
use Illuminate\Http\RedirectResponse;

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

    static function Success(Request $request): RedirectResponse
    {
        $payment = Payment::findOrFail($request->input('external_reference'));

        self::ensureAuthenticatedPaymentOwner($payment);

        // In some cases, the webhook is received even before the success route.
        if ($payment->status === PaymentStatus::PAID) {
            return Redirect::route('home')->with('success', 'Your payment has already been processed!');
        }

        self::setPaymentProcessing($payment->id);

        return Redirect::route('home')->with('success', 'Your payment is being processed!');
    }

    static function Webhook(Request $request): JsonResponse
    {
        $settings = new MercadoPagoSettings();
        $xSignature = (string) $request->header('x-signature', '');
        $xRequestId = (string) $request->header('x-request-id', '');

        // Validate webhook signature per Mercado Pago documentation
        if (!self::verifyMercadoPagoWebhookSignature($request, $xSignature, $xRequestId, $settings->webhook_secret)) {
            Log::warning('MercadoPago webhook signature verification failed.', [
                'x-signature_present' => $xSignature !== '',
                'x-request-id_present' => $xRequestId !== '',
            ]);
            return response()->json(['success' => false], 403);
        }

        $topic = (string) $request->input('topic', '');
        $action = (string) $request->input('action', '');
        $notificationId = $request->input('data.id', $request->input('id'));

        /**
         * Mercado Pago sends several requests for information in the webhook,
         *  but most are for other types of API, and that is why it is filtered here.
         */
        if (!empty($action) && !str_contains($action, 'payment')) {
            return response()->json(['success' => true]);
        }

        if (!empty($topic) && !in_array($topic, ['payment', 'merchant_order'], true)) {
            return response()->json(['success' => true]);
        }

        if (empty($notificationId)) {
            Log::warning('MercadoPago webhook missing notification id.', [
                'topic' => $topic,
                'action' => $action,
            ]);
            return response()->json(['success' => false], 400);
        }

        try {
            // Mercado pago test API for webhook request validation.
            if ((string) $notificationId === '123456') {
                return response()->json(['success' => true], 200);
            }

            $url = 'https://api.mercadopago.com/v1/payments/' . $notificationId;
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer ' . $settings->access_token,
            ])->get($url);

            if (!$response->successful()) {
                Log::error('MercadoPago webhook fetch failed.', [
                    'notification_id' => $notificationId,
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
                return response()->json(['success' => false], 502);
            }

            $mercado = $response->json();
            $status = $mercado['status'] ?? null;
            $ctrlPanelPaymentId = $mercado['metadata']['ctrl_panel_payment_id']
                ?? $mercado['metadata']['crtl_panel_payment_id']
                ?? $mercado['external_reference']
                ?? null;
            if (empty($ctrlPanelPaymentId)) {
                return response()->json(['success' => false], 422);
            }

            $payment = Payment::find($ctrlPanelPaymentId);
            if (!$payment || $payment->payment_method !== 'MercadoPago') {
                Log::warning('MercadoPago webhook payment lookup failed.', [
                    'payment_id' => $ctrlPanelPaymentId,
                    'mercadopago_payment_id' => $mercado['id'] ?? null,
                    'payment_method' => $payment?->payment_method,
                ]);

                return response()->json(['success' => true], 200);
            }

            // Validate payment using external_reference and status
            // MercadoPago converts to local currency, so we trust external_reference + status
            $externalRef = (string) ($mercado['external_reference'] ?? '');
            if ($externalRef !== $payment->id) {
                Log::warning('MercadoPago webhook external_reference mismatch.', [
                    'payment_id' => $payment->id,
                    'mercadopago_payment_id' => $mercado['id'] ?? null,
                    'external_reference' => $externalRef,
                ]);
                return response()->json(['success' => true], 200);
            }

            if ($status === 'approved') {
                self::completePayment($payment->id, (string) ($mercado['id'] ?? null));
            } elseif (in_array($status, ['cancelled', 'canceled', 'rejected', 'refunded', 'charged_back'], true)) {
                Log::warning('MercadoPago webhook canceled or failed payment.', [
                    'payment_id' => $payment->id,
                    'mercadopago_payment_id' => $mercado['id'] ?? null,
                    'status' => $status,
                ]);
                self::setPaymentCanceled($payment->id, (string) ($mercado['id'] ?? null));
            } else {
                self::setPaymentProcessing($payment->id, (string) ($mercado['id'] ?? null));
            }
        } catch (Exception $ex) {
            Log::error('MercadoPago Webhook(IPN) Payment failed.', [
                'error' => $ex->getMessage(),
                'topic' => $topic,
                'action' => $action,
                'notification_id' => $notificationId,
            ]);
            return response()->json(['success' => false], 500);
        }

        return response()->json(['success' => true], 200);
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
    protected static function verifyMercadoPagoWebhookSignature(Request $request, string $xSignature, string $xRequestId, ?string $secret): bool
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
