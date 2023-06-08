<?php

namespace App\Extensions\PaymentGateways\Mollie;

use App\Classes\AbstractExtension;
use App\Enums\PaymentStatus;
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

    public static function getRedirectUrl(Payment $payment, ShopProduct $shopProduct, string $totalPriceString): string
    {
        $url = 'https://api.mollie.com/v2/payments';
        $settings = new MollieSettings();
        try {
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer ' . $settings->api_key,
            ])->post($url, [
                'amount' => [
                    'currency' => $shopProduct->currency_code,
                    'value' => $totalPriceString,
                ],
                'description' => "Order #{$payment->id} - " . $shopProduct->name,
                'redirectUrl' => route('payment.MollieSuccess'),
                'cancelUrl' => route('payment.Cancel'),
                'webhookUrl' => url('/extensions/payment/MollieWebhook'),
                'metadata' => [
                    'payment_id' => $payment->id,
                ],
            ]);

            if ($response->status() != 201) {
                Log::error('Mollie Payment: ' . $response->body());
                throw new Exception('Payment failed');
            }

            return $response->json()['_links']['checkout']['href'];
        } catch (Exception $ex) {
            Log::error('Mollie Payment: ' . $ex->getMessage());
            throw new Exception('Payment failed');
        }
    }

    static function success(Request $request): void
    {
        $payment = Payment::findOrFail($request->input('payment'));
        $payment->status = PaymentStatus::PROCESSING;
        $payment->save();

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
            $shopProduct = ShopProduct::findOrFail($payment->shop_item_product_id);
            event(new PaymentEvent($payment, $payment, $shopProduct));

            if ($response->json()['status'] == 'paid') {
                $user = User::findOrFail($payment->user_id);
                $payment->status = PaymentStatus::PAID;
                $payment->save();
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
