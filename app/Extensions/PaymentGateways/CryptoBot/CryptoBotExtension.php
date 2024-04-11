<?php

namespace App\Extensions\PaymentGateways\CryptoBot;

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
use App\Notifications\ConfirmPaymentNotification;

/**
 * Summary of PayPalExtension
 */
class CryptoBotExtension extends AbstractExtension
{
    use CouponTrait;

    public static function getConfig(): array
    {
        return [
            "name" => "CryptoBot",
            "RoutesIgnoreCsrf" => [
                "payment/CryptoBotWebhook"
            ],
        ];
    }

    public static function getRedirectUrl(Payment $payment, ShopProduct $shopProduct, string $totalPriceString): string
    {
        $url = 'https://pay.crypt.bot/api/createInvoice';
        $settings = new CryptoBotSettings();
        try {
            $response = Http::withHeaders([
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
                'Crypto-Pay-API-Token' => $settings->api_key
            ])->post($url, [
                'amount' => $totalPriceString,
                'payload' => strval($payment->id),
                'description' => "Заказ #{$payment->id} - " . $shopProduct->name,
                'currency_type' => 'fiat',
                'fiat' => $shopProduct->currency_code,
                'hidden_message' => 'Спасибо за перевод 💖',
                'paid_btn_name' => 'callback',
                'paid_btn_url' => route('payment.CryptoBotSuccess').'?payment=YGqvOf4I'
            ]);

            if($response->json('ok') == false){
                return $response->json('error.name');
            }

            return $response->json('result.pay_url');
        } catch (Exception $ex) {
            Log::error('CryptoBot Payment: ' . $ex->getMessage());
            throw new Exception('Payment failed');
        }
    }

    static function success(Request $request): void
    {
        Redirect::route('home')->with('success', 'Ваш платёж в обработке 💖')->send();
        return;
    }

    static function webhook(Request $request): JsonResponse
    {

        $settings = new CryptoBotSettings();
        /***    Проверка подписи     ***/
        $sign_header = $request->header('crypto-pay-api-signature');

        $secret_key = hash('sha256', $settings->api_key, true);
        $calculated_signature = hash_hmac('sha256', $request->getContent(), $secret_key);

        if (!hash_equals($sign_header, $calculated_signature)) {
            return response()->json(['status' => 'invalid sign.']);
        } 
        /***                         ***/

        $payment = Payment::findOrFail($request->json('payload.payload'));
        $shopProduct = ShopProduct::findOrFail($payment->shop_item_product_id);
        $user = User::findOrFail($payment->user_id);

        //update payment
        $payment->update([
            'status' => PaymentStatus::PAID,
            'payment_id' => $request->input('payload.invoice_id'),
        ]);
        try {
            $user->increment('credits', $payment->amount);
        } catch (Exception $exception) {
            throw $exception;
        }


        event(new PaymentEvent($user, $payment, $shopProduct));
        event(new UserUpdateCreditsEvent($user));
        $user->notify(new ConfirmPaymentNotification($payment));

        // return a 200 status code
        return response()->json(['success' => true]);
    }
}
