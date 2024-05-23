<?php

namespace App\Extensions\PaymentGateways\MercadoPago;

use App\Classes\AbstractExtension;
use App\Enums\PaymentStatus;
use App\Events\PaymentEvent;
use App\Events\UserUpdateCreditsEvent;
use App\Models\Payment;
use App\Models\ShopProduct;
use App\Models\User;
use App\Traits\Coupon as CouponTrait;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use App\Notifications\ConfirmPaymentNotification;
use App\Extensions\PaymentGateways\MercadoPago\MercadoPagoSettings;

/**
 * Summary of MercadoPagoExtension
 */
class MercadoPagoExtension extends AbstractExtension
{
    use CouponTrait;

    public static function getConfig(): array
    {
        return [
            "name" => "MercadoPago",
            "RoutesIgnoreCsrf" => [
                "payment/MercadoPagoWebhook"
            ],
        ];
    }

    public static function getRedirectUrl(Payment $payment, ShopProduct $shopProduct, string $totalPriceString): string
    {
        $user = Auth::user();
        $user = User::findOrFail($user->id);
        $url = 'https://api.mercadopago.com/checkout/preferences';
        $settings = new MercadoPagoSettings();
        try {
            $response =  Http::withHeaders([
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer ' . $settings->access_token,
            ])->post($url, [
                'back_urls' => [
                    'success' => route('payment.MercadoPagoSuccess'),
                    'failure' => route('payment.Cancel'),
                    'pending' => route('payment.MercadoPagoSuccess'),
                ],
                'notification_url' => route('payment.MercadoPagoWebhook'),
                'payer' => [
                    'email' => $user->email,
                ],
                'items' => [
                    [
                        'title' => "Order #{$payment->id} - " . $shopProduct->name,
                        'quantity' => 1,
                        // convert to float
                        'unit_price' => floatval($totalPriceString),
                        'currency_id' => $shopProduct->currency_code,
                    ],
                ],
                'metadata' => [
                    'credit_amount' => $shopProduct->quantity,
                    'user_id' => $user->id,
                    'crtl_panel_payment_id' => $payment->id,
                ],
            ]);

            if ($response->successful()) {
                return $response->json()['init_point'];
            } else {
                Log::error('MercadoPago Payment: ' . $response->body());
                throw new Exception('Payment failed');
            }
        } catch (Exception $ex) {
            Log::error('MercadoPago Payment: ' . $ex->getMessage());
            throw new Exception('Payment failed');
        }
    }

    static function Success(Request $request): void
    {
        $payment = Payment::findOrFail($request->input('payment'));
        $payment->status = PaymentStatus::PROCESSING;
        $payment->save();
        Redirect::route('home')->with('success', 'Your payment is being processed')->send();
        return;
    }

    static function Webhook(Request $request): JsonResponse
    {
        $topic = $request->input('topic');
        if ($topic === 'merchant_order') {
            // ignore other types IPN 
            return response()->json(['success' => true]);
        } else if ($topic === 'payment') {
            // ignore other types IPN 
            return response()->json(['success' => true]);
        } else {
            try {
                $notificationId = $request->input('data_id') || $request->input('data.id') || "unknown";
                if ($notificationId == 'unknown') {
                    return response()->json(['success' => false]);
                } else if ($notificationId == '123456') {
                    // mercado pago api test
                    return response()->json(['success' => true]);
                } else {
                    $url = "https://api.mercadopago.com/v1/payments/" . $notificationId;
                    $settings = new MercadoPagoSettings();
                    $response = Http::withHeaders([
                        'Content-Type' => 'application/json',
                        'Authorization' => 'Bearer ' . $settings->access_token,
                    ])->get($url);

                    if ($response->successful()) {
                        $mercado = $response->json();
                        $status = $mercado['status'];
                        $payment = Payment::findOrFail($mercado['metadata']['crtl_panel_payment_id']);
                        $shopProduct = ShopProduct::findOrFail($payment->shop_item_product_id);
                        if ($status == "approved") {
                            // avoids double additions, if the user enters after the webhook has already added the credits
                            if ($payment->status !== PaymentStatus::PAID) {
                                $user = User::findOrFail($payment->user_id);
                                $payment->status = PaymentStatus::PAID;
                                $payment->save();
                                $user->notify(new ConfirmPaymentNotification($payment));
                                event(new PaymentEvent($user, $payment, $shopProduct));
                                event(new UserUpdateCreditsEvent($user));
                            }
                        } else {
                            if ($status == "cancelled") {
                                $user = User::findOrFail($payment->user_id);
                                $payment->status = PaymentStatus::CANCELED;
                            } else {
                                $user = User::findOrFail($payment->user_id);
                                $payment->status = PaymentStatus::PROCESSING;
                            }
                            $payment->save();
                            event(new PaymentEvent($user, $payment, $shopProduct));
                        }
                    }
                }
            } catch (Exception $ex) {
                Log::error('MercadoPago Webhook(IPN) Payment: ' . $ex->getMessage());
                return response()->json(['success' => false]);
            }
        }
        return response()->json(['success' => true]);
    }
}
