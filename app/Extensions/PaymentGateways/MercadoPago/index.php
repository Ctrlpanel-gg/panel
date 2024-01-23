<?php

use App\Events\PaymentEvent;
use App\Events\UserUpdateCreditsEvent;
use App\Models\PartnerDiscount;
use App\Models\Payment;
use App\Models\ShopProduct;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Log;
use MercadoPago\SDK;
use MercadoPago\Preference;
use MercadoPago;
/**
 * @param Request $request
 * @param ShopProduct $shopProduct
 */
function MercadoPagoPay(Request $request)
{
    /** @var User $user */
    $user = Auth::user();
    $shopProduct = ShopProduct::findOrFail($request->shopProduct);
    $discount = PartnerDiscount::getDiscount();

    // create a new payment server
    $payment = Payment::create([
        'user_id' => $user->id,
        'payment_id' => null,
        'payment_method' => 'mercadopago',
        'type' => $shopProduct->type,
        'status' => 'open',
        'amount' => $shopProduct->quantity,
        'price' => $shopProduct->price - ($shopProduct->price * $discount / 100),
        'tax_value' => $shopProduct->getTaxValue(),
        'tax_percent' => $shopProduct->getTaxPercent(),
        'total_price' => $shopProduct->getTotalPrice(),
        'currency_code' => $shopProduct->currency_code,
        'shop_item_product_id' => $shopProduct->id,
    ]);

    try {
        //basic restriction
        if (!str_contains(config('app.url'), 'https://')) {
            throw new \Exception('It is not possible to purchase via MercadoPago: APP_URL does not have HTTPS, required by Mercado Pago.');
        }
        // MercadoPago SDK
        SDK::setAccessToken(MercadoPagoAccess());

        // MercadoPago Payment
        $preference = new Preference();
        $preference->back_urls = [
            'success' => route('payment.MercadoPagoChecker'),
            'failure' => route('payment.Cancel'),
            'pending' => route('payment.MercadoPagoChecker'),
        ];
        $preference->notification_url = route('payment.MercadoPagoIPN');

        $preference->payer = new \MercadoPago\Payer();
        $preference->payer->email = $user->email;
        $item = new \MercadoPago\Item();

        $item->title = $shopProduct->display . ($discount ? (" (" . __('Discount') . " " . $discount . '%)') : "");
        $item->quantity = $shopProduct->quantity;
        $item->unit_price = $shopProduct->price - ($shopProduct->price * $discount / 100);
        $item->currency = $shopProduct->currency_code;
        $preference->items = [$item];
        $preference->metadata = [
            'credit_amount' => $shopProduct->quantity,
            'user_id' => $user->id,
            'user_email' => $user->email,
            'crtl_panel_payment_id' => $payment->id,
        ];
        $preference->save();
        // Send to Mercado Pago
        Redirect::to($preference->init_point)->send();
    } catch (HttpException $ex) {
        Log::error('Mercado Pago Payment: ' . $ex->getMessage());
        $payment->delete();

        Redirect::route('store.index')->with('error', __('Payment failed'))->send();
        return;
    }
}
/**
 * @param Request $laravelRequest
 */
function MercadoPagoChecker(Request $laravelRequest)
{
    $user = Auth::user();
    $user = User::findOrFail($user->id);

    try {
        SDK::setAccessToken(MercadoPagoAccess());
        $MpagoPayment = MercadoPago\Payment::find_by_id($laravelRequest->input('payment_id'));

        $payment = Payment::findOrFail($MpagoPayment->metadata->crtl_panel_payment_id);
        $shopProduct = ShopProduct::findOrFail($payment->shop_item_product_id);

        $status = $MpagoPayment->status;

        $Message = 'Payment successful';
        // Inicia O Setamento do pagamento
        if ($status === 'created') {
            $Message = 'O pagamento foi Criado.';
        }

        if ($status === 'pending') {
            $Message = 'O pagamento está pendente.';
        }

        if ($status === 'rejected') {
            $Message = 'O pagamento foi rejeitado.';
        }

        if ($status === 'cancelled') {
            $payment->update([
                'status' => 'cancelled',
                'payment_id' => $laravelRequest->input('payment_id'),
            ]);
            $Message = 'O pagamento foi cancelado.';
        }

        if ($status === 'refunded') {
            $Message = 'O pagamento foi reembolsado.';
        }

        if ($status === 'charged_back') {
            $Message = 'O pagamento foi estornado.';
        }

        if ($status === 'in_process') {
            $Message = 'O pagamento está em processo de validação.';
        }

        if ($status === 'in_mediation') {
            $Message = 'O pagamento está em mediação.';
        }

        if ($status === 'approved') {
            $payment->update([
                'status' => 'paid',
                'payment_id' => $laravelRequest->input('payment_id'),
            ]);
            event(new UserUpdateCreditsEvent($user));
            event(new PaymentEvent($user, $payment, $shopProduct));
            $Message = 'Sucesso - Créditos Adicionados';
        }

        Redirect::route('home')->with('success', $Message)->send();
    } catch (Exception $e) {
        Log::error('Mercado Pago Payment: ' . $e->getMessage());
        abort(500);
    }
}
/**
 * @param Request $laravelRequest
 */
function MercadoPagoIPN(Request $laravelRequest)
{
    $topic = $laravelRequest->input('topic');

    if ($topic === 'merchant_order') {
        $status = 200;
    } else if ($topic === 'payment') {
        $status = 200;
    } else {
        try {
            $notificationId = $laravelRequest->input('data.id') ?? $laravelRequest->input('id') ?? $laravelRequest->input('payment_id') ?? 'error12011';
            if ($notificationId == 'error12011') {
                $status = 400;
            } elseif ($notificationId == '123456') {
                $status = 200;
            } else {
                $result = MercadoPagoIpnProcess($notificationId);
                $status = $result;
            }
        } catch (\Exception $e) {
            Log::error('Mercado Pago Payment IPN: ' . $e->getMessage());
            $status = 401;
        }
    }
    if($status === 200) {
        return response()->json(['success' => true], 200);
    } else {
        abort($status);
    }
}

function MercadoPagoIpnProcess($notificationId)
{
    $Response = 200;
    try {
        SDK::setAccessToken(MercadoPagoAccess());
        $MpagoPayment = MercadoPago\Payment::find_by_id($notificationId);

        $payment = Payment::findOrFail($MpagoPayment->metadata->crtl_panel_payment_id);
        $shopProduct = ShopProduct::findOrFail($payment->shop_item_product_id);
        $user = User::where('id', $payment->user_id)->first();

        $status = $MpagoPayment->status;
        if ($status === 'approved') {
            $payment->update([
                'status' => 'paid',
                'payment_id' => $notificationId,
            ]);
            event(new UserUpdateCreditsEvent($user));
            event(new PaymentEvent($user, $payment, $shopProduct));
        }
    } catch (Exception $ex) {
        Log::error('Mercado Pago Payment IPN: ' . $e->getMessage());
        $Response = 500;
    }
    return $Response;
}
/**
 * Mercado Pago Access Token
 */
function MercadoPagoAccess()
{
    return config('SETTINGS::PAYMENTS:MPAGO:ACCESS_TOKEN');
}
