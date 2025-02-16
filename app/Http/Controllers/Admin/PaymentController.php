<?php

namespace App\Http\Controllers\Admin;

use App\Enums\PaymentStatus;
use App\Events\CouponUsedEvent;
use App\Events\PaymentEvent;
use App\Events\UserUpdateCreditsEvent;
use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\PartnerDiscount;
use App\Models\Payment;
use App\Models\User;
use App\Models\ShopProduct;
use App\Traits\Coupon as CouponTrait;
use Exception;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Helpers\ExtensionHelper;
use App\Settings\CouponSettings;
use App\Settings\GeneralSettings;
use App\Settings\LocaleSettings;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

class PaymentController extends Controller
{
    const BUY_PERMISSION = 'user.shop.buy';
    const VIEW_PERMISSION = "admin.payments.read";

    use CouponTrait;

    /**
     * @return Application|Factory|View
     */
    public function index(LocaleSettings $locale_settings)
    {
        $this->checkPermission(self::VIEW_PERMISSION);


        return view('admin.payments.index')->with([
            'payments' => Payment::paginate(15),
            'locale_datatables' => $locale_settings->datatables
        ]);
    }

    /**
     * @param  Request  $request
     * @param  ShopProduct  $shopProduct
     * @return Application|Factory|View
     */
    public function checkOut(ShopProduct $shopProduct, GeneralSettings $general_settings, CouponSettings $coupon_settings)
    {
        $this->checkPermission(self::BUY_PERMISSION);

        $discount = PartnerDiscount::getDiscount();
        $price = $shopProduct->price - ($shopProduct->price * $discount / 100);

        $paymentGateways = [];
        if ($price > 0) {
            $extensions = ExtensionHelper::getAllExtensionsByNamespace('PaymentGateways');

            // build a paymentgateways array that contains the routes for the payment gateways and the image path for the payment gateway which lays in public/images/Extensions/PaymentGateways with the extensionname in lowercase
            foreach ($extensions as $extension) {
                $extensionName = basename($extension);

                $extensionSettings = ExtensionHelper::getExtensionSettings($extensionName);
                if ($extensionSettings->enabled == false) continue;


                $payment = new \stdClass();
                $payment->name = ExtensionHelper::getExtensionConfig($extensionName, 'name');
                $payment->image = asset('images/Extensions/PaymentGateways/' . strtolower($extensionName) . '_logo.png');
                $paymentGateways[] = $payment;
            }
        }

        return view('store.checkout')->with([
            'product' => $shopProduct,
            'discountpercent' => $discount,
            'discountvalue' => $discount * $shopProduct->price / 100,
            'discountedprice' => $shopProduct->getPriceAfterDiscount(),
            'taxvalue' => $shopProduct->getTaxValue(),
            'taxpercent' => $shopProduct->getTaxPercent(),
            'total' => $shopProduct->getTotalPrice(),
            'paymentGateways'   => $paymentGateways,
            'productIsFree' => $price <= 0,
            'credits_display_name' => $general_settings->credits_display_name,
            'isCouponsEnabled' => $coupon_settings->enabled,
        ]);
    }

    /**
     * @param  Request  $request
     * @param  ShopProduct  $shopProduct
     * @return RedirectResponse
     */
    public function handleFreeProduct(ShopProduct $shopProduct)
    {
        /** @var User $user */
        $user = Auth::user();

        //create a payment
        $payment = Payment::create([
            'user_id' => $user->id,
            'payment_id' => uniqid(),
            'payment_method' => 'free',
            'type' => $shopProduct->type,
            'status' => PaymentStatus::PAID,
            'amount' => $shopProduct->quantity,
            'price' => $shopProduct->price - ($shopProduct->price * PartnerDiscount::getDiscount() / 100),
            'tax_value' => $shopProduct->getTaxValue(),
            'tax_percent' => $shopProduct->getTaxPercent(),
            'total_price' => $shopProduct->getTotalPrice(),
            'currency_code' => $shopProduct->currency_code,
            'shop_item_product_id' => $shopProduct->id,
        ]);

        event(new UserUpdateCreditsEvent($user));
        event(new PaymentEvent($user, $payment, $shopProduct));

        //not sending an invoice

        //redirect back to home
        return redirect()->route('home')->with('success', __('Your credit balance has been increased!'));
    }

    public function pay(Request $request)
    {
        try {
            $user = Auth::user();
            $user = User::findOrFail($user->id);
            $productId = $request->product_id;
            $shopProduct = ShopProduct::findOrFail($productId);

            $paymentGateway = $request->payment_method;
            $couponCode = $request->coupon_code;

            $subtotal = $shopProduct->getTotalPrice();

            // Apply Coupon
            if ($couponCode) {
                if ($this->isCouponValid($couponCode, $user, $shopProduct->id)) {
                    $subtotal = $this->applyCoupon($couponCode, $subtotal);

                    event(new CouponUsedEvent($couponCode));
                }
            }

            if ($subtotal <= 0) {
                return $this->handleFreeProduct($shopProduct);
            }

            // Format the total price to a readable string
            $totalPriceString = number_format($subtotal, 2, '.', '');
            //reset the price after coupon use
            $shopProduct->price = $totalPriceString;

            // create a new payment
            $payment = Payment::create([
                'user_id' => $user->id,
                'payment_id' => null,
                'payment_method' => $paymentGateway,
                'type' => $shopProduct->type,
                'status' => PaymentStatus::OPEN,
                'amount' => $shopProduct->quantity,
                'price' => $shopProduct->price,
                'tax_value' => $shopProduct->getTaxValue(),
                'tax_percent' => $shopProduct->getTaxPercent(),
                'total_price' => $totalPriceString,
                'currency_code' => $shopProduct->currency_code,
                'shop_item_product_id' => $shopProduct->id,
            ]);

            $paymentGatewayExtension = ExtensionHelper::getExtensionClass($paymentGateway);
            $redirectUrl = $paymentGatewayExtension::getRedirectUrl($payment, $shopProduct, $totalPriceString);

        } catch (Exception $e) {
            Log::error($e->getMessage());
            return redirect()->route('store.index')->with('error', __('Oops, something went wrong! Please try again later.'));
        }

        return redirect()->away($redirectUrl);
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
                $invoice = Invoice::where('payment_id', '=', $payment->payment_id)->first();

                if ($invoice && File::exists(storage_path('app/invoice/' . $invoice->invoice_user . '/' . $invoice->created_at->format('Y') . '/' . $invoice->invoice_name . '.pdf'))) {
                    return '<a data-content="' . __('Download') . '" data-toggle="popover" data-trigger="hover" data-placement="top" href="' . route('admin.invoices.downloadSingleInvoice', ['id' => $payment->payment_id]) . '" class="mr-1 text-white btn btn-sm btn-info"><i class="fas fa-file-download"></i></a>';
                } else {
                    return '';
                }
            })
            ->rawColumns(['actions', 'user'])
            ->make(true);
    }
}
