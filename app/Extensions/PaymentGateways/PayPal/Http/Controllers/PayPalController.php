<?php

namespace App\Extensions\PaymentGateways\PayPal\Http\Controllers;

use App\Enums\PaymentStatus;
use App\Extensions\PaymentGateways\PayPal\PayPalExtension;
use App\Models\Payment;
use App\Traits\HandlesGatewayPayments;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redirect;
use PayPalHttp\HttpException;

class PayPalController
{
    use HandlesGatewayPayments;

    public function success(Request $request): RedirectResponse
    {
        $payment = Payment::findOrFail($request->payment);
        self::ensureAuthenticatedPaymentOwner($payment);

        if ($payment->status === PaymentStatus::PAID) {
            return Redirect::route('home')->with('success', 'Your payment has already been processed!');
        }

        $orderId = (string) $request->input('token', '');
        if ($orderId === '') {
            return Redirect::route('home')->with('error', 'Missing PayPal order details.');
        }

        try {
            $order = PayPalExtension::getPayPalOrder($orderId);
            $resolvedPaymentId = PayPalExtension::extractPaymentIdFromOrder($order);
            if (empty($resolvedPaymentId) || $resolvedPaymentId !== $payment->id) {
                abort(403);
            }

            if (!PayPalExtension::isValidPayPalOrderAmount($payment, $order)) {
                self::setPaymentCanceled($payment->id, $orderId);

                return Redirect::route('home')->with('error', 'Unable to verify payment amount.');
            }

            self::setPaymentProcessing($payment->id, $orderId);

            // Best-effort capture fallback. Final crediting still happens only via verified webhook events.
            if (strtoupper((string) ($order->status ?? '')) === 'APPROVED') {
                PayPalExtension::capturePayPalOrder($orderId);
            }

            return Redirect::route('home')->with('success', 'Payment received. We are confirming it now.');
        } catch (HttpException $ex) {
            Log::error('PayPal payment capture failed', [
                'payment_id' => $payment->id,
                'error' => $ex->getMessage(),
                'status_code' => $ex->statusCode,
            ]);

            self::setPaymentProcessing($payment->id, $orderId);
            return Redirect::route('home')->with('info', 'Payment is pending confirmation. Please wait a moment and refresh.');
        } catch (Exception $ex) {
            Log::error('PayPal payment confirmation failed', [
                'payment_id' => $payment->id,
                'error' => $ex->getMessage(),
            ]);

            self::setPaymentProcessing($payment->id, $orderId);
            return Redirect::route('home')->with('info', 'Payment is pending confirmation. Please wait a moment and refresh.');
        }
    }

    public function webhook(Request $request): JsonResponse
    {
        $event = $request->json()->all();

        if (!is_array($event) || empty($event['event_type'])) {
            Log::warning('PayPal webhook missing event_type or invalid payload.', [
                'event_payload' => $event,
            ]);
            return response()->json(['success' => false], 400);
        }

        if (!PayPalExtension::verifyWebhookSignature($request, $event)) {
            Log::warning('PayPal webhook signature verification failed.', [
                'event_type' => $event['event_type'] ?? null,
            ]);
            return response()->json(['success' => false], 400);
        }

        try {
            $eventType = strtoupper((string) $event['event_type']);

            switch ($eventType) {
                case 'CHECKOUT.ORDER.APPROVED':
                    PayPalExtension::handleOrderApprovedWebhook($event);
                    break;
                case 'PAYMENT.CAPTURE.COMPLETED':
                    PayPalExtension::handleCaptureCompletedWebhook($event);
                    break;
                case 'PAYMENT.CAPTURE.PENDING':
                    PayPalExtension::handleCapturePendingWebhook($event);
                    break;
                case 'PAYMENT.CAPTURE.DENIED':
                case 'PAYMENT.CAPTURE.DECLINED':
                case 'PAYMENT.CAPTURE.REFUNDED':
                case 'PAYMENT.CAPTURE.REVERSED':
                    PayPalExtension::handleCaptureCanceledWebhook($event);
                    break;
                default:
                    break;
            }
        } catch (Exception $exception) {
            Log::error('PayPal webhook handling failed.', [
                'event_type' => $event['event_type'] ?? null,
                'event_id' => $event['id'] ?? null,
                'error' => $exception->getMessage(),
                'exception' => get_class($exception),
            ]);

            return response()->json(['success' => false], 500);
        }

        return response()->json(['success' => true], 200);
    }
}
