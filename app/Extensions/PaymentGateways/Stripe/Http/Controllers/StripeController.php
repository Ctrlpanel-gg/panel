<?php

namespace App\Extensions\PaymentGateways\Stripe\Http\Controllers;

use App\Enums\PaymentStatus;
use App\Extensions\PaymentGateways\Stripe\StripeExtension;
use App\Models\Payment;
use App\Traits\HandlesGatewayPayments;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redirect;
use Stripe\Stripe;
use Throwable;

class StripeController
{
    use HandlesGatewayPayments;

    /**
     * @param  Request  $request
     */
    public function success(Request $request): RedirectResponse
    {
        $payment = Payment::findOrFail($request->input('payment'));
        $sessionId = $request->input('session_id');
        self::ensureAuthenticatedPaymentOwner($payment);

        if ($payment->status === PaymentStatus::PAID) {
            return Redirect::route('home')->with('success', 'Your payment has already been processed!');
        }

        if (empty($sessionId)) {
            Log::warning('StripeSuccess missing session id', [
                'payment_id' => $payment->id,
            ]);
            return Redirect::route('home')->with('error', 'Missing Stripe session details.');
        }

        $stripeClient = StripeExtension::getStripeClient();
        try {
            $paymentSession = $stripeClient->checkout->sessions->retrieve($sessionId);

            $sessionMetadataPaymentId = (string) ($paymentSession->metadata->payment_id ?? '');
            $paymentIntentId = isset($paymentSession->payment_intent)
                ? (string) $paymentSession->payment_intent
                : null;

            $intentMetadataPaymentId = '';
            if (!empty($paymentIntentId)) {
                $paymentIntent = $stripeClient->paymentIntents->retrieve($paymentIntentId);
                $intentMetadataPaymentId = (string) ($paymentIntent->metadata->payment_id ?? '');
            }

            $resolvedPaymentId = $sessionMetadataPaymentId !== ''
                ? $sessionMetadataPaymentId
                : $intentMetadataPaymentId;

            if ($resolvedPaymentId !== $payment->id) {
                Log::error('StripeSuccess payment id mismatch', [
                    'payment_id' => $payment->id,
                    'resolved_payment_id' => $resolvedPaymentId,
                    'session_id' => $sessionId,
                ]);
                throw new Exception('Stripe checkout session does not match payment.');
            }

            if ($paymentSession->status === 'complete') {
                self::setPaymentProcessing($payment->id, $paymentIntentId);

                return Redirect::route('home')->with('success', 'Payment received. We are confirming it now.');
            }

            if ($paymentSession->status === 'expired') {
                Log::warning('StripeSuccess session expired, canceling payment', [
                    'payment_id' => $payment->id,
                    'payment_intent_id' => $paymentIntentId,
                ]);
                self::setPaymentCanceled($payment->id, $paymentIntentId);

                return Redirect::route('home')->with('info', __('Your payment has been canceled!'));
            }

            self::setPaymentProcessing($payment->id, $paymentIntentId);

            return Redirect::route('home')->with('success', 'Payment received. We are confirming it now.');
        } catch (Throwable $e) {
            Log::error('Stripe success handler failed', [
                'payment_id' => $payment->id,
                'session_id' => $sessionId,
                'error' => $e->getMessage(),
                'exception' => get_class($e),
                'code' => $e->getCode(),
            ]);

            return Redirect::route('home')->with('error', 'Oops, something went wrong while confirming your payment.');
        }
    }

    /**
     * @param  Request  $request
     */
    public function webhook(Request $request): JsonResponse
    {
        Stripe::setApiKey(StripeExtension::getStripeSecret());

        $endpointSecrets = StripeExtension::getStripeEndpointSecrets();
        if (empty($endpointSecrets)) {
            Log::error('Stripe webhook secret is not configured.');
            return response()->json(['success' => false], 500);
        }

        $payload = $request->getContent();
        $sigHeader = (string) $request->header('Stripe-Signature', '');
        if ($sigHeader === '') {
            Log::warning('Stripe webhook signature header is missing.');
            return response()->json(['success' => false], 400);
        }

        $event = StripeExtension::verifyWebhookSignature($payload, $sigHeader, $endpointSecrets);
        if ($event === null) {
            return response()->json(['success' => false], 400);
        }

        StripeExtension::processWebhookEvent($event);

        return response()->json(['success' => true], 200);
    }
}
