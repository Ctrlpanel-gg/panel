<?php

namespace App\Http\Controllers\Admin;

use App\Enums\PaymentStatus;
use App\Events\CouponUsedEvent;
use App\Events\PaymentEvent;
use App\Events\UserUpdateCreditsEvent;
use App\Helpers\CurrencyHelper;
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
    const WRITE_PERMISSION = "admin.payments.write";

    use CouponTrait;

    /**
     * @return Application|Factory|View
     */
    public function index(LocaleSettings $locale_settings, GeneralSettings $general_settings)
    {
        $this->checkPermission(self::VIEW_PERMISSION);


        return view('admin.payments.index')->with([
            'payments' => Payment::paginate(15),
            'locale_datatables' => $locale_settings->datatables,
            'credits_display_name' => $general_settings->credits_display_name,
        ]);
    }

    /**
     * @param  Request  $request
     * @param  ShopProduct  $shopProduct
     * @return Application|Factory|View
     */
    public function checkOut(ShopProduct $shopProduct, GeneralSettings $general_settings, CouponSettings $coupon_settings, CurrencyHelper $currencyHelper)
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
                if (!$extensionSettings || !($extensionSettings->enabled ?? false)) {
                    continue;
                }


                $payment = new \stdClass();
                $payment->name = ExtensionHelper::getExtensionConfig($extensionName, 'name') ?? $extensionName;
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
    public function handleFreeProduct(ShopProduct $shopProduct, GeneralSettings $general_settings, ?string $couponCode = null)
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
            'price' => 0,
            'tax_value' => 0,
            'tax_percent' => 0,
            'total_price' => 0,
            'currency_code' => $shopProduct->currency_code,
            'shop_item_product_id' => $shopProduct->id,
            'coupon_code' => $couponCode,
        ]);

        if ($couponCode) {
            event(new CouponUsedEvent($couponCode, $user));
        }

        event(new UserUpdateCreditsEvent($user));
        event(new PaymentEvent($user, $payment, $shopProduct));

        //not sending an invoice

        //redirect back to home
        return redirect()->route('home')->with('success', __('Your :credits balance has been increased!', ['credits' => $general_settings->credits_display_name]));
    }

    public function pay(Request $request)
    {
        $request->validate([
            'product_id' => ['required', 'exists:shop_products,id'],
            'payment_method' => ['nullable', 'string', 'max:191'],
            'coupon_code' => ['nullable', 'string', 'max:191'],
        ]);

        try {
            $user = Auth::user();
            $user = User::findOrFail($user->id);
            $productId = $request->input('product_id');
            $shopProduct = ShopProduct::findOrFail($productId);

            $paymentGateway = $request->input('payment_method');
            $couponCode = $request->input('coupon_code');

            $subtotal = $shopProduct->getTotalPrice();

            // Apply Coupon
            if ($couponCode) {
                if ($this->isCouponValid($couponCode, $user, $shopProduct->id)) {
                    $subtotal = $this->applyCoupon($couponCode, $subtotal);
                } else {
                    $couponCode = null;
                }
            }

            if ($subtotal <= 0) {
                return $this->handleFreeProduct($shopProduct, $general_settings, $couponCode);
            }

            $enabledPaymentGateways = [];
            $extensions = ExtensionHelper::getAllExtensionsByNamespace('PaymentGateways');
            foreach ($extensions as $extension) {
                $extensionName = basename($extension);
                $extensionSettings = ExtensionHelper::getExtensionSettings($extensionName);

                if ($extensionSettings && ($extensionSettings->enabled ?? false)) {
                    $enabledPaymentGateways[] = $extensionName;
                }
            }

            if (!in_array($paymentGateway, $enabledPaymentGateways, true)) {
                return redirect()->route('checkout', $shopProduct)->with('error', __('The selected payment gateway is unavailable.'));
            }

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
                'total_price' => $subtotal,
                'currency_code' => $shopProduct->currency_code,
                'shop_item_product_id' => $shopProduct->id,
                'coupon_code' => $couponCode,
            ]);

            $paymentGatewayExtension = ExtensionHelper::getExtensionClass($paymentGateway);
            if (!$paymentGatewayExtension || !class_exists($paymentGatewayExtension)) {
                throw new Exception('Invalid payment gateway class for: ' . $paymentGateway);
            }

            $redirectUrl = $paymentGatewayExtension::getRedirectUrl($payment, $shopProduct, $subtotal);

        } catch (Exception $e) {
            Log::error('Payment checkout failed', [
                'user_id' => Auth::id(),
                'product_id' => $request->input('product_id'),
                'payment_method' => $request->input('payment_method'),
                'error' => $e->getMessage(),
            ]);
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
        $this->checkPermission(self::VIEW_PERMISSION);

        $query = Payment::with('user');

        return datatables($query)

            ->addColumn('user', function (Payment $payment) {
                return ($payment->user) ? '<a href="' . route('admin.users.show', $payment->user->id) . '">' . $payment->user->name . '</a>' : __('Unknown user');
            })
            ->editColumn('amount', function (Payment $payment, CurrencyHelper $currencyHelper) {
                return $payment->type == 'Credits' ? $currencyHelper->formatForDisplay($payment->amount) : $payment->amount;
            })
            ->editColumn('type', function (Payment $payment, GeneralSettings $general_settings) {
                return $payment->type == 'Credits' ? $general_settings->credits_display_name : $payment->type;
            })
            ->editColumn('price', function (Payment $payment, CurrencyHelper $currencyHelper) {
                return $currencyHelper->formatToCurrency($payment->price, $payment->currency_code);
            })
            ->editColumn('tax_value', function (Payment $payment, CurrencyHelper $currencyHelper) {
                return $currencyHelper->formatToCurrency($payment->tax_value, $payment->currency_code);
            })
            ->editColumn('tax_percent', function (Payment $payment) {
                return $payment->tax_percent . ' %';
            })
            ->editColumn('total_price', function (Payment $payment, CurrencyHelper $currencyHelper) {
                return $currencyHelper->formatToCurrency($payment->total_price, $payment->currency_code);
            })
            ->editColumn('created_at', function (Payment $payment) {
                return [
                    'display' => $payment->created_at ? $payment->created_at->diffForHumans() : '',
                    'raw' => $payment->created_at ? strtotime($payment->created_at) : ''
                ];
            })
            ->addColumn('actions', function (Payment $payment) {
                $invoice = Invoice::where('payment_id', '=', $payment->payment_id)->first();

                $actions = '';
                if ($invoice && File::exists(storage_path('app/invoice/' . $invoice->invoice_user . '/' . $invoice->created_at->format('Y') . '/' . $invoice->invoice_name . '.pdf'))) {
                    $actions .= '<a data-content="' . __('Download') . '" data-toggle="popover" data-trigger="hover" data-placement="top" href="' . route('admin.invoices.downloadSingleInvoice', ['id' => $payment->payment_id]) . '" class="mr-1 text-white btn btn-sm btn-info"><i class="fas fa-file-download"></i></a>';
                }

                if ($payment->status !== PaymentStatus::PAID && $payment->status !== PaymentStatus::CANCELED) {
                    $actions .= '<form method="POST" action="' . route('admin.payments.statusUpdate', $payment->id) . '" style="display:inline-block;">' . csrf_field() . '<button type="submit" class="mr-1 btn btn-sm btn-success" data-toggle="popover" data-trigger="hover" data-placement="top" data-content="' . __('Force Confirm') . '"><i class="fas fa-check"></i></button></form>';

                    $extensionClass = ExtensionHelper::getExtensionClass($payment->payment_method);
                    if ($extensionClass && class_exists($extensionClass) && $extensionClass::supportsRecheck()) {
                        $actions .= '<form method="POST" action="' . route('admin.payments.recheck', $payment->id) . '" style="display:inline-block;">' . csrf_field() . '<button type="submit" class="mr-1 btn btn-sm btn-primary" data-toggle="popover" data-trigger="hover" data-placement="top" data-content="' . __('Recheck') . '"><i class="fas fa-sync"></i></button></form>';
                    }
                }

                return $actions;
            })
            ->rawColumns(['actions', 'user'])
            ->make(true);
    }

    public function statusUpdate(Payment $payment)
    {
        $this->checkPermission(self::WRITE_PERMISSION);

        // TODO: In the future, we could add a status parameter to allow switching to any status (canceled, processing, etc.)
        if ($payment->status === PaymentStatus::PAID) {
            return redirect()->route('admin.payments.index')->with('error', __('Payment is already paid.'));
        }

        $payment->status = PaymentStatus::PAID;
        $payment->save();

        $user = User::findOrFail($payment->user_id);
        $shopProduct = ShopProduct::findOrFail($payment->shop_item_product_id);

        if ($payment->coupon_code) {
            event(new CouponUsedEvent($payment->coupon_code, $user));
        }

        try {
            $user->notify(new \App\Notifications\ConfirmPaymentNotification($payment));
        } catch (Exception $e) {
            Log::error('Force confirm notification failed: ' . $e->getMessage());
        }

        event(new PaymentEvent($user, $payment, $shopProduct));
        event(new UserUpdateCreditsEvent($user));

        return redirect()->route('admin.payments.index')->with('success', __('Payment status updated successfully.'));
    }

    public function recheck(Payment $payment)
    {
        $this->checkPermission(self::WRITE_PERMISSION);

        $extensionClass = ExtensionHelper::getExtensionClass($payment->payment_method);
        if (!$extensionClass || !class_exists($extensionClass)) {
            return redirect()->route('admin.payments.index')->with('error', __('Payment extension not found.'));
        }

        if (!$extensionClass::supportsRecheck()) {
            return redirect()->route('admin.payments.index')->with('error', __('This payment gateway does not support recheck.'));
        }

        try {
            $extensionClass::recheckPayment($payment);
        } catch (Exception $e) {
            return redirect()->route('admin.payments.index')->with('error', __('Recheck failed: ') . $e->getMessage());
        }

        $payment->refresh();
        if ($payment->status === PaymentStatus::PAID) {
            return redirect()->route('admin.payments.index')->with('success', __('Payment confirmed after recheck.'));
        }

        return redirect()->route('admin.payments.index')->with('info', __('Payment status rechecked, but it is still: ') . $payment->status->value);
    }
}
