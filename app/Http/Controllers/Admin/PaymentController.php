<?php

namespace App\Http\Controllers\Admin;

use App\Events\UserUpdateCreditsEvent;
use App\Http\Controllers\Controller;
use App\Models\PartnerDiscount;
use App\Models\Payment;
use App\Models\User;
use App\Models\ShopProduct;
use Exception;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Helpers\ExtensionHelper;


class PaymentController extends Controller
{
    /**
     * @return Application|Factory|View
     */
    public function index()
    {
        return view('admin.payments.index')->with([
            'payments' => Payment::paginate(15),
        ]);
    }

    /**
     * @param  Request  $request
     * @param  ShopProduct  $shopProduct
     * @return Application|Factory|View
     */
    public function checkOut(ShopProduct $shopProduct)
    {
        $extensions = ExtensionHelper::getAllExtensionsByNamespace('PaymentGateways');

        // build a paymentgateways array that contains the routes for the payment gateways and the image path for the payment gateway which lays in public/images/Extensions/PaymentGateways with the extensionname in lowercase
        $paymentGateways = [];
        foreach ($extensions as $extension) {
            $extensionName = basename($extension);
            $payment = new \stdClass();
            $payment->name = ExtensionHelper::getExtensionConfig($extensionName, 'name');
            $payment->image = asset('images/Extensions/PaymentGateways/' . strtolower($extensionName) . '_logo.png');
            $paymentGateways[] = $payment;
        }

        $discount = PartnerDiscount::getDiscount();

        return view('store.checkout')->with([
            'product' => $shopProduct,
            'discountpercent' => $discount,
            'discountvalue' => $discount * $shopProduct->price / 100,
            'discountedprice' => $shopProduct->getPriceAfterDiscount(),
            'taxvalue' => $shopProduct->getTaxValue(),
            'taxpercent' => $shopProduct->getTaxPercent(),
            'total' => $shopProduct->getTotalPrice(),
            'paymentGateways'   => $paymentGateways,
        ]);
    }

    /**
     * @param  Request  $request
     * @param  ShopProduct  $shopProduct
     * @return RedirectResponse
     */
    public function FreePay(ShopProduct $shopProduct)
    {
        //check if the product is really free or the discount is 100%
        if ($shopProduct->getTotalPrice() > 0) return redirect()->route('home')->with('error', __('An error ocured. Please try again.'));

        //give product
        /** @var User $user */
        $user = Auth::user();

        //not updating server limit

        //update User with bought item
        if ($shopProduct->type == "Credits") {
            $user->increment('credits', $shopProduct->quantity);
        } elseif ($shopProduct->type == "Server slots") {
            $user->increment('server_limit', $shopProduct->quantity);
        }

        //skipped the referral commission, because the user did not pay anything.

        //not giving client role

        //store payment
        $payment = Payment::create([
            'user_id' => $user->id,
            'payment_id' => uniqid(),
            'payment_method' => 'free',
            'type' => $shopProduct->type,
            'status' => 'paid',
            'amount' => $shopProduct->quantity,
            'price' => $shopProduct->price - ($shopProduct->price * PartnerDiscount::getDiscount() / 100),
            'tax_value' => $shopProduct->getTaxValue(),
            'tax_percent' => $shopProduct->getTaxPercent(),
            'total_price' => $shopProduct->getTotalPrice(),
            'currency_code' => $shopProduct->currency_code,
            'shop_item_product_id' => $shopProduct->id,
        ]);

        event(new UserUpdateCreditsEvent($user));

        //not sending an invoice

        //redirect back to home
        return redirect()->route('home')->with('success', __('Your credit balance has been increased!'));
    }

    public function pay(Request $request)
    {
        $product = ShopProduct::find($request->product_id);
        $paymentGateway = $request->payment_method;

        return redirect()->route('payment.' . $paymentGateway . 'Pay', ['shopProduct' => $product->id]);
    }

    /**
     * @param  Request  $request
     */
    public function Cancel(Request $request)
    {
        return redirect()->route('store.index')->with('info', 'Payment was Canceled');
    }

    /**
     * @return JsonResponse|mixed
     *
     * @throws Exception
     */
    public function dataTable()
    {
        $query = Payment::with('user');

        return datatables($query)

            ->addColumn('user', function (Payment $payment) {
                return ($payment->user) ? '<a href="' . route('admin.users.show', $payment->user->id) . '">' . $payment->user->name . '</a>' : __('Unknown user');
            })
            ->editColumn('price', function (Payment $payment) {
                return $payment->formatToCurrency($payment->price);
            })
            ->editColumn('tax_value', function (Payment $payment) {
                return $payment->formatToCurrency($payment->tax_value);
            })
            ->editColumn('tax_percent', function (Payment $payment) {
                return $payment->tax_percent . ' %';
            })
            ->editColumn('total_price', function (Payment $payment) {
                return $payment->formatToCurrency($payment->total_price);
            })

            ->editColumn('created_at', function (Payment $payment) {
                return [
                    'display' => $payment->created_at ? $payment->created_at->diffForHumans() : '',
                    'raw' => $payment->created_at ? strtotime($payment->created_at) : ''
                ];
            })
            ->addColumn('actions', function (Payment $payment) {
                return '<a data-content="' . __('Download') . '" data-toggle="popover" data-trigger="hover" data-placement="top"  href="' . route('admin.invoices.downloadSingleInvoice', 'id=' . $payment->payment_id) . '" class="btn btn-sm text-white btn-info mr-1"><i class="fas fa-file-download"></i></a>';
            })
            ->rawColumns(['actions', 'user'])
            ->make(true);
    }
}
