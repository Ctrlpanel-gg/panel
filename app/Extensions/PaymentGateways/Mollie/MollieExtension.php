<?php

namespace App\Extensions\PaymentGateways\Mollie;

use App\Classes\PaymentExtension;
use App\Enums\PaymentStatus;
use App\Events\PaymentEvent;
use App\Events\UserUpdateCreditsEvent;
use App\Models\Payment;
use App\Models\ShopProduct;
use App\Models\User;
use App\Notifications\ConfirmPaymentNotification;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\URL;

/**
 * Summary of PayPalExtension
 */
class MollieExtension extends PaymentExtension
{
    public static function getConfig(): array
    {
        return [
            "name" => "Mollie",
            "RoutesIgnoreCsrf" => [
                "payment/MollieWebhook"
            ],
        ];
    }

    public static function getRedirectUrl(Payment $payment, ShopProduct $shopProduct, int $totalPrice): string
    {
        /**
         * Mollie requires the price to be a string with two decimal places.
         * The price is in cents, so we need to divide by 10 to get the value in 100 factors.
         * The price is also in the format of 0.00, so we need to format it to two decimal places.
         */
        $priceCents = $totalPrice / 10;
        $totalPrice = number_format($priceCents / 100, 2, '.', '');

        $url = 'https://api.mollie.com/v2/payments';
        $settings = new MollieSettings();
        try {
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer ' . $settings->api_key,
            ])->timeout(30)->connectTimeout(10)->post($url, [
                'amount' => [
                    'currency' => $shopProduct->currency_code,
                    'value' => $totalPrice,
                ],
                'description' => "Order #{$payment->id} - " . $shopProduct->display,
                'redirectUrl' => URL::temporarySignedRoute('payment.MollieSuccess', now()->addDay(), ['payment' => $payment->id]),
                'cancelUrl' => route('payment.Cancel'),
                'webhookUrl' => route('payment.MollieWebhook'),
                'metadata' => [
                    'payment_id' => $payment->id,
                ],
            ]);

            if ($response->status() != 201) {
                Log::error('Mollie payment creation failed.', [
                    'status' => $response->status(),
                    'error' => $response->json('title') ?: $response->json('detail') ?: 'Unknown Mollie error',
                ]);
                throw new Exception('Payment failed');
            }

            $payment->forceFill([
                'payment_id' => (string) $response->json('id', $payment->payment_id),
            ])->save();

            return $response->json()['_links']['checkout']['href'];
        } catch (Exception $ex) {
            Log::error('Mollie Payment: ' . $ex->getMessage());
            throw new Exception('Payment failed');
        }
    }

    static function success(Request $request): RedirectResponse
    {
        if (! $request->hasValidSignature()) {
            abort(403);
        }

        $payment = Payment::findOrFail($request->input('payment'));
        if ($payment->payment_method !== 'Mollie') {
            abort(403);
        }

        if ($payment->status === PaymentStatus::PAID) {
            return Redirect::route(self::getCallbackRedirectRoute())->with('success', 'Your payment has already been processed!');
        }

        $paymentOwner = User::findOrFail($payment->user_id);
        $shopProduct = ShopProduct::findOrFail($payment->shop_item_product_id);

        try {
            if (! $payment->payment_id) {
                return Redirect::route(self::getCallbackRedirectRoute())->with('success', 'Your payment is being processed');
            }

            $remotePayment = self::fetchMolliePayment((string) $payment->payment_id);
            $state = self::syncMolliePaymentState($payment, $remotePayment, $paymentOwner, $shopProduct);

            return match ($state) {
                'paid' => Redirect::route(self::getCallbackRedirectRoute())->with('success', 'Payment successful'),
                'canceled' => Redirect::route(self::getCallbackRedirectRoute())->with('info', __('Your payment has been canceled!')),
                default => Redirect::route(self::getCallbackRedirectRoute())->with('success', 'Your payment is being processed'),
            };
        } catch (Exception $ex) {
            Log::error('Mollie success callback failed.', [
                'payment_id' => $payment->id,
                'message' => $ex->getMessage(),
            ]);

            return Redirect::route(self::getCallbackRedirectRoute())->with(
                'error',
                __('We could not confirm your Mollie payment. If you were charged, please contact support.')
            );
        }
    }

    static function webhook(Request $request): JsonResponse
    {
        try {
            $remotePayment = self::fetchMolliePayment((string) $request->id);
            $payment = Payment::findOrFail($remotePayment['metadata']['payment_id']);
            if ($payment->payment_method !== 'Mollie') {
                abort(403);
            }

            $user = User::findOrFail($payment->user_id);
            $shopProduct = ShopProduct::findOrFail($payment->shop_item_product_id);

            self::syncMolliePaymentState($payment, $remotePayment, $user, $shopProduct);
        } catch (Exception $ex) {
            Log::error('Mollie Payment Webhook: ' . $ex->getMessage());
            return response()->json(['success' => false]);
        }

        // return a 200 status code
        return response()->json(['success' => true]);
    }

    private static function assertMolliePaymentMatches(Payment $payment, array $remotePayment): void
    {
        $expectedAmount = number_format($payment->total_price / 1000, 2, '.', '');
        $actualAmount = number_format((float) ($remotePayment['amount']['value'] ?? 0), 2, '.', '');
        $expectedCurrency = strtoupper($payment->currency_code);
        $actualCurrency = strtoupper((string) ($remotePayment['amount']['currency'] ?? ''));

        if (($remotePayment['metadata']['payment_id'] ?? null) !== $payment->id
            || $actualCurrency !== $expectedCurrency
            || $actualAmount !== $expectedAmount) {
            Log::critical('Mollie payment amount mismatch detected', [
                'payment_id' => $payment->id,
                'expected_amount' => $expectedAmount,
                'received_amount' => $actualAmount,
                'expected_currency' => $expectedCurrency,
                'received_currency' => $actualCurrency,
            ]);

            throw new Exception('Mollie payment amount verification failed.');
        }
    }

    private static function fetchMolliePayment(string $remotePaymentId): array
    {
        $settings = new MollieSettings();
        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer ' . $settings->api_key,
        ])->timeout(30)->connectTimeout(10)->get('https://api.mollie.com/v2/payments/' . $remotePaymentId);

        if ($response->status() != 200) {
            Log::error('Mollie payment lookup failed.', [
                'status' => $response->status(),
                'error' => $response->json('title') ?: $response->json('detail') ?: 'Unknown Mollie error',
            ]);
            throw new Exception('Mollie payment lookup failed.');
        }

        return $response->json();
    }

    private static function syncMolliePaymentState(Payment $payment, array $remotePayment, User $user, ShopProduct $shopProduct): string
    {
        self::assertMolliePaymentMatches($payment, $remotePayment);

        $status = (string) ($remotePayment['status'] ?? '');

        if ($status === 'paid') {
            $updated = Payment::whereKey($payment->id)
                ->where('status', '!=', PaymentStatus::PAID->value)
                ->update([
                    'payment_id' => (string) ($remotePayment['id'] ?? $payment->payment_id),
                    'status' => PaymentStatus::PAID->value,
                ]);

            if ($updated > 0) {
                $payment = $payment->fresh();
                $user->notify(new ConfirmPaymentNotification($payment));
                event(new PaymentEvent($user, $payment, $shopProduct));
                event(new UserUpdateCreditsEvent($user));
            }

            return 'paid';
        }

        if (in_array($status, ['pending', 'authorized', 'processing'], true)) {
            Payment::whereKey($payment->id)
                ->where('status', '!=', PaymentStatus::PAID->value)
                ->update([
                    'payment_id' => (string) ($remotePayment['id'] ?? $payment->payment_id),
                    'status' => PaymentStatus::PROCESSING->value,
                ]);

            return 'processing';
        }

        if (in_array($status, ['failed', 'expired', 'canceled'], true)) {
            Payment::whereKey($payment->id)
                ->where('status', '!=', PaymentStatus::PAID->value)
                ->update([
                    'payment_id' => (string) ($remotePayment['id'] ?? $payment->payment_id),
                    'status' => PaymentStatus::CANCELED->value,
                ]);

            return 'canceled';
        }

        return 'processing';
    }

    private static function getCallbackRedirectRoute(): string
    {
        return Auth::check() ? 'home' : 'login';
    }
}
