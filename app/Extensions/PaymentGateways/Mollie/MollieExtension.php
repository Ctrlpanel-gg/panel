<?php

namespace App\Extensions\PaymentGateways\Mollie;

use App\Helpers\AbstractExtension;
use App\Events\PaymentEvent;
use App\Events\UserUpdateCreditsEvent;
use App\Models\PartnerDiscount;
use App\Models\Payment;
use App\Models\ShopProduct;
use App\Models\User;
use App\Models\Coupon;
use App\Traits\Coupon as CouponTrait;
use App\Events\CouponUsedEvent;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

/**
 * Summary of PayPalExtension
 */
class MollieExtension extends AbstractExtension
{
    use CouponTrait;

    public static function getConfig(): array
    {
        return [
            "name" => "Mollie",
            "RoutesIgnoreCsrf" => [
                "payment/MollieWebhook"
            ],
        ];
    }

    public function pay(Request $request): void
    {
        $url = 'https://api.mollie.com/v2/payments';
        $settings = new MollieSettings();

        $user = Auth::user();
        $shopProduct = ShopProduct::findOrFail($request->shopProduct);
        $discount = PartnerDiscount::getDiscount();
        $couponCode = $request->input('couponCode');
        $isValidCoupon = $this->validateCoupon($request->user(), $couponCode, $request->shopProduct);
        $price = $shopProduct->price;

        // Coupon Discount.
        if ($isValidCoupon->getStatusCode() == 200) {
            $price = $this->calcDiscount($price, $isValidCoupon->getData());
        }

        // Partner Discount.
        $price = $price - ($price * $discount / 100);
        $price = number_format($price, 2, thousands_separator: '');

        // create a new payment
        $payment = Payment::create([
            'user_id' => $user->id,
            'payment_id' => null,
            'payment_method' => 'mollie',
            'type' => $shopProduct->type,
            'status' => 'open',
            'amount' => $shopProduct->quantity,
            'price' => $price,
            'tax_value' => $shopProduct->getTaxValue(),
            'tax_percent' => $shopProduct->getTaxPercent(),
            'total_price' => $shopProduct->getTotalPrice(),
            'currency_code' => $shopProduct->currency_code,
            'shop_item_product_id' => $shopProduct->id,
        ]);

        try {
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer ' . $settings->api_key,
            ])->post($url, [
                'amount' => [
                    'currency' => $shopProduct->currency_code,
                    'value' => $price,
                ],
                'description' => "Order #{$payment->id} - " . $shopProduct->name,
                'redirectUrl' => route('payment.MollieSuccess', ['couponCode' => $couponCode]),
                'cancelUrl' => route('payment.Cancel'),
                'webhookUrl' => url('/extensions/payment/MollieWebhook'),
                'metadata' => [
                    'payment_id' => $payment->id,
                ],
            ]);

            if ($response->status() != 201) {
                Log::error('Mollie Payment: ' . $response->body());
                $payment->delete();

                Redirect::route('store.index')->with('error', __('Payment failed'))->send();
                return;
            }

            $payment->update([
                'payment_id' => $response->json()['id'],
            ]);

            Redirect::away($response->json()['_links']['checkout']['href'])->send();
            return;
        } catch (Exception $ex) {
            Log::error('Mollie Payment: ' . $ex->getMessage());
            $payment->delete();

            Redirect::route('store.index')->with('error', __('Payment failed'))->send();
            return;
        }
    }

    static function success(Request $request): void
    {
        $payment = Payment::findOrFail($request->input('payment'));
        $payment->status = 'pending';
        $couponCode = $request->input('couponCode');

        if ($couponCode) {
            event(new CouponUsedEvent(new Coupon, $couponCode));
        }

        Redirect::route('home')->with('success', 'Your payment is being processed')->send();
        return;
    }

    static function webhook(Request $request): JsonResponse
    {
        $url = 'https://api.mollie.com/v2/payments/' . $request->id;
        $settings = new MollieSettings();

        try {
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer ' . $settings->api_key,
            ])->get($url);
            if ($response->status() != 200) {
                Log::error('Mollie Payment Webhook: ' . $response->json()['title']);
                return response()->json(['success' => false]);
            }

            $payment = Payment::findOrFail($response->json()['metadata']['payment_id']);
            $payment->status->update([
                'status' => $response->json()['status'],
            ]);

            $shopProduct = ShopProduct::findOrFail($payment->shop_item_product_id);
            event(new PaymentEvent($payment, $payment, $shopProduct));

            if ($response->json()['status'] == 'paid') {
                $user = User::findOrFail($payment->user_id);
                event(new UserUpdateCreditsEvent($user));
            }
        } catch (Exception $ex) {
            Log::error('Mollie Payment Webhook: ' . $ex->getMessage());
            return response()->json(['success' => false]);
        }

        // return a 200 status code
        return response()->json(['success' => true]);
    }
}
