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
use Illuminate\Validation\Rule;
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
                $payment->available = true;
                $payment->unavailable_reason = null;

                $extensionClass = ExtensionHelper::getExtensionClass($extensionName);
                if ($extensionClass && class_exists($extensionClass) && method_exists($extensionClass, 'isAvailableForCheckout')) {
                    $totalPrice = (float) $currencyHelper->convertForDisplay($shopProduct->getTotalPrice());
                    $availability = $extensionClass::isAvailableForCheckout($shopProduct->currency_code, $totalPrice);
                    $payment->available = $availability['available'];
                    $payment->unavailable_reason = $availability['reason'];
                }

                $payment->fee_config = $extensionClass::getFeeConfig();
                $payment->fee_description = $extensionClass::getFeeDescription($shopProduct->currency_code);

                $paymentGateways[] = $payment;
            }
        }

        $gatewayFeeConfigs = [];
        foreach ($paymentGateways as $gateway) {
            $gatewayFeeConfigs[$gateway->name] = $gateway->fee_config ?? [];
        }

        return view('store.checkout')->with([
            'product' => $shopProduct,
            'discountpercent' => $discount,
            'discountvalue' => $discount * $shopProduct->price / 100,
            'discountedprice' => $shopProduct->getPriceAfterDiscount(),
            'taxvalue' => $shopProduct->getTaxValue(),
            'taxpercent' => $shopProduct->getTaxPercent(),
            'total' => $shopProduct->getTotalPrice(),
            'paymentGateways' => $paymentGateways,
            'gatewayFeeConfigs' => $gatewayFeeConfigs,
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

    public function pay(Request $request, GeneralSettings $general_settings)
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

            $discountedPrice = $shopProduct->getPriceAfterDiscount();

            // Apply Coupon to the pre-tax discounted price. The tax is
            // recalculated afterwards, so the total is price - discount + tax + fee.
            if ($couponCode) {
                if ($this->isCouponValid($couponCode, $user, $shopProduct->id)) {
                    $discountedPrice = $this->applyCoupon($couponCode, $discountedPrice);
                } else {
                    $couponCode = null;
                }
            }

            if ($discountedPrice <= 0) {
                return $this->handleFreeProduct($shopProduct, $general_settings, $couponCode);
            }

            $taxValue = $discountedPrice * $shopProduct->getTaxPercent() / 100;
            $subtotal = $discountedPrice + $taxValue;

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
                return redirect()->route('checkout', $shopProduct)->with('error', __('The selected payment gateway is unavailable'));
            }

            $paymentGatewayExtension = ExtensionHelper::getExtensionClass($paymentGateway);
            if (!$paymentGatewayExtension || !class_exists($paymentGatewayExtension)) {
                return redirect()->route('checkout', $shopProduct)->with('error', __('The selected payment gateway is unavailable'));
            }

            $availability = $paymentGatewayExtension::isAvailableForCheckout(
                $shopProduct->currency_code,
                (float) app(CurrencyHelper::class)->convertForDisplay($subtotal)
            );
            if (!$availability['available']) {
                return redirect()->route('checkout', $shopProduct)->with('error', $availability['reason']);
            }

            // Calculate the payment processing fee for this gateway and charge it
            // on top of the product total.
            $fee = $paymentGatewayExtension::getPaymentFee((int) $subtotal, $shopProduct->currency_code);
            $totalPrice = $subtotal + $fee;

            // create a new payment
            $payment = Payment::create([
                'user_id' => $user->id,
                'payment_id' => null,
                'payment_method' => $paymentGateway,
                'type' => $shopProduct->type,
                'status' => PaymentStatus::OPEN,
                'amount' => $shopProduct->quantity,
                'price' => $shopProduct->price,
                'tax_value' => $taxValue,
                'tax_percent' => $shopProduct->getTaxPercent(),
                'total_price' => $totalPrice,
                'fee' => $fee,
                'currency_code' => $shopProduct->currency_code,
                'shop_item_product_id' => $shopProduct->id,
                'coupon_code' => $couponCode,
            ]);

            $redirectUrl = $paymentGatewayExtension::getRedirectUrl($payment, $shopProduct, $totalPrice);

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
            ->editColumn('fee', function (Payment $payment, CurrencyHelper $currencyHelper) {
                return (int) $payment->fee > 0 ? $currencyHelper->formatToCurrency($payment->fee, $payment->currency_code) : '-';
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

                // paid and refunded are terminal: refunded gets no actions, paid
                // only gets the one-way Refund action.
                if ($payment->status === PaymentStatus::REFUNDED) {
                    return $actions;
                }

                $statusUpdateUrl = route('admin.payments.statusUpdate', $payment->id);

                if ($payment->status === PaymentStatus::PAID) {
                    $actions .= '<button type="button" class="mr-1 btn btn-sm btn-danger" data-toggle="popover" data-trigger="hover" data-placement="top" data-content="' . __('Refund') . '" onclick="confirmAndSubmit(\'' . $statusUpdateUrl . '\', \'refunded\', \'' . __('The payment will be marked as REFUNDED. This is irreversible and no credits or slots will be returned.') . '\', \'#d33\')"><i class="fas fa-undo-alt"></i></button>';

                    return $actions;
                }

                // Confirm: move the payment to paid and grant the related actions.
                $actions .= '<button type="button" class="mr-1 btn btn-sm btn-success" data-toggle="popover" data-trigger="hover" data-placement="top" data-content="' . __('Confirm Payment') . '" onclick="confirmAndSubmit(\'' . $statusUpdateUrl . '\', \'paid\', \'' . __('The payment will be marked as PAID and the credits or product will be granted.') . '\', \'#28a745\')"><i class="fas fa-check"></i></button>';

                $extensionClass = ExtensionHelper::getExtensionClass($payment->payment_method);
                if ($extensionClass && class_exists($extensionClass) && $extensionClass::supportsRecheck()) {
                    $actions .= '<form method="POST" action="' . route('admin.payments.recheck', $payment->id) . '" style="display:inline-block;">' . csrf_field() . '<button type="submit" class="mr-1 btn btn-sm btn-primary" data-toggle="popover" data-trigger="hover" data-placement="top" data-content="' . __('Recheck') . '"><i class="fas fa-sync"></i></button></form>';
                }

                if ($payment->status === PaymentStatus::CANCELED) {
                    // Reopen: move a canceled payment back to open.
                    $actions .= '<button type="button" class="mr-1 btn btn-sm btn-secondary" data-toggle="popover" data-trigger="hover" data-placement="top" data-content="' . __('Reopen Payment') . '" onclick="confirmAndSubmit(\'' . $statusUpdateUrl . '\', \'open\', \'' . __('The payment will be reopened.') . '\', \'#6c757d\')"><i class="fas fa-undo"></i></button>';
                } else {
                    // Cancel: move an open/processing payment to canceled.
                    $actions .= '<button type="button" class="mr-1 btn btn-sm btn-warning" data-toggle="popover" data-trigger="hover" data-placement="top" data-content="' . __('Cancel Payment') . '" onclick="confirmAndSubmit(\'' . $statusUpdateUrl . '\', \'canceled\', \'' . __('The payment will be marked as CANCELED.') . '\')"><i class="fas fa-ban"></i></button>';
                }

                return $actions;
            })
            ->rawColumns(['actions', 'user'])
            ->make(true);
    }

    public function statusUpdate(Payment $payment, Request $request)
    {
        $this->checkPermission(self::WRITE_PERMISSION);

        // Determine the target status. Defaults to PAID for backward compatibility
        // with a plain confirm request that does not include a status.
        $request->validate([
            'status' => ['nullable', Rule::enum(PaymentStatus::class)],
        ]);

        $status = $request->has('status')
            ? PaymentStatus::from($request->input('status'))
            : PaymentStatus::PAID;

        if ($payment->status === $status) {
            return redirect()->route('admin.payments.index')->with('error', __('Payment is already :status.', ['status' => $status->value]));
        }

        // Enforce the allowed transitions. paid and refunded are terminal.
        // paid can only be moved to refunded, and refunded cannot be left at all.
        $allowedTransitions = [
            PaymentStatus::OPEN->value => [PaymentStatus::PROCESSING, PaymentStatus::PAID, PaymentStatus::CANCELED],
            PaymentStatus::PROCESSING->value => [PaymentStatus::OPEN, PaymentStatus::PAID, PaymentStatus::CANCELED],
            PaymentStatus::CANCELED->value => [PaymentStatus::OPEN, PaymentStatus::PROCESSING, PaymentStatus::PAID],
            PaymentStatus::PAID->value => [PaymentStatus::REFUNDED],
            PaymentStatus::REFUNDED->value => [],
        ];

        $allowed = $allowedTransitions[$payment->status->value] ?? [];
        if (!in_array($status, $allowed, true)) {
            return redirect()->route('admin.payments.index')->with('error', __('The payment status cannot be moved from :current to :target.', ['current' => $payment->status->value, 'target' => $status->value]));
        }

        $payment->status = $status;
        $payment->save();

        $user = User::findOrFail($payment->user_id);

        // Only trigger payment-related actions when the payment transitions to PAID.
        // The transition matrix guarantees the payment was not PAID before, so
        // credits and related events cannot be granted twice for the same payment.
        if ($status === PaymentStatus::PAID) {
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
        }

        if ($status === PaymentStatus::REFUNDED) {
            return redirect()->route('admin.payments.index')->with('success', __('Payment marked as refunded. No credits or slots were returned.'));
        }

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
