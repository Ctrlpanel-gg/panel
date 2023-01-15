<?php

use App\Events\PaymentEvent;
use App\Events\UserUpdateCreditsEvent;
use App\Models\PartnerDiscount;
use App\Models\Payment;
use App\Models\Product;
use App\Models\ShopProduct;
use App\Models\User;
use App\Notifications\ConfirmPaymentNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use Stripe\Exception\SignatureVerificationException;
use Stripe\Stripe;
use Stripe\StripeClient;



/**
 * @param  Request  $request
 * @param  ShopProduct  $shopProduct
 */
function StripePay(Request $request)
{
    $user = Auth::user();
    $shopProduct = ShopProduct::findOrFail($request->shopProduct);

    // check if the price is valid for stripe
    if (!checkPriceAmount($shopProduct->getTotalPrice(), strtoupper($shopProduct->currency_code), 'stripe')) {
        Redirect::route('home')->with('error', __('The product you chose can\'t be purchased with this payment method. The total amount is too small. Please buy a bigger amount or try a different payment method.'))->send();
        return;
    }


    // create payment
    $payment = Payment::create([
        'user_id' => $user->id,
        'payment_id' => null,
        'payment_method' => 'stripe',
        'type' => $shopProduct->type,
        'status' => 'open',
        'amount' => $shopProduct->quantity,
        'price' => $shopProduct->price - ($shopProduct->price * PartnerDiscount::getDiscount() / 100),
        'tax_value' => $shopProduct->getTaxValue(),
        'total_price' => $shopProduct->getTotalPrice(),
        'tax_percent' => $shopProduct->getTaxPercent(),
        'currency_code' => $shopProduct->currency_code,
        'shop_item_product_id' => $shopProduct->id,
    ]);

    $stripeClient = getStripeClient();
    $request = $stripeClient->checkout->sessions->create([
        'line_items' => [
            [
                'price_data' => [
                    'currency' => $shopProduct->currency_code,
                    'product_data' => [
                        'name' => $shopProduct->display . (PartnerDiscount::getDiscount() ? (' (' . __('Discount') . ' ' . PartnerDiscount::getDiscount() . '%)') : ''),
                        'description' => $shopProduct->description,
                    ],
                    'unit_amount_decimal' => round($shopProduct->getPriceAfterDiscount() * 100, 2),
                ],
                'quantity' => 1,
            ],
            [
                'price_data' => [
                    'currency' => $shopProduct->currency_code,
                    'product_data' => [
                        'name' => __('Tax'),
                        'description' => $shopProduct->getTaxPercent() . '%',
                    ],
                    'unit_amount_decimal' => round($shopProduct->getTaxValue(), 2) * 100,
                ],
                'quantity' => 1,
            ],
        ],

        'mode' => 'payment',
        'payment_method_types' => str_getcsv(config('SETTINGS::PAYMENTS:STRIPE:METHODS')),
        'success_url' => route('payment.StripeSuccess', ['payment' => $payment->id]) . '&session_id={CHECKOUT_SESSION_ID}',
        'cancel_url' => route('payment.Cancel'),
    ]);

    Redirect::to($request->url)->send();
}

/**
 * @param  Request  $request
 */
function StripeSuccess(Request $request)
{
    $user = Auth::user();
    error_log('StripeSuccess');
    $payment = Payment::findOrFail($request->input('payment'));
    $shopProduct = ShopProduct::findOrFail($payment->shop_item_product_id);


    $stripeClient = getStripeClient();
    try {
        //get stripe data
        $paymentSession = $stripeClient->checkout->sessions->retrieve($request->input('session_id'));
        $paymentIntent = $stripeClient->paymentIntents->retrieve($paymentSession->payment_intent);

        //get DB entry of this payment ID if existing
        $paymentDbEntry = Payment::where('payment_id', $paymentSession->payment_intent)->count();

        // check if payment is 100% completed and payment does not exist in db already
        if ($paymentSession->status == 'complete' && $paymentIntent->status == 'succeeded' && $paymentDbEntry == 0) {

            //update payment
            $payment->update([
                'payment_id' => $paymentSession->payment_intent,
                'status' => 'paid',
            ]);

            //payment notification
            $user->notify(new ConfirmPaymentNotification($payment));

            event(new UserUpdateCreditsEvent($user));
            event(new PaymentEvent($user, $payment, $shopProduct));

            //redirect back to home
            Redirect::route('home')->with('success', 'Payment successful')->send();
        } else {
            if ($paymentIntent->status == 'processing') {

                //update payment
                $payment->update([
                    'payment_id' => $paymentSession->payment_intent,
                    'status' => 'processing',
                ]);

                // todo invoice
                event(new PaymentEvent($user, $payment, $shopProduct));

                Redirect::route('home')->with('success', 'Your payment is being processed')->send();
            }

            if ($paymentDbEntry == 0 && $paymentIntent->status != 'processing') {
                $stripeClient->paymentIntents->cancel($paymentIntent->id);

                //redirect back to home
                Redirect::route('home')->with('info', __('Your payment has been canceled!'))->send();
            } else {
                abort(402);
            }
        }
    } catch (Exception $e) {
        if (env('APP_ENV') == 'local') {
            dd($e->getMessage());
        } else {
            abort(422);
        }
    }
}

/**
 * @param  Request  $request
 */
function handleStripePaymentSuccessHook($paymentIntent)
{
    try {
        // Get payment db entry
        $payment = Payment::where('payment_id', $paymentIntent->id)->first();
        $user = User::where('id', $payment->user_id)->first();
        $shopProduct = ShopProduct::findOrFail($payment->shop_item_product_id);

        if ($paymentIntent->status == 'succeeded' && $payment->status == 'processing') {


            //update payment db entry status
            $payment->update(['status' => 'paid']);

            //payment notification
            $user->notify(new ConfirmPaymentNotification($payment));
            event(new UserUpdateCreditsEvent($user));
            event(new PaymentEvent($user, $payment, $shopProduct));
        }
    } catch (Exception $ex) {
        abort(422);
    }
}

/**
 * @param  Request  $request
 */
function StripeWebhooks(Request $request)
{
    Stripe::setApiKey(getStripeSecret());

    try {
        $payload = @file_get_contents('php://input');
        $sig_header = $request->header('Stripe-Signature');
        $event = null;
        $event = \Stripe\Webhook::constructEvent(
            $payload,
            $sig_header,
            getStripeEndpointSecret()
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
            handleStripePaymentSuccessHook($paymentIntent);
            break;
        default:
            echo 'Received unknown event type ' . $event->type;
    }
}

/**
 * @return \Stripe\StripeClient
 */
function getStripeClient()
{
    return new StripeClient(getStripeSecret());
}

/**
 * @return string
 */
function getStripeSecret()
{
    return env('APP_ENV') == 'local'
        ? config('SETTINGS::PAYMENTS:STRIPE:TEST_SECRET')
        : config('SETTINGS::PAYMENTS:STRIPE:SECRET');
}

/**
 * @return string
 */
function getStripeEndpointSecret()
{
    return env('APP_ENV') == 'local'
        ? config('SETTINGS::PAYMENTS:STRIPE:ENDPOINT_TEST_SECRET')
        : config('SETTINGS::PAYMENTS:STRIPE:ENDPOINT_SECRET');
}
/**
 * @param  $amount
 * @param  $currencyCode
 * @param  $payment_method
 * @return bool
 * @description check if the amount is higher than the minimum amount for the stripe gateway
 */
function checkPriceAmount($amount, $currencyCode, $payment_method)
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

function getStripeConfig()
{
    return [
        "name" => "Stripe",
        "description" => "Stripe payment gateway",
        "mode" => [
            "type" => "select",
            "label" => "Mode",
            "value" => config("APP_ENV") == 'local' ? "sandbox" : "live",
            "options" => [
                "sandbox" => "Sandbox",
                "live" => "Live",
            ],
        ],
        "TEST_SECRET" => [
            "type" => "text",
            "label" => "Test Secret Key",
            "value" => config("SETTINGS::PAYMENTS:STRIPE:TEST_SECRET"),
        ],
        "SECRET" => [
            "type" => "text",
            "label" => "Live Secret Key",
            "value" => config("SETTINGS::PAYMENTS:STRIPE:SECRET"),
        ],
        "ENDPOINT_TEST_SECRET" => [
            "type" => "text",
            "label" => "Test Endpoint Secret",
            "value" => config("SETTINGS::PAYMENTS:STRIPE:ENDPOINT_TEST_SECRET"),
        ],
        "ENDPOINT_SECRET" => [
            "type" => "text",
            "label" => "Live Endpoint Secret",
            "value" => config("SETTINGS::PAYMENTS:STRIPE:ENDPOINT_SECRET"),
        ],
    ];
}
