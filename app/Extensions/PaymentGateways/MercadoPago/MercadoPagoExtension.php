<?php

namespace App\Extensions\PaymentGateways\MercadoPago;

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
 * Summary of MercadoPagoExtension
 */
class MercadoPagoExtension extends PaymentExtension
{
    public static function getConfig(): array
    {
        return [
            "name" => "MercadoPago",
            "RoutesIgnoreCsrf" => [
                "payment/MercadoPagoWebhook"
            ],
        ];
    }

    public static function getRedirectUrl(Payment $payment, ShopProduct $shopProduct, int $totalPrice): string
    {
        /**
         * For Mercado Pago to work correctly,
         * it is necessary to use SSL and the app.url must start with "https://",
         * this is necessary so that the webhook receives the information and not an error.
         */
        if (!str_contains(config('app.url'), 'https://')) {
            throw new Exception(__('It is not possible to purchase via MercadoPago: APP_URL does not have HTTPS, required by Mercado Pago.'));
        }

        // Converts from cents to decimal places.
        $totalPrice = $totalPrice / 1000;

        $user = Auth::user();
        $user = User::findOrFail($user->id);
        $url = 'https://api.mercadopago.com/checkout/preferences';
        $settings = new MercadoPagoSettings();
        try {
            $response =  Http::withHeaders([
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer ' . $settings->access_token,
            ])->timeout(30)->connectTimeout(10)->post($url, [
                'back_urls' => [
                    'success' => URL::temporarySignedRoute('payment.MercadoPagoSuccess', now()->addDay(), ['payment' => $payment->id]),
                    'failure' => route('payment.Cancel'),
                    'pending' => URL::temporarySignedRoute('payment.MercadoPagoPending', now()->addDay(), ['payment' => $payment->id]),
                ],
                'auto_return' => 'approved',
                'notification_url' => route('payment.MercadoPagoWebhook'),
                'payer' => [
                    'email' => $user->email,
                ],
                'items' => [
                    [
                        'title' => "Order #{$payment->id} - " . $shopProduct->display,
                        'quantity' => 1,
                        'unit_price' => $totalPrice,
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
                Log::error('MercadoPago payment preference creation failed.', [
                    'status' => $response->status(),
                    'error' => $response->json('message') ?: $response->json('error') ?: 'Unknown MercadoPago error',
                ]);
                throw new Exception('Payment failed');
            }
        } catch (Exception $ex) {
            Log::error('MercadoPago Payment: ' . $ex->getMessage());
            throw new Exception('Payment failed');
        }
    }

    public static function Success(Request $request): RedirectResponse
    {
        return self::handleCheckoutReturn($request, false);
    }

    public static function Pending(Request $request): RedirectResponse
    {
        return self::handleCheckoutReturn($request, true);
    }

    public static function Webhook(Request $request): JsonResponse
    {
        $topic = $request->input('topic');
        $action = $request->input('action');

        try {
            $paymentIds = [];
            $notificationId = (string) data_get($request->all(), 'data.id', '');

            if ($topic === 'merchant_order' && $notificationId !== '') {
                $paymentIds = self::fetchMerchantOrderPaymentIds($notificationId);
            } elseif ($topic === 'payment' && $notificationId !== '') {
                $paymentIds = [$notificationId];
            } elseif ($action && str_contains($action, 'payment') && $notificationId !== '') {
                $paymentIds = [$notificationId];
            }

            if ($paymentIds === []) {
                return response()->json(['success' => false], 400);
            }

            foreach (array_unique($paymentIds) as $paymentId) {
                $mercado = self::fetchMercadoPagoPayment((string) $paymentId);
                $payment = Payment::findOrFail($mercado['metadata']['crtl_panel_payment_id']);
                if ($payment->payment_method !== 'MercadoPago') {
                    abort(403);
                }

                $user = User::findOrFail($payment->user_id);
                $shopProduct = ShopProduct::findOrFail($payment->shop_item_product_id);
                self::syncMercadoPagoPaymentState($payment, $mercado, $user, $shopProduct);
            }
        } catch (Exception $ex) {
            Log::error('MercadoPago Webhook(IPN) Payment: ' . $ex->getMessage());
            return response()->json(['success' => false]);
        }
        return response()->json(['success' => true]);
    }

    private static function assertMercadoPagoPaymentMatches(Payment $payment, array $mercadoPayment): void
    {
        $expectedAmount = number_format($payment->total_price / 1000, 2, '.', '');
        $actualAmount = number_format((float) ($mercadoPayment['transaction_amount'] ?? 0), 2, '.', '');
        $expectedCurrency = strtoupper($payment->currency_code);
        $actualCurrency = strtoupper((string) ($mercadoPayment['currency_id'] ?? ''));
        $metadataPaymentId = (string) ($mercadoPayment['metadata']['crtl_panel_payment_id'] ?? '');
        $externalReference = (string) ($mercadoPayment['external_reference'] ?? '');
        $metadataUserId = (string) ($mercadoPayment['metadata']['user_id'] ?? '');

        if ($metadataPaymentId !== (string) $payment->id
            || ($externalReference !== '' && $externalReference !== (string) $payment->id)
            || ($metadataUserId !== '' && $metadataUserId !== (string) $payment->user_id)
            || $actualCurrency !== $expectedCurrency
            || $actualAmount !== $expectedAmount) {
            Log::critical('MercadoPago payment amount mismatch detected', [
                'payment_id' => $payment->id,
                'expected_amount' => $expectedAmount,
                'received_amount' => $actualAmount,
                'expected_currency' => $expectedCurrency,
                'received_currency' => $actualCurrency,
                'expected_user_id' => $payment->user_id,
                'received_user_id' => $metadataUserId,
            ]);

            throw new Exception('MercadoPago payment verification failed.');
        }
    }

    private static function handleCheckoutReturn(Request $request, bool $pendingFlow): RedirectResponse
    {
        if (! $request->hasValidSignatureWhileIgnoring([
            'collection_id',
            'collection_status',
            'external_reference',
            'merchant_order_id',
            'payment_id',
            'preference_id',
            'site_id',
            'status',
        ])) {
            abort(403);
        }

        $payment = Payment::findOrFail($request->input('payment'));
        if ($payment->payment_method !== 'MercadoPago') {
            abort(403);
        }

        if ($payment->status === PaymentStatus::PAID) {
            return Redirect::route(self::getCallbackRedirectRoute())->with('success', 'Your payment has already been processed!');
        }

        $paymentOwner = User::findOrFail($payment->user_id);
        $shopProduct = ShopProduct::findOrFail($payment->shop_item_product_id);
        $remotePaymentId = (string) ($request->input('payment_id') ?: $request->input('collection_id') ?: '');

        try {
            if ($remotePaymentId !== '') {
                $mercado = self::fetchMercadoPagoPayment($remotePaymentId);
                $state = self::syncMercadoPagoPaymentState($payment, $mercado, $paymentOwner, $shopProduct);

                return match ($state) {
                    'paid' => Redirect::route(self::getCallbackRedirectRoute())->with('success', 'Payment successful'),
                    'canceled' => Redirect::route(self::getCallbackRedirectRoute())->with('info', __('Your payment has been canceled!')),
                    default => Redirect::route(self::getCallbackRedirectRoute())->with('success', 'Your payment is being processed!'),
                };
            }
        } catch (Exception $ex) {
            Log::error('MercadoPago success callback failed.', [
                'payment_id' => $payment->id,
                'message' => $ex->getMessage(),
            ]);
        }

        if ($pendingFlow || in_array((string) $request->input('status'), ['pending', 'in_process'], true)) {
            Payment::whereKey($payment->id)
                ->where('status', '!=', PaymentStatus::PAID->value)
                ->update(['status' => PaymentStatus::PROCESSING->value]);

            return Redirect::route(self::getCallbackRedirectRoute())->with('success', 'Your payment is being processed!');
        }

        return Redirect::route(self::getCallbackRedirectRoute())->with(
            'error',
            __('We could not confirm your Mercado Pago payment. If you were charged, please contact support.')
        );
    }

    private static function fetchMercadoPagoPayment(string $notification): array
    {
        $settings = new MercadoPagoSettings();
        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer ' . $settings->access_token,
        ])->timeout(30)->connectTimeout(10)->get("https://api.mercadopago.com/v1/payments/{$notification}");

        if (! $response->successful()) {
            Log::error('MercadoPago payment lookup failed.', [
                'status' => $response->status(),
                'error' => $response->json('message') ?: $response->json('error') ?: 'Unknown MercadoPago error',
            ]);
            throw new Exception('MercadoPago payment lookup failed.');
        }

        return $response->json();
    }

    private static function fetchMerchantOrderPaymentIds(string $merchantOrderId): array
    {
        $settings = new MercadoPagoSettings();
        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer ' . $settings->access_token,
        ])->timeout(30)->connectTimeout(10)->get("https://api.mercadopago.com/merchant_orders/{$merchantOrderId}");

        if (! $response->successful()) {
            Log::error('MercadoPago merchant order lookup failed.', [
                'status' => $response->status(),
                'error' => $response->json('message') ?: $response->json('error') ?: 'Unknown MercadoPago error',
            ]);
            throw new Exception('MercadoPago merchant order lookup failed.');
        }

        return collect($response->json('payments', []))
            ->pluck('id')
            ->filter()
            ->map(fn ($id) => (string) $id)
            ->values()
            ->all();
    }

    private static function syncMercadoPagoPaymentState(Payment $payment, array $mercado, User $user, ShopProduct $shopProduct): string
    {
        $status = (string) ($mercado['status'] ?? '');
        self::assertMercadoPagoPaymentMatches($payment, $mercado);

        if ($status === 'approved') {
            $updated = Payment::whereKey($payment->id)
                ->where('status', '!=', PaymentStatus::PAID->value)
                ->update([
                    'payment_id' => (string) ($mercado['id'] ?? $payment->payment_id),
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

        if (in_array($status, ['cancelled', 'rejected', 'refunded', 'charged_back'], true)) {
            $updated = Payment::whereKey($payment->id)
                ->where('status', '!=', PaymentStatus::PAID->value)
                ->update([
                    'payment_id' => (string) ($mercado['id'] ?? $payment->payment_id),
                    'status' => PaymentStatus::CANCELED->value,
                ]);

            if ($updated > 0) {
                event(new PaymentEvent($user, $payment->fresh(), $shopProduct));
            }

            return 'canceled';
        }

        $updated = Payment::whereKey($payment->id)
            ->where('status', '!=', PaymentStatus::PAID->value)
            ->update([
                'payment_id' => (string) ($mercado['id'] ?? $payment->payment_id),
                'status' => PaymentStatus::PROCESSING->value,
            ]);

        if ($updated > 0) {
            event(new PaymentEvent($user, $payment->fresh(), $shopProduct));
        }

        return 'processing';
    }

    private static function getCallbackRedirectRoute(): string
    {
        return Auth::check() ? 'home' : 'login';
    }
}
