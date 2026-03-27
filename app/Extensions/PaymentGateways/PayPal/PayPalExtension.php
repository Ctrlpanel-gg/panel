<?php

namespace App\Extensions\PaymentGateways\PayPal;

use App\Events\PaymentEvent;
use App\Events\UserUpdateCreditsEvent;
use App\Classes\PaymentExtension;
use App\Enums\PaymentStatus;
use App\Models\Payment;
use App\Models\ShopProduct;
use App\Models\User;
use App\Notifications\ConfirmPaymentNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\URL;


/**
 * Summary of PayPalExtension
 */
class PayPalExtension extends PaymentExtension
{
    public static function getConfig(): array
    {
        return [
            "name" => "PayPal",
            "RoutesIgnoreCsrf" => [],
        ];
    }

    public static function getRedirectUrl(Payment $payment, ShopProduct $shopProduct, int $totalPrice): string
    {
        $totalPrice = number_format($totalPrice / 1000, 2, '.', '');
        $payload = [
            "intent" => "CAPTURE",
            "purchase_units" => [
                [
                    "reference_id" => (string) \Illuminate\Support\Str::ulid(),
                    "custom_id" => $payment->id,
                    "description" => $shopProduct->display,
                    "amount" => [
                        "value" => $totalPrice,
                        'currency_code' => strtoupper($shopProduct->currency_code),
                        'breakdown' => [
                            'item_total' => [
                                'currency_code' => strtoupper($shopProduct->currency_code),
                                'value' => $totalPrice,
                            ],

                            /* Removed due to errors in the coupon discount calculation. Its not used in other paymentgateways aswell and basically nice to have but unnessecary

                            'tax_total' => [
                                'currency_code' => strtoupper($shopProduct->currency_code),
                                'value' => round($shopProduct->getTaxValue(), 2),
                            ]
                            */
                        ]
                    ]
                ]
            ],
            'payment_source' => [
                'paypal' => [
                    'experience_context' => [
                        'cancel_url' => route('payment.Cancel'),
                        'return_url' => URL::temporarySignedRoute('payment.PayPalSuccess', now()->addDay(), ['payment' => $payment->id]),
                        'brand_name' => config('app.name', 'CtrlPanel.GG'),
                        'shipping_preference' => 'NO_SHIPPING',
                    ],
                ],
            ],
        ];

        try {
            $response = self::paypalRequest('post', '/v2/checkout/orders', $payload, [
                'Prefer' => 'return=representation',
            ]);
            if ($response->status() !== 201) {
                throw new \Exception('Unexpected PayPal status: ' . $response->status());
            }

            $body = $response->json();
            $approveUrl = collect($body['links'] ?? [])->first(
                fn (array $link): bool => in_array($link['rel'] ?? null, ['approve', 'payer-action'], true)
            )['href'] ?? null;

            if (! is_string($approveUrl) || $approveUrl === '') {
                throw new \Exception('No redirect link found');
            }

            return $approveUrl;
        } catch (\Throwable $ex) {
            Log::error('PayPal Payment: ' . $ex->getMessage(), [
                'payment_id' => $payment->id,
            ]);

            throw new \Exception('PayPal Payment: ' . $ex->getMessage());
        }
    }


    public static function PaypalSuccess(Request $laravelRequest): \Illuminate\Http\RedirectResponse
    {
        if (! $laravelRequest->hasValidSignatureWhileIgnoring(['token', 'PayerID'])) {
            abort(403);
        }

        $payment = Payment::findOrFail($laravelRequest->payment);
        if ($payment->payment_method !== 'PayPal') {
            abort(403);
        }

        if ($payment->status === PaymentStatus::PAID) {
            return Redirect::route(self::getCallbackRedirectRoute())->with('success', 'Payment successful');
        }

        $shopProduct = ShopProduct::findOrFail($payment->shop_item_product_id);
        $paymentOwner = User::findOrFail($payment->user_id);

        try {
            $orderId = (string) $laravelRequest->input('token', '');
            if ($orderId === '') {
                return Redirect::route(self::getCallbackRedirectRoute())->with(
                    'error',
                    __('We could not confirm your PayPal payment because the callback was incomplete.')
                );
            }

            $response = self::paypalRequest(
                'post',
                '/v2/checkout/orders/' . $orderId . '/capture',
                [],
                ['Prefer' => 'return=representation']
            );

            if ($response->status() == 201 || $response->status() == 200) {
                $result = $response->json();
                $customId = data_get($result, 'purchase_units.0.custom_id');
                if ($customId !== $payment->id) {
                    abort(403);
                }

                self::assertCapturedAmountMatches($payment, $result);

                $updated = Payment::whereKey($payment->id)
                    ->where('status', '!=', PaymentStatus::PAID->value)
                    ->update([
                        'status' => PaymentStatus::PAID->value,
                        'payment_id' => data_get($result, 'id'),
                    ]);

                if ($updated === 0) {
                    return Redirect::route(self::getCallbackRedirectRoute())->with('success', 'Payment successful');
                }

                $payment = $payment->fresh();
                $paymentOwner->notify(new ConfirmPaymentNotification($payment));
                event(new UserUpdateCreditsEvent($paymentOwner));
                event(new PaymentEvent($paymentOwner, $payment, $shopProduct));

                // redirect to the payment success page with success message
                return Redirect::route(self::getCallbackRedirectRoute())->with('success', 'Payment successful');
            }

            Log::warning('PayPal capture returned an unexpected status.', [
                'payment_id' => $payment->id,
                'status' => $response->status(),
                'body' => $response->json() ?: $response->body(),
            ]);
        } catch (\Throwable $ex) {
            Log::error('PayPal capture failed', [
                'payment_id' => $payment->id,
                'message' => $ex->getMessage(),
            ]);
        }

        return Redirect::route(self::getCallbackRedirectRoute())->with(
            'error',
            __('We could not confirm your PayPal payment. If you were charged, please contact support.')
        );
    }

    private static function assertCapturedAmountMatches(Payment $payment, array|object $result): void
    {
        $purchaseUnit = data_get($result, 'purchase_units.0');
        $capturedAmount = data_get($purchaseUnit, 'payments.captures.0.amount')
            ?? data_get($purchaseUnit, 'amount');

        if ($capturedAmount === null) {
            throw new \Exception('PayPal capture amount missing.');
        }

        $expectedAmount = number_format($payment->total_price / 1000, 2, '.', '');
        $actualAmount = number_format((float) data_get($capturedAmount, 'value', 0), 2, '.', '');
        $actualCurrency = strtoupper((string) data_get($capturedAmount, 'currency_code', ''));

        if ($actualCurrency !== strtoupper($payment->currency_code) || $actualAmount !== $expectedAmount) {
            Log::critical('PayPal payment amount mismatch detected', [
                'payment_id' => $payment->id,
                'expected_amount' => $expectedAmount,
                'received_amount' => $actualAmount,
                'expected_currency' => strtoupper($payment->currency_code),
                'received_currency' => $actualCurrency,
            ]);

            throw new \Exception('PayPal payment amount verification failed.');
        }
    }

    private static function paypalRequest(string $method, string $path, array $payload = [], array $headers = [])
    {
        $request = Http::acceptJson()
            ->withToken(self::getPayPalAccessToken())
            ->withHeaders(array_merge([
                'Content-Type' => 'application/json',
            ], $headers))
            ->timeout(30);

        return match (strtolower($method)) {
            'get' => $request->get(self::getPayPalApiBaseUrl() . $path, $payload),
            'post' => $request->post(self::getPayPalApiBaseUrl() . $path, $payload),
            default => throw new \InvalidArgumentException('Unsupported PayPal HTTP method.'),
        };
    }

    private static function getPayPalAccessToken(): string
    {
        $response = Http::asForm()
            ->acceptJson()
            ->withBasicAuth(self::getPaypalClientId(), self::getPaypalClientSecret())
            ->timeout(30)
            ->post(self::getPayPalApiBaseUrl() . '/v1/oauth2/token', [
                'grant_type' => 'client_credentials',
            ]);

        if (! $response->successful()) {
            Log::error('PayPal auth failed', [
                'status' => $response->status(),
                'body' => $response->json() ?: $response->body(),
            ]);

            throw new \Exception('Failed to authenticate with PayPal.');
        }

        $token = $response->json('access_token');
        if (! is_string($token) || $token === '') {
            throw new \Exception('PayPal access token missing.');
        }

        return $token;
    }

    private static function getPayPalApiBaseUrl(): string
    {
        return self::getPayPalMode() === 'sandbox'
            ? 'https://api-m.sandbox.paypal.com'
            : 'https://api-m.paypal.com';
    }

    /**
     * @return string
     */
    static function getPaypalClientId(): string
    {
        $settings = new PayPalSettings();
        return self::getPayPalMode() === 'sandbox' ?  $settings->sandbox_client_id : $settings->client_id;
    }
    /**
     * @return string
     */
    static function getPaypalClientSecret(): string
    {
        $settings = new PayPalSettings();
        return self::getPayPalMode() === 'sandbox' ? $settings->sandbox_client_secret : $settings->client_secret;
    }

    private static function getPayPalMode(): string
    {
        $settings = new PayPalSettings();

        return $settings->mode === 'sandbox' ? 'sandbox' : 'live';
    }

    private static function getCallbackRedirectRoute(): string
    {
        return Auth::check() ? 'home' : 'login';
    }
}
