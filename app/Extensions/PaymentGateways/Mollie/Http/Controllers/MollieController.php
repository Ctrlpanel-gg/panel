<?php

namespace App\Extensions\PaymentGateways\Mollie\Http\Controllers;

use App\Enums\PaymentStatus;
use App\Extensions\PaymentGateways\Mollie\MollieExtension;
use App\Extensions\PaymentGateways\Mollie\MollieSettings;
use App\Models\Payment;
use App\Traits\HandlesGatewayPayments;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redirect;

class MollieController
{
    use HandlesGatewayPayments;

    public function success(Request $request): RedirectResponse
    {
        $payment = Payment::findOrFail($request->input('payment_id'));
        self::ensureAuthenticatedPaymentOwner($payment);

        if ($payment->status === PaymentStatus::PAID) {
            return Redirect::route('home')->with('success', 'Your payment has already been processed!');
        }

        self::setPaymentProcessing($payment->id);

        return Redirect::route('home')->with('success', 'Your payment is being processed');
    }

    public function webhook(Request $request): JsonResponse
    {
        $settings = new MollieSettings();
        $incomingWebhookToken = (string) $request->query('token', '');
        if (empty($settings->webhook_secret) || !hash_equals((string) $settings->webhook_secret, $incomingWebhookToken)) {
            Log::warning('Mollie webhook rejected due to invalid token.');
            return response()->json(['success' => false], 403);
        }

        $molliePaymentId = (string) $request->input('id', '');
        if (empty($molliePaymentId)) {
            return response()->json(['success' => false], 400);
        }

        $url = 'https://api.mollie.com/v2/payments/' . $molliePaymentId;

        try {
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer ' . $settings->api_key,
            ])->get($url);

            if (!$response->successful()) {
                Log::error('Mollie Payment Webhook: ' . $response->body());
                return response()->json(['success' => false], 502);
            }

            $status = $response->json('status');
            $paymentId = $response->json('metadata.payment_id');
            if (empty($paymentId)) {
                return response()->json(['success' => false], 422);
            }

            $payment = Payment::find($paymentId);
            if (!$payment || $payment->payment_method !== 'Mollie') {
                Log::warning('Mollie webhook payment lookup failed.', [
                    'payment_id' => $paymentId,
                    'mollie_payment_id' => $response->json('id'),
                ]);

                return response()->json(['success' => true], 200);
            }

            if (!MollieExtension::matchesMollieAmountAndCurrency(
                $payment,
                (string) $response->json('amount.value', ''),
                (string) $response->json('amount.currency', '')
            )) {
                Log::warning('Mollie webhook amount/currency mismatch.', [
                    'payment_id' => $payment->id,
                    'mollie_payment_id' => $response->json('id'),
                ]);

                self::setPaymentCanceled($payment->id, (string) $response->json('id'));
                return response()->json(['success' => true], 200);
            }

            if ($status === 'paid') {
                self::completePayment($payment->id, (string) $response->json('id'));
            } elseif (in_array($status, ['failed', 'expired', 'canceled'], true)) {
                self::setPaymentCanceled($payment->id, (string) $response->json('id'));
            } elseif (in_array($status, ['authorized', 'pending', 'open'], true)) {
                self::setPaymentProcessing($payment->id, (string) $response->json('id'));
            }
        } catch (Exception $ex) {
            Log::error('Mollie Payment Webhook: ' . $ex->getMessage());
            return response()->json(['success' => false], 500);
        }

        return response()->json(['success' => true], 200);
    }
}
