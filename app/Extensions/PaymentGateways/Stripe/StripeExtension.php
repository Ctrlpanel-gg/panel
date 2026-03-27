<?php

namespace App\Extensions\PaymentGateways\Stripe;

use App\Classes\PaymentExtension;
use App\Enums\PaymentStatus;
use App\Events\PaymentEvent;
use App\Events\UserUpdateCreditsEvent;
use App\Models\Payment;
use App\Models\ShopProduct;
use App\Models\User;
use App\Traits\Coupon as CouponTrait;
use App\Notifications\ConfirmPaymentNotification;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\URL;
use Stripe\Exception\SignatureVerificationException;
use Stripe\Stripe;
use Stripe\StripeClient;

class StripeExtension extends PaymentExtension
{
    use CouponTrait;

    // https://docs.stripe.com/currencies#zero-decimal
    protected const ZERO_DECIMAL_CURRENCIES = [
        'BIF', 'CLP', 'DJF', 'GNF', 'JPY', 'KMF', 'KRW', 'MGA', 'PYG', 'RWF', 'VND', 'VUV', 'XAF', 'XOF', 'XPF',
    ];

    // https://docs.stripe.com/currencies#three-decimal
    protected const THREE_DECIMAL_CURRENCIES = [
        'BHD', 'JOD', 'KWD', 'OMR', 'TND',
    ];

    public static function getConfig(): array
    {
        return [
            "name" => "Stripe",
            "RoutesIgnoreCsrf" => [
                "payment/StripeWebhooks",
            ],
        ];
    }

    public static function getRedirectUrl(Payment $payment, ShopProduct $shopProduct, int $totalPrice): string
    {
        // check if the total price is valid for stripe
        if (!self::checkPriceAmount(floatval($totalPrice), strtoupper($shopProduct->currency_code), 'stripe')) {
            throw new Exception('Invalid price amount');
        }

        $stripeClient = self::getStripeClient();
        $request = $stripeClient->checkout->sessions->create([
            'line_items' => [
                [
                    'price_data' => [
                        'currency' => $shopProduct->currency_code,
                        'product_data' => [
                            'name' => $shopProduct->display,
                            'description' => $shopProduct->description,
                        ],
                        'unit_amount_decimal' => self::convertAmount($totalPrice, $shopProduct->currency_code),
                    ],
                    'quantity' => 1,
                ],
                /* Removed due to errors in the coupon discount calculation. Its not used in other paymentgateways aswell and basically nice to have but unnessecary
                [
                    'price_data' => [
                        'currency' => $shopProduct->currency_code,
                        'product_data' => [
                            'name' => __('Tax'),
                            'description' => $shopProduct->getTaxPercent() . '%',
                        ],
                        'unit_amount_decimal' => round($shopProduct->getTaxValue(), 2),
                    ],
                    'quantity' => 1,
                ],
                */
            ],

            'mode' => 'payment',
            'success_url' => URL::temporarySignedRoute('payment.StripeSuccess', now()->addDay(), ['payment' => $payment->id]) . '&session_id={CHECKOUT_SESSION_ID}',
            'cancel_url' => route('payment.Cancel'),
            'payment_intent_data' => [
                'metadata' => [
                    'payment_id' => $payment->id,
                ],
            ],
        ]);

        return $request->url;
    }

    /**
     * @param  Request  $request
     */
    public static function StripeSuccess(Request $request): RedirectResponse
    {
        if (! $request->hasValidSignatureWhileIgnoring(['session_id'])) {
            abort(403);
        }

        $payment = Payment::findOrFail($request->input('payment'));
        if ($payment->payment_method !== 'Stripe') {
            abort(403);
        }

        $shopProduct = ShopProduct::findOrFail($payment->shop_item_product_id);
        $paymentOwner = User::findOrFail($payment->user_id);

        $sessionId = (string) $request->input('session_id', '');
        if ($sessionId === '') {
            return Redirect::route(self::getCallbackRedirectRoute())->with(
                'error',
                __('We could not confirm your Stripe payment because the callback was incomplete.')
            );
        }

        $stripeClient = self::getStripeClient();
        try {
            //get stripe data
            $paymentSession = $stripeClient->checkout->sessions->retrieve($sessionId);
            $paymentIntent = $stripeClient->paymentIntents->retrieve($paymentSession->payment_intent);

            self::assertStripePaymentMatches($payment, $paymentIntent);

            if ($paymentIntent->status == 'succeeded') {
                $updated = Payment::whereKey($payment->id)
                    ->where('status', '!=', PaymentStatus::PAID->value)
                    ->update([
                        'payment_id' => $paymentSession->payment_intent,
                        'status' => PaymentStatus::PAID->value,
                    ]);

                if ($updated > 0) {
                    $payment = $payment->fresh();
                    $paymentOwner->notify(new ConfirmPaymentNotification($payment));
                    event(new UserUpdateCreditsEvent($paymentOwner));
                    event(new PaymentEvent($paymentOwner, $payment, $shopProduct));
                }

                return Redirect::route(self::getCallbackRedirectRoute())->with('success', 'Payment successful');
            }

            if ($paymentIntent->status == 'processing') {
                $updated = Payment::whereKey($payment->id)
                    ->where('status', '!=', PaymentStatus::PAID->value)
                    ->update([
                        'payment_id' => $paymentSession->payment_intent,
                        'status' => PaymentStatus::PROCESSING->value,
                    ]);

                if ($updated > 0) {
                    $payment = $payment->fresh();
                    event(new PaymentEvent($paymentOwner, $payment, $shopProduct));

                    return Redirect::route(self::getCallbackRedirectRoute())->with('success', 'Your payment is being processed');
                }

                return Redirect::route(self::getCallbackRedirectRoute())->with('success', 'Payment successful');
            }

            $freshPayment = $payment->fresh();
            if ($freshPayment->status === PaymentStatus::PAID) {
                return Redirect::route(self::getCallbackRedirectRoute())->with('success', 'Payment successful');
            }

            if ($paymentIntent->status != 'processing') {
                $stripeClient->paymentIntents->cancel($paymentIntent->id);

                //redirect back to home
                return Redirect::route(self::getCallbackRedirectRoute())->with('info', __('Your payment has been canceled!'));
            }
        } catch (Exception $e) {
            Log::error('Stripe success callback failed.', [
                'payment_id' => $payment->id,
                'session_id' => $sessionId,
                'message' => $e->getMessage(),
            ]);

            return Redirect::route(self::getCallbackRedirectRoute())->with(
                'error',
                __('We could not confirm your Stripe payment. If you were charged, please contact support.')
            );
        }
    }

    /**
     * @param  Request  $request
     */
    public static function handleStripePaymentSuccessHook($paymentIntent): JsonResponse
    {
        try {
            $payment = Payment::findOrFail($paymentIntent->metadata->payment_id);
            if ($payment->payment_method !== 'Stripe') {
                abort(403);
            }

            $user = User::findOrFail($payment->user_id);
            $shopProduct = ShopProduct::findOrFail($payment->shop_item_product_id);

            self::assertStripePaymentMatches($payment, $paymentIntent);

            if ($paymentIntent->status == 'succeeded') {
                $updated = Payment::whereKey($payment->id)
                    ->where('status', '!=', PaymentStatus::PAID->value)
                    ->update([
                        'payment_id' => $payment->payment_id ?? $paymentIntent->id,
                        'status' => PaymentStatus::PAID->value,
                    ]);

                if ($updated > 0) {
                    $payment = $payment->fresh();

                    //payment notification
                    $user->notify(new ConfirmPaymentNotification($payment));
                    event(new UserUpdateCreditsEvent($user));
                    event(new PaymentEvent($user, $payment, $shopProduct));
                }
            }

            // return 200
            return response()->json(['success' => true], 200);
        } catch (Exception $ex) {
            abort(422);
        }
    }

    /**
     * @param  Request  $request
     */
    public static function StripeWebhooks(Request $request): JsonResponse
    {
        Stripe::setApiKey(self::getStripeSecret());

        try {
            $payload = @file_get_contents('php://input');
            $sig_header = $request->header('Stripe-Signature');
            $event = null;
            $event = \Stripe\Webhook::constructEvent(
                $payload,
                $sig_header,
                self::getStripeEndpointSecret()
            );
        } catch (\UnexpectedValueException $e) {
            // Invalid payload

            abort(400);
        } catch (SignatureVerificationException $e) {
            // Invalid signature

            abort(400);
        }

        // Handle the event
        switch ($event->type) {
            case 'payment_intent.succeeded':
                $paymentIntent = $event->data->object; // contains a \Stripe\PaymentIntent
                return self::handleStripePaymentSuccessHook($paymentIntent);
            default:
                return response()->json(['success' => true]);
        }
    }

    /**
     * @return \Stripe\StripeClient
     */
    public static function getStripeClient()
    {
        return new StripeClient(self::getStripeSecret());
    }

    /**
     * @return string
     */
    public static function getStripeSecret()
    {
        $settings = new StripeSettings();

        return self::getStripeMode() === 'test'
            ? $settings->test_secret_key
            : $settings->secret_key;
    }

    /**
     * @return string
     */
    public static function getStripeEndpointSecret()
    {
        $settings = new StripeSettings();
        return self::getStripeMode() === 'test'
            ? $settings->test_endpoint_secret
            : $settings->endpoint_secret;
    }

    private static function getStripeMode(): string
    {
        $settings = new StripeSettings();

        return $settings->mode === 'test' ? 'test' : 'live';
    }

    private static function getCallbackRedirectRoute(): string
    {
        return Auth::check() ? 'home' : 'login';
    }
    /**
     * @param  $amount
     * @param  $currencyCode
     * @param  $payment_method
     * @return bool
     * @description check if the amount is higher than the minimum amount for the stripe gateway
     */
    public static function checkPriceAmount(float $amount,  string $currencyCode, string $payment_method)
    {
        $minimums = [
            "USD" => [
                "paypal" => 0,
                "stripe" => 0.5
            ],
            "AED" => [
                "paypal" => 0,
                "stripe" => 2
            ],
            "AUD" => [
                "paypal" => 0,
                "stripe" => 0.5
            ],
            "BGN" => [
                "paypal" => 0,
                "stripe" => 1
            ],
            "BRL" => [
                "paypal" => 0,
                "stripe" => 0.5
            ],
            "CAD" => [
                "paypal" => 0,
                "stripe" => 0.5
            ],
            "CHF" => [
                "paypal" => 0,
                "stripe" => 0.5
            ],
            "CZK" => [
                "paypal" => 0,
                "stripe" => 15
            ],
            "DKK" => [
                "paypal" => 0,
                "stripe" => 2.5
            ],
            "EUR" => [
                "paypal" => 0,
                "stripe" => 0.5
            ],
            "GBP" => [
                "paypal" => 0,
                "stripe" => 0.3
            ],
            "HKD" => [
                "paypal" => 0,
                "stripe" => 4
            ],
            "HRK" => [
                "paypal" => 0,
                "stripe" => 0.5
            ],
            "HUF" => [
                "paypal" => 0,
                "stripe" => 175
            ],
            "INR" => [
                "paypal" => 0,
                "stripe" => 0.5
            ],
            "JPY" => [
                "paypal" => 0,
                "stripe" => 0.5
            ],
            "MXN" => [
                "paypal" => 0,
                "stripe" => 10
            ],
            "MYR" => [
                "paypal" => 0,
                "stripe" => 2
            ],
            "NOK" => [
                "paypal" => 0,
                "stripe" => 3
            ],
            "NZD" => [
                "paypal" => 0,
                "stripe" => 0.5
            ],
            "PLN" => [
                "paypal" => 0,
                "stripe" => 2
            ],
            "RON" => [
                "paypal" => 0,
                "stripe" => 2
            ],
            "SEK" => [
                "paypal" => 0,
                "stripe" => 3
            ],
            "SGD" => [
                "paypal" => 0,
                "stripe" => 0.5
            ],
            "THB" => [
                "paypal" => 0,
                "stripe" => 10
            ]
        ];
        return $amount >= $minimums[$currencyCode][$payment_method];
    }

    protected static function convertAmount(float $amount, string $currency): int
    {
        if (in_array($currency, self::ZERO_DECIMAL_CURRENCIES, true)) {
            return $amount / 1000;
        }

        if (in_array($currency, self::THREE_DECIMAL_CURRENCIES, true)) {
            return $amount;
        }

        return $amount / 10;
    }

    private static function assertStripePaymentMatches(Payment $payment, object $paymentIntent): void
    {
        if (($paymentIntent->metadata->payment_id ?? null) !== $payment->id) {
            throw new Exception('Stripe payment metadata mismatch.');
        }

        $actualCurrency = strtoupper((string) ($paymentIntent->currency ?? ''));
        $expectedCurrency = strtoupper($payment->currency_code);
        $expectedAmount = self::convertAmount((float) $payment->total_price, $expectedCurrency);
        $actualAmount = (int) (($paymentIntent->amount_received ?? 0) > 0
            ? $paymentIntent->amount_received
            : ($paymentIntent->amount ?? 0));

        if ($actualCurrency !== $expectedCurrency || $actualAmount !== $expectedAmount) {
            Log::critical('Stripe payment amount mismatch detected', [
                'payment_id' => $payment->id,
                'expected_amount' => $expectedAmount,
                'received_amount' => $actualAmount,
                'expected_currency' => $expectedCurrency,
                'received_currency' => $actualCurrency,
                'user_id' => $payment->user_id,
            ]);

            throw new Exception('Stripe payment amount verification failed.');
        }
    }
}
