<?php

namespace App\Http\Controllers\Admin;

use App\Events\UserUpdateCreditsEvent;
use App\Http\Controllers\Controller;
use App\Models\Configuration;
use App\Models\Payment;
use App\Models\CreditProduct;
use App\Models\Product;
use App\Models\User;
use App\Notifications\ConfirmPaymentNotification;
use Exception;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use PayPalCheckoutSdk\Core\PayPalHttpClient;
use PayPalCheckoutSdk\Core\ProductionEnvironment;
use PayPalCheckoutSdk\Core\SandboxEnvironment;
use PayPalCheckoutSdk\Orders\OrdersCaptureRequest;
use PayPalCheckoutSdk\Orders\OrdersCreateRequest;
use PayPalHttp\HttpException;
use Stripe\Stripe;


class PaymentController extends Controller
{

    /**
     * @return Application|Factory|View
     */
    public function index()
    {
        return view('admin.payments.index')->with([
            'payments' => Payment::paginate(15)
        ]);
    }

    /**
     * @param Request $request
     * @param CreditProduct $creditProduct
     * @return Application|Factory|View
     */
    public function checkOut(Request $request, CreditProduct $creditProduct)
    {
        return view('store.checkout')->with([
            'product'      => $creditProduct,
            'taxvalue'     => $creditProduct->getTaxValue(),
            'taxpercent'   => $creditProduct->getTaxPercent(),
            'total'        => $creditProduct->getTotalPrice()
        ]);
    }

    /**
     * @param Request $request
     * @param CreditProduct $creditProduct
     * @return RedirectResponse
     */
    public function PaypalPay(Request $request, CreditProduct $creditProduct)
    {
        $request = new OrdersCreateRequest();
        $request->prefer('return=representation');
        $request->body = [
            "intent" => "CAPTURE",
            "purchase_units" => [
                [
                    "reference_id" => uniqid(),
                    "description" => $creditProduct->description,
                    "amount"       => [
                        "value"         => $creditProduct->getTotalPrice(),
                        'currency_code' => strtoupper($creditProduct->currency_code),
                        'breakdown' =>[
                            'item_total' =>
                               [
                                    'currency_code' => strtoupper($creditProduct->currency_code),
                                    'value' => $creditProduct->price,
                                ],
                            'tax_total' =>
                                [
                                    'currency_code' => strtoupper($creditProduct->currency_code),
                                    'value' => $creditProduct->getTaxValue(),
                                ]
                        ]
                    ]
                ]
            ],
            "application_context" => [
                "cancel_url" => route('payment.Cancel'),
                "return_url" => route('payment.PaypalSuccess', ['product' => $creditProduct->id]),
                'brand_name' =>  config('app.name', 'Laravel'),
                'shipping_preference'  => 'NO_SHIPPING'
            ]


        ];


        try {
            // Call API with your client and get a response for your call
            $response = $this->getPayPalClient()->execute($request);
            return redirect()->away($response->result->links[1]->href);

            // If call returns body in response, you can get the deserialized version from the result attribute of the response
        } catch (HttpException $ex) {
            echo $ex->statusCode;
            dd(json_decode($ex->getMessage()));
        }

    }

    /**
     * @return PayPalHttpClient
     */
    protected function getPayPalClient()
    {
        $environment = env('APP_ENV') == 'local'
            ? new SandboxEnvironment($this->getPaypalClientId(), $this->getPaypalClientSecret())
            : new ProductionEnvironment($this->getPaypalClientId(), $this->getPaypalClientSecret());

        return new PayPalHttpClient($environment);
    }

    /**
     * @return string
     */
    protected function getPaypalClientId()
    {
        return env('APP_ENV') == 'local' ? env('PAYPAL_SANDBOX_CLIENT_ID') : env('PAYPAL_CLIENT_ID');
    }

    /**
     * @return string
     */
    protected function getPaypalClientSecret()
    {
        return env('APP_ENV') == 'local' ? env('PAYPAL_SANDBOX_SECRET') : env('PAYPAL_SECRET');
    }

    /**
     * @param Request $laravelRequest
     */
    public function PaypalSuccess(Request $laravelRequest)
    {
        /** @var CreditProduct $creditProduct */
        $creditProduct = CreditProduct::findOrFail($laravelRequest->input('product'));

        /** @var User $user */
        $user = Auth::user();

        $request = new OrdersCaptureRequest($laravelRequest->input('token'));
        $request->prefer('return=representation');
        try {
            // Call API with your client and get a response for your call
            $response = $this->getPayPalClient()->execute($request);
            if ($response->statusCode == 201 || $response->statusCode == 200) {

                //update credits
                $user->increment('credits', $creditProduct->quantity);

                //update server limit
                if (Configuration::getValueByKey('SERVER_LIMIT_AFTER_IRL_PURCHASE') !== 0) {
                    if ($user->server_limit < Configuration::getValueByKey('SERVER_LIMIT_AFTER_IRL_PURCHASE')) {
                        $user->update(['server_limit' => Configuration::getValueByKey('SERVER_LIMIT_AFTER_IRL_PURCHASE')]);
                    }
                }

                //update role
                if ($user->role == 'member') {
                    $user->update(['role' => 'client']);
                }

                //store payment
                $payment = Payment::create([
                    'user_id' => $user->id,
                    'payment_id' => $response->result->id,
                    'payer_id' => $laravelRequest->input('PayerID'),
                    'type' => 'Credits',
                    'status' => $response->result->status,
                    'amount' => $creditProduct->quantity,
                    'price' => $creditProduct->price,
                    'tax_value' => $creditProduct->getTaxValue(),
                    'tax_percent' => $creditProduct->getTaxPercent(),
                    'total_price' => $creditProduct->getTotalPrice(),
                    'currency_code' => $creditProduct->currency_code,
                    'payer' => json_encode($response->result->payer),
                ]);

                //payment notification
                $user->notify(new ConfirmPaymentNotification($payment));

                event(new UserUpdateCreditsEvent($user));

                //redirect back to home
                return redirect()->route('home')->with('success', 'Your credit balance has been increased!');
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
     * @param Request $request
     */
    public function Cancel(Request $request)
    {
        return redirect()->route('store.index')->with('success', 'Payment was Canceled');
    }

     /**
     * @param Request $request
     * @param CreditProduct $creditProduct
     * @return RedirectResponse
     */
    public function StripePay(Request $request, CreditProduct $creditProduct)
    {
        \Stripe\Stripe::setApiKey('sk_test_51Js6U8J2KSABgZztx8QWiohacnzGyIlpOk48DfSoUWPW8mhqLzxcQ5B9a1Wiz8jCC4Xfp3QeBDTsuSU7hkXEUksW00JyN08hoU');

        $request = \Stripe\Checkout\Session::create([
            'line_items' => [
                [
                'price_data' => [
                  'currency' => $creditProduct->currency_code,
                  'product_data' => [
                      'name' => $creditProduct->display,
                      'description' => $creditProduct->description,
                  ],
                  'unit_amount_decimal' => round($creditProduct->price*100, 2),
                  ],
                  'quantity' => 1,
                ],
                [
                    'price_data' => [
                        'currency' => $creditProduct->currency_code,
                        'product_data' => [
                            'name' => 'Product Tax',
                            'description' => $creditProduct->getTaxPercent() . "%",
                        ],
                        'unit_amount_decimal' => round($creditProduct->getTaxValue(), 2)*100,
                        ],
                        'quantity' => 1,
                ]
            ],

            'mode' => 'payment',
              'success_url' => route('payment.StripeSuccess').'?session_id={CHECKOUT_SESSION_ID}',
              'cancel_url' => route('payment.Cancel'),
          ]);



          return redirect($request->url, 303);
    }

    /**
     * @param Request $request
     */
    public function StripeSuccess(Request $request)
    {
        echo $request->input('product');
        \Stripe\Stripe::setApiKey('sk_test_51Js6U8J2KSABgZztx8QWiohacnzGyIlpOk48DfSoUWPW8mhqLzxcQ5B9a1Wiz8jCC4Xfp3QeBDTsuSU7hkXEUksW00JyN08hoU');
        /** @var CreditProduct $creditProduct */
        $creditProduct = CreditProduct::findOrFail($request->input('product'));

        /** @var User $user */
        $user = Auth::user();



        try{
        $response = \Stripe\Checkout\Session::retrieve($request->input('session_id'));

        if ($response->payment_status == "paid") {

            //update credits
            $user->increment('credits', $creditProduct->quantity);

            //update server limit
            if (Configuration::getValueByKey('SERVER_LIMIT_AFTER_IRL_PURCHASE') !== 0) {
                if ($user->server_limit < Configuration::getValueByKey('SERVER_LIMIT_AFTER_IRL_PURCHASE')) {
                    $user->update(['server_limit' => Configuration::getValueByKey('SERVER_LIMIT_AFTER_IRL_PURCHASE')]);
                }
            }

            //update role
            if ($user->role == 'member') {
                $user->update(['role' => 'client']);
            }

            //store payment
            $payment = Payment::create([
                'user_id' => $user->id,
                'payment_id' => $response->result->id,
                'payer_id' => $request->input('PayerID'),
                'type' => 'Credits',
                'status' => $response->payment_status,
                'amount' => $creditProduct->quantity,
                'price' => $creditProduct->price,
                'tax_value' => $creditProduct->getTaxValue(),
                'tax_percent' => $creditProduct->getTaxPercent(),
                'total_price' => $creditProduct->getTotalPrice(),
                'currency_code' => $creditProduct->currency_code,
                'payer' => json_encode($response->result->payer),
            ]);

            //payment notification
            $user->notify(new ConfirmPaymentNotification($payment));

            event(new UserUpdateCreditsEvent($user));

            //redirect back to home
            return redirect()->route('home')->with('success', 'Your credit balance has been increased!');
        }
        }catch (HttpException $ex) {
            if (env('APP_ENV') == 'local') {
                echo $ex->statusCode;
                dd($ex->getMessage());
            } else {
                abort(422);
            }
        }
    }

    /**
     * @return string
     */
    protected function getStripeClientId()
    {
        return env('APP_ENV') == 'local' ? env('PAYPAL_SANDBOX_CLIENT_ID') : env('PAYPAL_CLIENT_ID');
    }

    /**
     * @return string
     */
    protected function getStripeClientSecret()
    {
        return env('STRIPE_SECRET');
    }


    /**
     * @return JsonResponse|mixed
     * @throws Exception
     */
    public function dataTable()
    {
        $query = Payment::with('user');

        return datatables($query)
            ->editColumn('user', function (Payment $payment) {
                return $payment->user->name;
            })
            ->editColumn('price', function (Payment $payment) {
                return $payment->formatToCurrency($payment->price);
            })
            ->editColumn('tax_value', function (Payment $payment) {
                return $payment->formatToCurrency($payment->tax_value);
            })
            ->editColumn('total_price', function (Payment $payment) {
                return $payment->formatToCurrency($payment->total_price);
            })
            ->editColumn('created_at', function (Payment $payment) {
                return $payment->created_at ? $payment->created_at->diffForHumans() : '';
            })
            ->make();
    }
}
