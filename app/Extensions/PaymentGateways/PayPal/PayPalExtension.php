<?php

namespace App\Extensions\PaymentGateways\PayPal;

use App\Classes\PaymentExtension;
use App\Models\Payment;
use App\Models\ShopProduct;
use App\Traits\HandlesGatewayPayments;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use PayPalCheckoutSdk\Core\PayPalHttpClient;
use PayPalCheckoutSdk\Core\ProductionEnvironment;
use PayPalCheckoutSdk\Core\SandboxEnvironment;
use PayPalCheckoutSdk\Orders\OrdersCaptureRequest;
use PayPalCheckoutSdk\Orders\OrdersCreateRequest;
use PayPalCheckoutSdk\Orders\OrdersGetRequest;
use PayPalHttp\HttpException;


/**
 * Summary of PayPalExtension
 */
class PayPalExtension extends PaymentExtension
{
    use HandlesGatewayPayments;

    public static function getConfig(): array
    {
        return [
            "name" => "PayPal",
            "RoutesIgnoreCsrf" => [
                "payment/PayPalWebhook",
            ],
        ];
    }

    public static function getPermissions(): array
    {
        return [
            'View Paypal Settings' => 'settings.paypal.read',
            'Manage Paypal Settings' => 'settings.paypal.write',
        ];
    }
    /**
     * Currencies supported by PayPal for payments and account balances.
     *
     * @return array<int, string>
     */
    public static function getSupportedCurrencies(): array
    {
        return [
            'AUD', 'BRL', 'CAD', 'CHF', 'CNY', 'CZK', 'DKK', 'EUR', 'GBP', 'HKD',
            'HUF', 'ILS', 'JPY', 'MXN', 'MYR', 'NOK', 'NZD', 'PHP', 'PLN', 'SEK',
            'SGD', 'THB', 'TWD', 'USD',
        ];
    }

    public static function getRedirectUrl(Payment $payment, ShopProduct $shopProduct, int $totalPrice): string
    {
        $currencyHelper = self::currencyHelper();
        $totalPriceFormatted = $currencyHelper->formatForForm($totalPrice, 2);

        $currencyCode = strtoupper($shopProduct->currency_code);

        // Itemize the purchase unit into product, tax and fee line items.
        // item_total must equal the sum of all items; amount.value must equal item_total.
        $productUnit = max(0, $totalPrice - (int) $payment->fee - (int) $payment->tax_value);

        $items = [];
        $items[] = [
            'name' => $shopProduct->display,
            'unit_amount' => [
                'currency_code' => $currencyCode,
                'value' => $currencyHelper->formatForForm($productUnit, 2),
            ],
            'quantity' => '1',
        ];

        if ((int) $payment->tax_value > 0) {
            $items[] = [
                'name' => __('Tax'),
                'unit_amount' => [
                    'currency_code' => $currencyCode,
                    'value' => $currencyHelper->formatForForm((int) $payment->tax_value, 2),
                ],
                'quantity' => '1',
            ];
        }

        if ((int) $payment->fee > 0) {
            $items[] = [
                'name' => __('Payment fee'),
                'unit_amount' => [
                    'currency_code' => $currencyCode,
                    'value' => $currencyHelper->formatForForm((int) $payment->fee, 2),
                ],
                'quantity' => '1',
            ];
        }

        $request = new OrdersCreateRequest();
        $request->prefer('return=representation');
        $request->body = [
            "intent" => "CAPTURE",
            "purchase_units" => [
                [
                    "reference_id" => (string) $payment->id,
                    'custom_id' => (string) $payment->id,
                    "description" => $shopProduct->display,
                    "items" => $items,
                    "amount" => [
                        "value" => $totalPriceFormatted,
                        'currency_code' => $currencyCode,
                        'breakdown' => [
                            'item_total' => [
                                'currency_code' => $currencyCode,
                                'value' => $totalPriceFormatted,
                            ],
                        ],
                    ]
                ]
            ],
            "application_context" => [
                "cancel_url" => route('payment.Cancel'),
                "return_url" => route('payment.PayPalSuccess', ['payment' => $payment->id]),
                'brand_name' =>  config('app.name', 'CtrlPanel.GG'),
                'shipping_preference'  => 'NO_SHIPPING'
            ]
        ];



        try {
            // Call API with your client and get a response for your call
            $response = self::getPayPalClient()->execute($request);

            // check for any errors in the response
            if ($response->statusCode != 201) {
                throw new \Exception($response->statusCode);
            }

            $approvalLink = null;
            foreach (($response->result->links ?? []) as $link) {
                if (($link->rel ?? null) === 'approve') {
                    $approvalLink = $link->href ?? null;
                    break;
                }
            }

            if (empty($approvalLink)) {
                throw new \Exception('No redirect link found');
            }

            return $approvalLink;
        } catch (HttpException $ex) {
            Log::error('PayPal Payment: ' . $ex->getMessage());

            throw new \Exception('PayPal Payment: ' . $ex->getMessage());
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

        try {
            $order = self::getPayPalOrder($payment->payment_id);

            if (strtoupper((string) ($order->status ?? '')) === 'APPROVED') {
                self::capturePayPalOrder($payment->payment_id);
                $order = self::getPayPalOrder($payment->payment_id);
            }

            if (strtoupper((string) ($order->status ?? '')) === 'COMPLETED') {
                if (self::isValidPayPalOrderAmount($payment, $order)) {
                    self::completePayment($payment->id, $payment->payment_id);
                }
            } elseif (in_array(strtoupper((string) ($order->status ?? '')), ['VOIDED', 'EXPIRED'], true)) {
                self::setPaymentCanceled($payment->id, $payment->payment_id);
            }
        } catch (Exception $e) {
            Log::error('PayPal recheck failed', [
                'payment_id' => $payment->id,
                'gateway_id' => $payment->payment_id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    public static function handleOrderApprovedWebhook(array $event): void
    {
        $orderId = (string) data_get($event, 'resource.id', '');
        if ($orderId === '') {
            Log::warning('PayPal webhook order approved missing resource id.', [
                'event_id' => $event['id'] ?? null,
            ]);
            return;
        }

        $payment = self::findPaymentByOrderId($orderId);
        if ($payment) {
            self::setPaymentProcessing($payment->id, $orderId);
        } else {
            Log::warning('PayPal webhook order approved could not find payment for order id.', [
                'order_id' => $orderId,
            ]);
        }

        // Capture order after approval. Final crediting is handled in capture-completed webhook event.
        self::capturePayPalOrder($orderId);
    }

    public static function handleCaptureCompletedWebhook(array $event): void
    {
        $resource = (array) data_get($event, 'resource', []);
        $orderId = (string) data_get($resource, 'supplementary_data.related_ids.order_id', '');

        $payment = null;
        if ($orderId !== '') {
            $payment = self::findPaymentByOrderId($orderId);
        }

        if (!$payment) {
            $paymentId = (string) data_get($resource, 'custom_id', '');
            if ($paymentId !== '') {
                $payment = Payment::whereKey($paymentId)->where('payment_method', 'PayPal')->first();
            }
        }

        if (!$payment) {
            Log::warning('PayPal webhook capture completed could not locate payment.', [
                'event_id' => $event['id'] ?? null,
                'order_id' => $orderId,
                'custom_id' => data_get($resource, 'custom_id'),
            ]);
            return;
        }

        $gatewayReference = $orderId !== ''
            ? $orderId
            : ((string) ($payment->payment_id ?: data_get($resource, 'id', '')));

        if (!self::isValidPayPalCaptureAmount($payment, $resource)) {
            Log::warning('PayPal webhook capture amount/currency mismatch.', [
                'payment_id' => $payment->id,
                'gateway_reference' => $gatewayReference,
                'capture_id' => data_get($resource, 'id'),
                'amount' => data_get($resource, 'amount', []),
            ]);

            self::setPaymentCanceled($payment->id, $gatewayReference !== '' ? $gatewayReference : null);
            return;
        }

        self::completePayment($payment->id, $gatewayReference !== '' ? $gatewayReference : null);
    }

    public static function handleCapturePendingWebhook(array $event): void
    {
        $orderId = (string) data_get($event, 'resource.supplementary_data.related_ids.order_id', '');

        $payment = null;
        if ($orderId !== '') {
            $payment = self::findPaymentByOrderId($orderId);
        }

        if (!$payment) {
            $paymentId = (string) data_get($event, 'resource.custom_id', '');
            if ($paymentId !== '') {
                $payment = Payment::whereKey($paymentId)->where('payment_method', 'PayPal')->first();
            }
        }

        if (!$payment) {
            Log::warning('PayPal webhook capture pending could not locate payment.', [
                'event_id' => $event['id'] ?? null,
                'order_id' => $orderId,
                'custom_id' => data_get($event, 'resource.custom_id'),
            ]);
            return;
        }

        $gatewayReference = $orderId !== ''
            ? $orderId
            : ((string) ($payment->payment_id ?: data_get($event, 'resource.id', '')));

        self::setPaymentProcessing($payment->id, $gatewayReference !== '' ? $gatewayReference : null);
    }

    public static function handleCaptureCanceledWebhook(array $event): void
    {
        $resource = (array) data_get($event, 'resource', []);
        $orderId = (string) data_get($resource, 'supplementary_data.related_ids.order_id', '');

        $payment = null;
        if ($orderId !== '') {
            $payment = self::findPaymentByOrderId($orderId);
        }

        if (!$payment) {
            $paymentId = (string) data_get($resource, 'custom_id', '');
            if ($paymentId !== '') {
                $payment = Payment::whereKey($paymentId)->where('payment_method', 'PayPal')->first();
            }
        }

        if (!$payment) {
            Log::warning('PayPal webhook capture canceled could not locate payment.', [
                'event_id' => $event['id'] ?? null,
                'order_id' => $orderId,
                'custom_id' => data_get($resource, 'custom_id'),
            ]);
            return;
        }

        $gatewayReference = $orderId !== ''
            ? $orderId
            : ((string) ($payment->payment_id ?: data_get($resource, 'id', '')));

        self::setPaymentCanceled($payment->id, $gatewayReference !== '' ? $gatewayReference : null);
    }

    public static function verifyWebhookSignature(Request $request, array $event): bool
    {
        try {
            $webhookId = self::getPaypalWebhookId();
            if (empty($webhookId)) {
                Log::error('PayPal webhook rejected because webhook ID setting is missing.', [
                    'event_type' => $event['event_type'] ?? null,
                ]);
                return false;
            }

            $transmissionId = (string) $request->header('paypal-transmission-id', '');
            $transmissionTime = (string) $request->header('paypal-transmission-time', '');
            $certUrl = (string) $request->header('paypal-cert-url', '');
            $authAlgo = (string) $request->header('paypal-auth-algo', '');
            $transmissionSig = (string) $request->header('paypal-transmission-sig', '');

            if (
                $transmissionId === '' ||
                $transmissionTime === '' ||
                $certUrl === '' ||
                $authAlgo === '' ||
                $transmissionSig === ''
            ) {
                Log::warning('PayPal webhook rejected due to missing verification headers.', [
                    'event_type' => $event['event_type'] ?? null,
                    'transmission_id' => $transmissionId,
                    'transmission_time' => $transmissionTime,
                ]);
                return false;
            }

            $response = Http::withToken(self::getPayPalAccessToken())
                ->post(self::getPayPalApiBaseUrl() . '/v1/notifications/verify-webhook-signature', [
                    'transmission_id' => $transmissionId,
                    'transmission_time' => $transmissionTime,
                    'cert_url' => $certUrl,
                    'auth_algo' => $authAlgo,
                    'transmission_sig' => $transmissionSig,
                    'webhook_id' => $webhookId,
                    'webhook_event' => $event,
                ]);

            if (!$response->successful()) {
                Log::warning('PayPal webhook signature verification request failed.', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                    'event_type' => $event['event_type'] ?? null,
                ]);
                return false;
            }

            $verificationStatus = strtoupper((string) $response->json('verification_status', ''));

            return $verificationStatus === 'SUCCESS';
        } catch (Exception $exception) {
            Log::error('PayPal webhook verification failed.', [
                'error' => $exception->getMessage(),
                'event_type' => $event['event_type'] ?? null,
            ]);

            return false;
        }
    }

    public static function findPaymentByOrderId(string $orderId): ?Payment
    {
        $order = self::getPayPalOrder($orderId);
        $paymentId = self::extractPaymentIdFromOrder($order);
        if (empty($paymentId)) {
            return null;
        }

        $payment = Payment::find($paymentId);

        if (!$payment || $payment->payment_method !== 'PayPal') {
            return null;
        }

        return $payment;
    }

    public static function extractPaymentIdFromOrder(object $order): ?string
    {
        $orderData = json_decode(json_encode($order), true);
        $paymentId = data_get($orderData, 'purchase_units.0.custom_id');

        return is_string($paymentId) && $paymentId !== '' ? $paymentId : null;
    }

    public static function isValidPayPalOrderAmount(Payment $payment, object $order): bool
    {
        $orderData = json_decode(json_encode($order), true);
        $amount = data_get($orderData, 'purchase_units.0.amount.value');
        $currency = data_get($orderData, 'purchase_units.0.amount.currency_code');

        return self::matchesExpectedAmountAndCurrency($payment, $amount, $currency);
    }

    public static function isValidPayPalCaptureAmount(Payment $payment, array $captureResource): bool
    {
        $amount = data_get($captureResource, 'amount.value');
        $currency = data_get($captureResource, 'amount.currency_code');

        return self::matchesExpectedAmountAndCurrency($payment, $amount, $currency);
    }

    public static function matchesExpectedAmountAndCurrency(Payment $payment, mixed $amount, mixed $currency): bool
    {
        if (!is_numeric($amount) || !is_string($currency)) {
            return false;
        }

        $expectedAmount = (float) self::currencyHelper()->formatForForm($payment->total_price, 2);
        if (abs((float) $amount - $expectedAmount) > 0.0001) {
            return false;
        }

        return strtoupper($currency) === strtoupper($payment->currency_code);
    }

    public static function capturePayPalOrder(string $orderId): void
    {
        $request = new OrdersCaptureRequest($orderId);
        $request->prefer('return=representation');

        try {
            self::getPayPalClient()->execute($request);
        } catch (HttpException $exception) {
            // Approved orders can be captured by another retry/webhook delivery.
            if (in_array((int) $exception->statusCode, [409, 422], true)) {
                return;
            }

            throw $exception;
        }
    }

    public static function getPayPalOrder(string $orderId): object
    {
        $request = new OrdersGetRequest($orderId);

        $response = self::getPayPalClient()->execute($request);
        if (!in_array((int) $response->statusCode, [200, 201], true)) {
            throw new Exception('Unable to retrieve PayPal order details.');
        }

        return $response->result;
    }

    public static function getPayPalApiBaseUrl(): string
    {
        return config('app.env') == 'local'
            ? 'https://api-m.sandbox.paypal.com'
            : 'https://api-m.paypal.com';
    }

    public static function getPayPalAccessToken(): string
    {
        $cacheKey = 'paypal_access_token_' . md5(self::getPayPalApiBaseUrl() . '|' . self::getPaypalClientId());
        $cachedToken = Cache::get($cacheKey);
        if (is_string($cachedToken) && $cachedToken !== '') {
            return $cachedToken;
        }

        $response = Http::asForm()
            ->withBasicAuth(self::getPaypalClientId(), self::getPaypalClientSecret())
            ->post(self::getPayPalApiBaseUrl() . '/v1/oauth2/token', [
                'grant_type' => 'client_credentials',
            ]);

        if (!$response->successful()) {
            throw new Exception('Unable to authenticate PayPal webhook verification call.');
        }

        $accessToken = (string) $response->json('access_token', '');
        if ($accessToken === '') {
            throw new Exception('PayPal webhook verification access token missing.');
        }

        $expiresIn = (int) $response->json('expires_in', 300);
        Cache::put($cacheKey, $accessToken, max(60, $expiresIn - 60));

        return $accessToken;
    }

    static function getPayPalClient(): PayPalHttpClient
    {

        $environment = config('app.env') == 'local'
            ? new SandboxEnvironment(self::getPaypalClientId(), self::getPaypalClientSecret())
            : new ProductionEnvironment(self::getPaypalClientId(), self::getPaypalClientSecret());
        return new PayPalHttpClient($environment);
    }

    static function getPaypalWebhookId(): ?string
    {
        $settings = new PayPalSettings();
        $webhookId = config('app.env') == 'local'
            ? ($settings->sandbox_webhook_id ?? $settings->webhook_id ?? null)
            : ($settings->webhook_id ?? $settings->sandbox_webhook_id ?? null);

        return is_string($webhookId) && $webhookId !== '' ? $webhookId : null;
    }

    /**
     * @return string
     */
    static function getPaypalClientId(): string
    {
        $settings = new PayPalSettings();
        return config('app.env') == 'local' ?  $settings->sandbox_client_id : $settings->client_id;
    }
    /**
     * @return string
     */
    static function getPaypalClientSecret(): string
    {
        $settings = new PayPalSettings();
        return config('app.env') == 'local' ? $settings->sandbox_client_secret : $settings->client_secret;
    }
}
