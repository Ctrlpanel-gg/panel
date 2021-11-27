<?php

namespace App\Http\Controllers\Admin;

use App\Events\UserUpdateCreditsEvent;
use App\Http\Controllers\Controller;
use App\Models\Configuration;
use App\Models\Payment;
use App\Models\PaypalProduct;
use App\Models\Product;
use App\Models\User;
use App\Notifications\ConfirmPaymentNotification;
use App\Notifications\InvoiceNotification;
use Exception;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use LaravelDaily\Invoices\Classes\Party;
use PayPalCheckoutSdk\Core\PayPalHttpClient;
use PayPalCheckoutSdk\Core\ProductionEnvironment;
use PayPalCheckoutSdk\Core\SandboxEnvironment;
use PayPalCheckoutSdk\Orders\OrdersCaptureRequest;
use PayPalCheckoutSdk\Orders\OrdersCreateRequest;
use PayPalHttp\HttpException;
use LaravelDaily\Invoices\Invoice;
use LaravelDaily\Invoices\Classes\Buyer;
use LaravelDaily\Invoices\Classes\InvoiceItem;

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
     * @param PaypalProduct $paypalProduct
     * @return Application|Factory|View
     */
    public function checkOut(Request $request, PaypalProduct $paypalProduct)
    {
        return view('store.checkout')->with([
            'product'      => $paypalProduct,
            'taxvalue'     => $paypalProduct->getTaxValue(),
            'taxpercent'   => $paypalProduct->getTaxPercent(),
            'total'        => $paypalProduct->getTotalPrice()
        ]);
    }

    /**
     * @param Request $request
     * @param PaypalProduct $paypalProduct
     * @return RedirectResponse
     */
    public function pay(Request $request, PaypalProduct $paypalProduct)
    {
        $request = new OrdersCreateRequest();
        $request->prefer('return=representation');
        $request->body = [
            "intent" => "CAPTURE",
            "purchase_units" => [
                [
                    "reference_id" => uniqid(),
                    "description" => $paypalProduct->description,
                    "amount"       => [
                        "value"         => $paypalProduct->getTotalPrice(),
                        'currency_code' => strtoupper($paypalProduct->currency_code),
                        'breakdown' =>[
                            'item_total' =>
                               [
                                    'currency_code' => strtoupper($paypalProduct->currency_code),
                                    'value' => $paypalProduct->price,
                                ],
                            'tax_total' =>
                                [
                                    'currency_code' => strtoupper($paypalProduct->currency_code),
                                    'value' => $paypalProduct->getTaxValue(),
                                ]
                        ]
                    ]
                ]
            ],
            "application_context" => [
                "cancel_url" => route('payment.cancel'),
                "return_url" => route('payment.success', ['product' => $paypalProduct->id]),
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
            ? new SandboxEnvironment($this->getClientId(), $this->getClientSecret())
            : new ProductionEnvironment($this->getClientId(), $this->getClientSecret());

        return new PayPalHttpClient($environment);
    }

    /**
     * @return string
     */
    protected function getClientId()
    {
        return env('APP_ENV') == 'local' ? env('PAYPAL_SANDBOX_CLIENT_ID') : env('PAYPAL_CLIENT_ID');
    }

    /**
     * @return string
     */
    protected function getClientSecret()
    {
        return env('APP_ENV') == 'local' ? env('PAYPAL_SANDBOX_SECRET') : env('PAYPAL_SECRET');
    }

    /**
     * @param Request $laravelRequest
     */
    public function success(Request $laravelRequest)
    {
        /** @var PaypalProduct $paypalProduct */
        $paypalProduct = PaypalProduct::findOrFail($laravelRequest->input('product'));
        /** @var User $user */
        $user = Auth::user();

        $request = new OrdersCaptureRequest($laravelRequest->input('token'));
        $request->prefer('return=representation');
        try {
            // Call API with your client and get a response for your call
            $response = $this->getPayPalClient()->execute($request);
            if ($response->statusCode == 201 || $response->statusCode == 200) {

                //update credits
                $user->increment('credits', $paypalProduct->quantity);

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
                    'amount' => $paypalProduct->quantity,
                    'price' => $paypalProduct->price,
                    'tax_value' => $paypalProduct->getTaxValue(),
                    'tax_percent' => $paypalProduct->getTaxPercent(),
                    'total_price' => $paypalProduct->getTotalPrice(),
                    'currency_code' => $paypalProduct->currency_code,
                    'payer' => json_encode($response->result->payer),
                ]);

                //payment notification
                $user->notify(new ConfirmPaymentNotification($payment));

                event(new UserUpdateCreditsEvent($user));

                //create invoice
                $seller = new Party([
                    'name'          => 'Hafuga Company',
                    'phone'         => '+49 12346709',
                    'address'       => 'Deutschlandstr 4, 66666 Hell',
                    'custom_fields' => [
                        'UST_ID' => '365#GG',
                        'E-Mail' => 'invoice@hafuga.de',
                    ],
                ]);



                $customer = new Buyer([
                    'name'          => 'Not Dennis',
                    'custom_fields' => [
                        'email' => 'customer@google.de',
                        'order number' => '> 654321 <',
                    ],
                ]);
                $item = (new InvoiceItem())->title($paypalProduct->description)->pricePerUnit($paypalProduct->price);

                $lastInvoiceID = \App\Models\invoice::where("invoice_name","like","%".now()->format('M')."%")->max("id");
                $newInvoiceID = $lastInvoiceID + 1;

                $invoice = Invoice::make()
                    ->buyer($customer)
                    ->seller($seller)
                    ->discountByPercent(0)
                    ->taxRate(floatval($paypalProduct->getTaxPercent()))
                    ->shipping(0)
                    ->addItem($item)
                    ->status(__('invoices::invoice.paid'))

                    ->series(now()->format('M'))
                    ->delimiter("-")
                    ->sequence($newInvoiceID)
                    ->serialNumberFormat('{SEQUENCE} - {SERIES}')

                    ->logo(public_path('vendor/invoices/logo.png'))

                    ->save('public');

                $user->notify(new InvoiceNotification($invoice));

                \App\Models\invoice::create([
                    'invoice_user' => $user->id,
                    'invoice_name' => "invoice_".$invoice->series.$invoice->delimiter.$invoice->sequence,
                    'payment_id' => $payment->payment_id,
                ]);
                //redirect back to home
                return redirect()->route('home')->with('success', 'Your credit balance has been increased! Find the invoice in your Notifications');
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
    public function cancel(Request $request)
    {
        return redirect()->route('store.index')->with('success', 'Payment was Canceled');
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
