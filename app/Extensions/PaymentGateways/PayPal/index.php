<?php

use App\Events\PaymentEvent;
use App\Events\UserUpdateCreditsEvent;
use App\Models\PartnerDiscount;
use App\Models\Payment;
use App\Models\ShopProduct;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use PayPalCheckoutSdk\Core\PayPalHttpClient;
use PayPalCheckoutSdk\Core\ProductionEnvironment;
use PayPalCheckoutSdk\Core\SandboxEnvironment;
use PayPalCheckoutSdk\Orders\OrdersCaptureRequest;
use PayPalCheckoutSdk\Orders\OrdersCreateRequest;
use PayPalHttp\HttpException;



/**
 * @param Request $request
 * @param ShopProduct $shopProduct
 * @return RedirectResponse
 */
function PaypalPay(Request $request)
{
    $shopProduct = ShopProduct::findOrFail($request->shopProduct);
    
    $request = new OrdersCreateRequest();
    $request->prefer('return=representation');
    $request->body = [
            "intent" => "CAPTURE",
            "purchase_units" => [
                [
                    "reference_id" => uniqid(),
                    "description" => $shopProduct->display . (PartnerDiscount::getDiscount()?(" (" . __('Discount') . " " . PartnerDiscount::getDiscount() . '%)'):""),
                    "amount"       => [
                        "value"         => $shopProduct->getTotalPrice(),
                        'currency_code' => strtoupper($shopProduct->currency_code),
                        'breakdown' => [
                            'item_total' =>
                            [
                                'currency_code' => strtoupper($shopProduct->currency_code),
                                'value' => $shopProduct->getPriceAfterDiscount(),
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
                "return_url" => route('payment.PayPalSuccess'),
                'brand_name' =>  config('app.name', 'Laravel'),
                'shipping_preference'  => 'NO_SHIPPING'
            ]


    ];
    try {
        // Call API with your client and get a response for your call
        $response = getPayPalClient()->execute($request);
        return redirect()->away($response->result->links[1]->href);
        // If call returns body in response, you can get the deserialized version from the result attribute of the response
    } catch (HttpException $ex) {
        echo $ex->statusCode;
        dd(json_decode($ex->getMessage()));
    }
}
/**
 * @param Request $laravelRequest
 */
function PaypalSuccess(Request $laravelRequest)
{
    /** @var ShopProduct $shopProduct */
    $shopProduct = ShopProduct::findOrFail($laravelRequest->input('product'));
    /** @var User $user */
    $user = Auth::user();
    $request = new OrdersCaptureRequest($laravelRequest->input('token'));
    $request->prefer('return=representation');
    try {
        // Call API with your client and get a response for your call
        $response = getPayPalClient()->execute($request);
        if ($response->statusCode == 201 || $response->statusCode == 200) {
            //update server limit
            if (config('SETTINGS::USER:SERVER_LIMIT_AFTER_IRL_PURCHASE') !== 0) {
                    if ($user->server_limit < config('SETTINGS::USER:SERVER_LIMIT_AFTER_IRL_PURCHASE')) {
                        $user->update(['server_limit' => config('SETTINGS::USER:SERVER_LIMIT_AFTER_IRL_PURCHASE')]);
                    }
            }
            //update User with bought item
            if ($shopProduct->type=="Credits") {
                    $user->increment('credits', $shopProduct->quantity);
            }elseif ($shopProduct->type=="Server slots"){
                    $user->increment('server_limit', $shopProduct->quantity);
            }
            //give referral commission always
            if((config("SETTINGS::REFERRAL:MODE") == "commission" || config("SETTINGS::REFERRAL:MODE") == "both") && $shopProduct->type=="Credits" && config("SETTINGS::REFERRAL::ALWAYS_GIVE_COMMISSION") == "true"){
                    if($ref_user = DB::table("user_referrals")->where('registered_user_id', '=', $user->id)->first()){
                        $ref_user = User::findOrFail($ref_user->referral_id);
                        $increment = number_format($shopProduct->quantity*(PartnerDiscount::getCommission($ref_user->id))/100,0,"","");
                        $ref_user->increment('credits', $increment);

                        //LOGS REFERRALS IN THE ACTIVITY LOG
                        activity()
                            ->performedOn($user)
                            ->causedBy($ref_user)
                            ->log('gained '. $increment.' '.config("SETTINGS::SYSTEM:CREDITS_DISPLAY_NAME").' for commission-referral of '.$user->name.' (ID:'.$user->id.')');
                    }

            }
            //update role give Referral-reward
            if ($user->role == 'member') {
                    $user->update(['role' => 'client']);

                    //give referral commission only on first purchase
                    if((config("SETTINGS::REFERRAL:MODE") == "commission" || config("SETTINGS::REFERRAL:MODE") == "both") && $shopProduct->type=="Credits" && config("SETTINGS::REFERRAL::ALWAYS_GIVE_COMMISSION") == "false"){
                        if($ref_user = DB::table("user_referrals")->where('registered_user_id', '=', $user->id)->first()){
                            $ref_user = User::findOrFail($ref_user->referral_id);
                            $increment = number_format($shopProduct->quantity*(PartnerDiscount::getCommission($ref_user->id))/100,0,"","");
                            $ref_user->increment('credits', $increment);

                            //LOGS REFERRALS IN THE ACTIVITY LOG
                            activity()
                                ->performedOn($user)
                                ->causedBy($ref_user)
                                ->log('gained '. $increment.' '.config("SETTINGS::SYSTEM:CREDITS_DISPLAY_NAME").' for commission-referral of '.$user->name.' (ID:'.$user->id.')');
                        }

                    }

            }
            //store payment
            $payment = Payment::create([
                    'user_id' => $user->id,
                    'payment_id' => $response->result->id,
                    'payment_method' => 'paypal',
                    'type' => $shopProduct->type,
                    'status' => 'paid',
                    'amount' => $shopProduct->quantity,
                    'price' => $shopProduct->price - ($shopProduct->price*PartnerDiscount::getDiscount()/100),
                    'tax_value' => $shopProduct->getTaxValue(),
                    'tax_percent' => $shopProduct->getTaxPercent(),
                    'total_price' => $shopProduct->getTotalPrice(),
                    'currency_code' => $shopProduct->currency_code,
                    'shop_item_product_id' => $shopProduct->id,
            ]);
            event(new UserUpdateCreditsEvent($user));
            event(new PaymentEvent($payment));
            
            //redirect back to home
            return redirect()->route('home')->with('success', __('Your credit balance has been increased!'));
        }
        // If call returns body in response, you can get the deserialized version from the result attribute of the response
        if (env('APP_ENV') == 'local') {
            dd($response);
        } else {
            abort(500);
        }
    } catch (HttpException $ex) {
        if (env('APP_ENV') == 'local') {
            echo $ex->statusCode;
            dd($ex->getMessage());
        } else {
            abort(422);
        }
    }
}
/**
 * @return PayPalHttpClient
 */
function getPayPalClient()
{
    $environment = env('APP_ENV') == 'local'
        ? new SandboxEnvironment(getPaypalClientId(), getPaypalClientSecret())
        : new ProductionEnvironment(getPaypalClientId(), getPaypalClientSecret());
    return new PayPalHttpClient($environment);
}
/**
 * @return string
 */
function getPaypalClientId()
{
    return env('APP_ENV') == 'local' ?  config("SETTINGS::PAYMENTS:PAYPAL:SANDBOX_CLIENT_ID") : config("SETTINGS::PAYMENTS:PAYPAL:CLIENT_ID");
}
/**
 * @return string
 */
function getPaypalClientSecret()
{
    return env('APP_ENV') == 'local' ? config("SETTINGS::PAYMENTS:PAYPAL:SANDBOX_SECRET") : config("SETTINGS::PAYMENTS:PAYPAL:SECRET");
}
function getConfig()
{
    return [
        "name" => "PayPal",
        "description" => "PayPal payment gateway",
        "settings" => [
            "mode" => [
                "type" => "select",
                "label" => "Mode",
                "value" => config("APP_ENV") == 'local' ? "sandbox" : "live",
                "options" => [
                    "sandbox" => "Sandbox",
                    "live" => "Live",
                ],
            ],
            "CLIENT_ID" => [
                "type" => "text",
                "label" => "PayPal Client ID",
                "value" => config("SETTINGS::PAYMENTS:PAYPAL:CLIENT_ID"),
            ],
            "SECRET" => [
                "type" => "text",
                "label" => "PayPal Secret",
                "value" => config("SETTINGS::PAYMENTS:PAYPAL:SECRET"),
            ],
            "SANDBOX_CLIENT_ID" => [
                "type" => "text",
                "label" => "PayPal Sandbox Client ID",
                "value" => config("SETTINGS::PAYMENTS:PAYPAL:SANDBOX_CLIENT_ID"),
            ],
            "SANDBOX_SECRET" => [
                "type" => "text",
                "label" => "PayPal Sandbox Secret",
                "value" => config("SETTINGS::PAYMENTS:PAYPAL:SANDBOX_SECRET"),
            ],
        ],
    ];
}