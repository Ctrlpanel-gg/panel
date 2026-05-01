<?php

namespace App\Extensions\PaymentGateways\Stripe;

use App\Classes\PaymentExtension;
use App\Enums\PaymentStatus;
use App\Models\Payment;
use App\Models\ShopProduct;
use App\Traits\HandlesGatewayPayments;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redirect;
use Throwable;
use Stripe\Exception\SignatureVerificationException;
use Stripe\Stripe;
use Stripe\StripeClient;

class StripeExtension extends PaymentExtension
{
    use HandlesGatewayPayments;

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
        $displayTotal = self::currencyHelper()->convertForDisplay($totalPrice);

        // check if the total price is valid for stripe
        if (!self::checkPriceAmount((float) $displayTotal, strtoupper($shopProduct->currency_code), 'stripe')) {
            Log::warning('Stripe getRedirectUrl rejected due to invalid price amount', [
                'payment_id' => $payment->id,
                'currency_code' => $shopProduct->currency_code,
                'display_total' => $displayTotal,
            ]);
            throw new Exception('Invalid price amount');
        }

        $stripeClient = self::getStripeClient();
        $request = $stripeClient->checkout->sessions->create([
            'metadata' => [
                'payment_id' => $payment->id,
            ],
            'line_items' => [
                [
                    'price_data' => [
                        'currency' => $shopProduct->currency_code,
                        'product_data' => [
                            'name' => $shopProduct->display,
                            'description' => $shopProduct->description,
                        ],
                        'unit_amount' => self::convertAmount($totalPrice, $shopProduct->currency_code),
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
            'success_url' => route('payment.StripeSuccess', ['payment' => $payment->id]) . '&session_id={CHECKOUT_SESSION_ID}',
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
        $payment = Payment::findOrFail($request->input('payment'));
        $sessionId = $request->input('session_id');
        self::ensureAuthenticatedPaymentOwner($payment);


        if ($payment->status === PaymentStatus::PAID) {
            return Redirect::route('home')->with('success', 'Your payment has already been processed!');
        }

        if (empty($sessionId)) {
            Log::warning('StripeSuccess missing session id', [
                'payment_id' => $payment->id,
            ]);
            return Redirect::route('home')->with('error', 'Missing Stripe session details.');
        }

        $stripeClient = self::getStripeClient();
        try {
            $paymentSession = $stripeClient->checkout->sessions->retrieve($sessionId);

            $sessionMetadataPaymentId = (string) ($paymentSession->metadata->payment_id ?? '');
            $paymentIntentId = isset($paymentSession->payment_intent)
                ? (string) $paymentSession->payment_intent
                : null;

            $intentMetadataPaymentId = '';
            if (!empty($paymentIntentId)) {
                $paymentIntent = $stripeClient->paymentIntents->retrieve($paymentIntentId);
                $intentMetadataPaymentId = (string) ($paymentIntent->metadata->payment_id ?? '');
            }

            $resolvedPaymentId = $sessionMetadataPaymentId !== ''
                ? $sessionMetadataPaymentId
                : $intentMetadataPaymentId;

            if ($resolvedPaymentId !== $payment->id) {
                Log::error('StripeSuccess payment id mismatch', [
                    'payment_id' => $payment->id,
                    'resolved_payment_id' => $resolvedPaymentId,
                    'session_id' => $sessionId,
                ]);
                throw new Exception('Stripe checkout session does not match payment.');
            }

            if ($paymentSession->status === 'complete') {
                self::setPaymentProcessing($payment->id, $paymentIntentId);

                return Redirect::route('home')->with('success', 'Payment received. We are confirming it now.');
            }

            if ($paymentSession->status === 'expired') {
                Log::warning('StripeSuccess session expired, canceling payment', [
                    'payment_id' => $payment->id,
                    'payment_intent_id' => $paymentIntentId,
                ]);
                self::setPaymentCanceled($payment->id, $paymentIntentId);

                return Redirect::route('home')->with('info', __('Your payment has been canceled!'));
            }

            self::setPaymentProcessing($payment->id, $paymentIntentId);

            return Redirect::route('home')->with('success', 'Payment received. We are confirming it now.');
        } catch (Throwable $e) {
            Log::error('Stripe success handler failed', [
                'payment_id' => $payment->id,
                'session_id' => $sessionId,
                'error' => $e->getMessage(),
                'exception' => get_class($e),
                'code' => $e->getCode(),
            ]);

            return Redirect::route('home')->with('error', 'Oops, something went wrong while confirming your payment.');
        }
    }

    /**
     * @param  Request  $request
     */
    protected static function handleStripePaymentSucceeded(object $paymentIntent): void
    {
        $paymentId = $paymentIntent->metadata->payment_id ?? null;
        if (empty($paymentId)) {
            Log::warning('Stripe webhook payment intent missing payment_id metadata', [
                'payment_intent_id' => $paymentIntent->id ?? null,
            ]);
            return;
        }

        $payment = Payment::find($paymentId);
        if (!$payment || $payment->payment_method !== 'Stripe') {
            Log::warning('Stripe webhook payment lookup failed.', [
                'payment_id' => $paymentId,
                'payment_intent_id' => $paymentIntent->id ?? null,
            ]);
            return;
        }

        if (!self::isValidStripePaymentPayload($payment, $paymentIntent)) {
            Log::warning('Stripe webhook payload validation failed; canceling payment', [
                'payment_id' => $payment->id,
                'payment_intent_id' => $paymentIntent->id ?? null,
                'payment_currency' => $payment->currency_code,
                'webhook_currency' => $paymentIntent->currency ?? null,
                'amount_received' => $paymentIntent->amount_received ?? null,
                'expected_total' => $payment->total_price,
            ]);
            self::setPaymentCanceled($payment->id, $paymentIntent->id ?? null);
            return;
        }

        self::completePayment($payment->id, $paymentIntent->id ?? null);

    }

    protected static function isValidStripePaymentPayload(Payment $payment, object $paymentIntent): bool
    {
        $currency = strtoupper((string) ($paymentIntent->currency ?? ''));
        $expectedCurrency = strtoupper($payment->currency_code);
        $amountInSmallestUnit = (int) ($paymentIntent->amount_received ?? $paymentIntent->amount ?? 0);
        $amountInDatabaseUnits = self::convertGatewayAmountToDatabaseUnits($amountInSmallestUnit, $currency);

        $isValid = $currency !== ''
            && $currency === $expectedCurrency
            && $amountInDatabaseUnits === (int) $payment->total_price;

        return $isValid;
    }

    protected static function convertGatewayAmountToDatabaseUnits(int $gatewayAmount, string $currency): int
    {
        $currency = strtoupper($currency);

        if (in_array($currency, self::ZERO_DECIMAL_CURRENCIES, true)) {
            $result = self::currencyHelper()->prepareForDatabase((float) $gatewayAmount);
            return $result;
        }

        if (in_array($currency, self::THREE_DECIMAL_CURRENCIES, true)) {
            $result = self::currencyHelper()->prepareForDatabase($gatewayAmount / 1000);
            return $result;
        }

        $result = self::currencyHelper()->prepareForDatabase($gatewayAmount / 100);
        return $result;
    }

    /**
     * @param  Request  $request
     */
    public static function StripeWebhooks(Request $request): JsonResponse
    {
        Stripe::setApiKey(self::getStripeSecret());

        $endpointSecrets = self::getStripeEndpointSecrets();
        if (empty($endpointSecrets)) {
            Log::error('Stripe webhook secret is not configured.');
            return response()->json(['success' => false], 500);
        }

        $payload = $request->getContent();
        $sig_header = (string) $request->header('Stripe-Signature', '');
        if ($sig_header === '') {
            Log::warning('Stripe webhook signature header is missing.');
            return response()->json(['success' => false], 400);
        }

        $event = null;
        $signatureErrors = [];

        try {
            foreach ($endpointSecrets as $secretName => $endpointSecret) {
                try {
                    $event = \Stripe\Webhook::constructEvent(
                        $payload,
                        $sig_header,
                        $endpointSecret
                    );

                    break;
                } catch (SignatureVerificationException $e) {
                    $signatureErrors[$secretName] = $e->getMessage();
                }
            }
        } catch (\UnexpectedValueException $e) {
            Log::warning('Stripe webhook payload could not be parsed', [
                'error' => $e->getMessage(),
                'exception' => get_class($e),
            ]);
            return response()->json(['success' => false], 400);
        }

        if ($event === null) {
            Log::warning('Stripe webhook signature verification failed', [
                'errors' => $signatureErrors,
                'secrets_checked' => array_keys($endpointSecrets),
            ]);
            return response()->json(['success' => false], 400);
        }

        // Handle the event
        switch ($event->type) {
            case 'payment_intent.processing':
                $paymentIntent = $event->data->object;
                $paymentId = $paymentIntent->metadata->payment_id ?? null;
                if (!empty($paymentId) && Payment::whereKey($paymentId)->where('payment_method', 'Stripe')->exists()) {
                    self::setPaymentProcessing($paymentId, $paymentIntent->id ?? null);
                }
                break;
            case 'payment_intent.succeeded':
                $paymentIntent = $event->data->object; // contains a \Stripe\PaymentIntent
                self::handleStripePaymentSucceeded($paymentIntent);
                break;
            case 'payment_intent.payment_failed':
                $paymentIntent = $event->data->object;
                $paymentId = $paymentIntent->metadata->payment_id ?? null;
                if (!empty($paymentId) && Payment::whereKey($paymentId)->where('payment_method', 'Stripe')->exists()) {
                    Log::warning('Stripe webhook setting payment canceled', [
                        'payment_id' => $paymentId,
                        'payment_intent_id' => $paymentIntent->id ?? null,
                    ]);
                    self::setPaymentCanceled($paymentId, $paymentIntent->id ?? null);
                }
                break;
            default:
                break;
        }

        return response()->json(['success' => true], 200);
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

        return config('app.env') == 'local'
            ? $settings->test_secret_key
            : $settings->secret_key;
    }

    /**
     * @return string|null
     */
    public static function getStripeEndpointSecret()
    {
        $endpointSecrets = self::getStripeEndpointSecrets();
        $firstSecret = reset($endpointSecrets);

        return $firstSecret === false ? null : $firstSecret;
    }

    /**
     * @return array<string, string>
     */
    public static function getStripeEndpointSecrets(): array
    {
        $settings = new StripeSettings();
        $isLocal = config('app.env') == 'local';

        // Prefer explicit webhook signing secret fields, then legacy endpoint secret fields.
        $orderedSecrets = $isLocal
            ? [
                'test_webhook_signing_secret' => $settings->test_webhook_signing_secret,
                'test_publishable_key' => $settings->test_publishable_key,
                'webhook_signing_secret' => $settings->webhook_signing_secret,
                'publishable_key' => $settings->publishable_key,
            ]
            : [
                'webhook_signing_secret' => $settings->webhook_signing_secret,
                'publishable_key' => $settings->publishable_key,
                'test_webhook_signing_secret' => $settings->test_webhook_signing_secret,
                'test_publishable_key' => $settings->test_publishable_key,
            ];

        $secrets = [];
        $seenValues = [];
        foreach ($orderedSecrets as $name => $secret) {
            if (!is_string($secret)) {
                continue;
            }

            $normalized = trim($secret);
            if ($normalized === '' || isset($seenValues[$normalized])) {
                continue;
            }

            $seenValues[$normalized] = true;
            $secrets[$name] = $normalized;
        }

        return $secrets;
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

        if (!isset($minimums[$currencyCode], $minimums[$currencyCode][$payment_method])) {
            return false;
        }

        return $amount >= $minimums[$currencyCode][$payment_method];
    }

    protected static function convertAmount(float $amount, string $currency): int
    {
        $displayAmount = self::currencyHelper()->convertForDisplay($amount);

        if (in_array(strtoupper($currency), self::ZERO_DECIMAL_CURRENCIES, true)) {
            return (int) round($displayAmount);
        }

        if (in_array(strtoupper($currency), self::THREE_DECIMAL_CURRENCIES, true)) {
            return (int) round($displayAmount * 1000);
        }

        return (int) round($displayAmount * 100);
    }
}
