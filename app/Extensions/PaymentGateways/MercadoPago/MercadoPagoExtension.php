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
use Illuminate\Http\RedirectResponse;

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
        /**
         * For Mercado Pago to work correctly,
         * it is necessary to use SSL and the app.url must start with "https://",
         * this is necessary so that the webhook receives the information and not an error.
         */
        if (!str_contains(config('app.url'), 'https://')) {
            throw new Exception(__('It is not possible to purchase via MercadoPago: APP_URL does not have HTTPS, required by Mercado Pago.'));
        }

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
                'auto_return' => 'approved',
                'notification_url' => route('payment.MercadoPagoWebhook'),
                'payer' => [
                    'email' => $user->email,
                ],
                'items' => [
                    [
                        'title' => "Order #{$payment->id} - " . $shopProduct->name,
                        'quantity' => 1,
                        'unit_price' => floatval($totalPriceString),
                        'currency_id' => $shopProduct->currency_code,
                    ],
                ],
                'external_reference' => $payment->id,
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

    static function Success(Request $request): RedirectResponse
    {
        $payment = Payment::findOrFail($request->input('external_reference'));

        // In some cases, the webhook is received even before the success route.
        if ($payment->status === PaymentStatus::PAID) {
            return Redirect::route('home')->with('success', 'Your payment has already been processed!')->send();
        }

        $payment->status = PaymentStatus::PROCESSING;
        $payment->save();

        return Redirect::route('home')->with('success', 'Your payment is being processed!')->send();
    }

    static function Webhook(Request $request): JsonResponse
    {
        $topic = $request->input('topic');
        $action = $request->input('action');

        /**
         * Mercado Pago sends several requests for information in the webhook,
         *  but most are for other types of API, and that is why it is filtered here.
         */
        if ($topic && ($topic === 'merchant_order' || $topic === 'payment')) {
            return response()->json(['success' => true]);
        }

        try {
            if($action) {
                $notification = $request['data']['id'];

                // Filter the API for payments
                if (!$notification || !$action) return response()->json(['success' => false], 400);
                // Mercado pago test api, for testing webhook request
                if ($notification == '123456') return response()->json(['success' => true], 200);

                /**
                 * Check action have payment.*,
                 * what is expected for this type of api
                 */
                if (str_contains($action, 'payment')) {
                    $url = "https://api.mercadopago.com/v1/payments/" . $notification;
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
                            // Avoid double addition of credits, whether due to double requests from the paid market, or a malicious user
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
                        return response()->json(['success' => true]);
                    } else {
                        return response()->json(['success' => false]);
                    }
                } else {
                    return response()->json(['success' => false]);
                }
            }
        } catch (Exception $ex) {
            Log::error('MercadoPago Webhook(IPN) Payment: ' . $ex->getMessage());
            return response()->json(['success' => false]);
        }
        return response()->json(['success' => true]);
    }
}
