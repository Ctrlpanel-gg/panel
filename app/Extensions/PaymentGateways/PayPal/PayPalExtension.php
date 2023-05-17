<?php

namespace App\Extensions\PaymentGateways\PayPal;

use App\Helpers\AbstractExtension;
use App\Events\PaymentEvent;
use App\Events\UserUpdateCreditsEvent;
use App\Extensions\PaymentGateways\PayPal\PayPalSettings;
use App\Models\PartnerDiscount;
use App\Models\Payment;
use App\Models\ShopProduct;
use App\Models\User;
use App\Models\Coupon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Log;
use PayPalCheckoutSdk\Core\PayPalHttpClient;
use PayPalCheckoutSdk\Core\ProductionEnvironment;
use PayPalCheckoutSdk\Core\SandboxEnvironment;
use PayPalCheckoutSdk\Orders\OrdersCaptureRequest;
use PayPalCheckoutSdk\Orders\OrdersCreateRequest;
use PayPalHttp\HttpException;


/**
 * Summary of PayPalExtension
 */
class PayPalExtension extends AbstractExtension
{
    public static function getConfig(): array
    {
        return [
            "name" => "PayPal",
            "RoutesIgnoreCsrf" => [],
        ];
    }

    static function PaypalPay(Request $request): void
    {
        /** @var User $user */
        $user = Auth::user();
        $shopProduct = ShopProduct::findOrFail($request->shopProduct);
        $discount = PartnerDiscount::getDiscount();
        $discountPrice = $request->get('discountPrice');

        dd($discountPrice);

        // Partner Discount.
        $price = $shopProduct->price - ($shopProduct->price * $discount / 100);

        // Coupon Discount.
        // if ($discountPrice) {
        //     $price = $price - ($price * floatval($coupon_percentage) / 100);
        // }

        // create a new payment
        $payment = Payment::create([
            'user_id' => $user->id,
            'payment_id' => null,
            'payment_method' => 'paypal',
            'type' => $shopProduct->type,
            'status' => 'open',
            'amount' => $shopProduct->quantity,
            'price' => $price,
            'tax_value' => $shopProduct->getTaxValue(),
            'tax_percent' => $shopProduct->getTaxPercent(),
            'total_price' => $shopProduct->getTotalPrice(),
            'currency_code' => $shopProduct->currency_code,
            'shop_item_product_id' => $shopProduct->id,
        ]);

        $request = new OrdersCreateRequest();
        $request->prefer('return=representation');
        $request->body = [
            "intent" => "CAPTURE",
            "purchase_units" => [
                [
                    "reference_id" => uniqid(),
                    "description" => $shopProduct->display . ($discount ? (" (" . __('Discount') . " " . $discount . '%)') : ""),
                    "amount"       => [
                        "value"         => $shopProduct->getTotalPrice(),
                        'currency_code' => strtoupper($shopProduct->currency_code),
                        'breakdown' => [
                            'item_total' =>
                            [
                                'currency_code' => strtoupper($shopProduct->currency_code),
                                'value' => number_format($price, 2),
                            ],
                            'tax_total' =>
                            [
                                'currency_code' => strtoupper($shopProduct->currency_code),
                                'value' => $shopProduct->getTaxValue(),
                            ]
                        ]
                    ]
                ]
            ],
            "application_context" => [
                "cancel_url" => route('payment.Cancel'),
                "return_url" => route('payment.PayPalSuccess', ['payment' => $payment->id, 'couponCode' => $coupon_code]),
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

            // make sure the link is not empty
            if (empty($response->result->links[1]->href)) {
                throw new \Exception('No redirect link found');
            }

            Redirect::away($response->result->links[1]->href)->send();
            return;
        } catch (HttpException $ex) {
            Log::error('PayPal Payment: ' . $ex->getMessage());
            $payment->delete();

            Redirect::route('store.index')->with('error', __('Payment failed'))->send();
            return;
        }
    }

    static function PaypalSuccess(Request $laravelRequest): void
    {
        $user = Auth::user();
        $user = User::findOrFail($user->id);

        $payment = Payment::findOrFail($laravelRequest->payment);
        $shopProduct = ShopProduct::findOrFail($payment->shop_item_product_id);
				$coupon_code = $laravelRequest->input('couponCode');

        $request = new OrdersCaptureRequest($laravelRequest->input('token'));
        $request->prefer('return=representation');

        try {
            // Call API with your client and get a response for your call
            $response = self::getPayPalClient()->execute($request);
            if ($response->statusCode == 201 || $response->statusCode == 200) {
                //update payment
                $payment->update([
                    'status' => 'paid',
                    'payment_id' => $response->result->id,
                ]);

								// increase the use of the coupon when the payment is confirmed.
								if ($coupon_code) {
									$coupon = new Coupon;
									$coupon->incrementUses($coupon_code);
								}

                event(new UserUpdateCreditsEvent($user));
                event(new PaymentEvent($user, $payment, $shopProduct));

                // redirect to the payment success page with success message
                Redirect::route('home')->with('success', 'Payment successful')->send();
            } elseif (env('APP_ENV') == 'local') {
                // If call returns body in response, you can get the deserialized version from the result attribute of the response
                $payment->delete();
                dd($response);
            } else {
                $payment->update([
                    'status' => 'cancelled',
                    'payment_id' => $response->result->id,
                ]);
                abort(500);
            }
        } catch (HttpException $ex) {
            if (env('APP_ENV') == 'local') {
                echo $ex->statusCode;
                $payment->delete();
                dd($ex->getMessage());
            } else {
                $payment->update([
                    'status' => 'cancelled',
                    'payment_id' => $response->result->id,
                ]);
                abort(422);
            }
        }
    }

    static function getPayPalClient(): PayPalHttpClient
    {
        $environment = env('APP_ENV') == 'local'
            ? new SandboxEnvironment(self::getPaypalClientId(), self::getPaypalClientSecret())
            : new ProductionEnvironment(self::getPaypalClientId(), self::getPaypalClientSecret());
        return new PayPalHttpClient($environment);
    }
    /**
     * @return string
     */
    static function getPaypalClientId(): string
    {
        $settings = new PayPalSettings();
        return env('APP_ENV') == 'local' ?  $settings->sandbox_client_id : $settings->client_id;
    }
    /**
     * @return string
     */
    static function getPaypalClientSecret(): string
    {
        $settings = new PayPalSettings();
        return env('APP_ENV') == 'local' ? $settings->sandbox_client_secret : $settings->client_secret;
    }
}
