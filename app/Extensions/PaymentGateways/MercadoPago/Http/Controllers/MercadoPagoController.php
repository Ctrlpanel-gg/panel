<?php

namespace App\Extensions\PaymentGateways\MercadoPago\Http\Controllers;

use App\Enums\PaymentStatus;
use App\Extensions\PaymentGateways\MercadoPago\MercadoPagoExtension;
use App\Extensions\PaymentGateways\MercadoPago\MercadoPagoSettings;
use App\Models\Payment;
use App\Traits\HandlesGatewayPayments;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redirect;

class MercadoPagoController
{
    use HandlesGatewayPayments;

    public function success(Request $request): RedirectResponse
    {
        $payment = Payment::findOrFail($request->input('external_reference'));

        self::ensureAuthenticatedPaymentOwner($payment);

        // In some cases, the webhook is received even before the success route.
        if ($payment->status === PaymentStatus::PAID) {
            return Redirect::route('home')->with('success', 'Your payment has already been processed!');
        }

        self::setPaymentProcessing($payment->id);

        return Redirect::route('home')->with('success', 'Your payment is being processed!');
    }

    public function webhook(Request $request): JsonResponse
    {
        $settings = new MercadoPagoSettings();
        $xSignature = (string) $request->header('x-signature', '');
        $xRequestId = (string) $request->header('x-request-id', '');

        // Validate webhook signature per Mercado Pago documentation
        if (!MercadoPagoExtension::verifyMercadoPagoWebhookSignature($request, $xSignature, $xRequestId, $settings->webhook_secret)) {
            Log::warning('MercadoPago webhook signature verification failed.', [
                'x-signature_present' => $xSignature !== '',
                'x-request-id_present' => $xRequestId !== '',
            ]);
            return response()->json(['success' => false], 403);
        }

        $topic = (string) $request->input('topic', '');
        $action = (string) $request->input('action', '');
        $notificationId = $request->input('data.id', $request->input('id'));

        /**
         * Mercado Pago sends several requests for information in the webhook,
         *  but most are for other types of API, and that is why it is filtered here.
         */
        if (!empty($action) && !str_contains($action, 'payment')) {
            return response()->json(['success' => true]);
        }

        if (!empty($topic) && !in_array($topic, ['payment', 'merchant_order'], true)) {
            return response()->json(['success' => true]);
        }

        if (empty($notificationId)) {
            Log::warning('MercadoPago webhook missing notification id.', [
                'topic' => $topic,
                'action' => $action,
            ]);
            return response()->json(['success' => false], 400);
        }

        try {
            // Mercado pago test API for webhook request validation.
            if ((string) $notificationId === '123456') {
                return response()->json(['success' => true], 200);
            }

            $url = 'https://api.mercadopago.com/v1/payments/' . $notificationId;
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer ' . $settings->access_token,
            ])->get($url);

            if (!$response->successful()) {
                Log::error('MercadoPago webhook fetch failed.', [
                    'notification_id' => $notificationId,
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
                return response()->json(['success' => false], 502);
            }

            $mercado = $response->json();
            $status = $mercado['status'] ?? null;
            $ctrlPanelPaymentId = $mercado['metadata']['ctrl_panel_payment_id']
                ?? $mercado['metadata']['crtl_panel_payment_id']
                ?? $mercado['external_reference']
                ?? null;
            if (empty($ctrlPanelPaymentId)) {
                return response()->json(['success' => false], 422);
            }

            $payment = Payment::find($ctrlPanelPaymentId);
            if (!$payment || $payment->payment_method !== 'MercadoPago') {
                Log::warning('MercadoPago webhook payment lookup failed.', [
                    'payment_id' => $ctrlPanelPaymentId,
                    'mercadopago_payment_id' => $mercado['id'] ?? null,
                    'payment_method' => $payment?->payment_method,
                ]);

                return response()->json(['success' => true], 200);
            }

            // Validate payment using external_reference and status
            // MercadoPago converts to local currency, so we trust external_reference + status
            $externalRef = (string) ($mercado['external_reference'] ?? '');
            if ($externalRef !== $payment->id) {
                Log::warning('MercadoPago webhook external_reference mismatch.', [
                    'payment_id' => $payment->id,
                    'mercadopago_payment_id' => $mercado['id'] ?? null,
                    'external_reference' => $externalRef,
                ]);
                return response()->json(['success' => true], 200);
            }

            if ($status === 'approved') {
                self::completePayment($payment->id, (string) ($mercado['id'] ?? null));
            } elseif (in_array($status, ['cancelled', 'canceled', 'rejected', 'refunded', 'charged_back'], true)) {
                Log::warning('MercadoPago webhook canceled or failed payment.', [
                    'payment_id' => $payment->id,
                    'mercadopago_payment_id' => $mercado['id'] ?? null,
                    'status' => $status,
                ]);
                self::setPaymentCanceled($payment->id, (string) ($mercado['id'] ?? null));
            } else {
                self::setPaymentProcessing($payment->id, (string) ($mercado['id'] ?? null));
            }
        } catch (Exception $ex) {
            Log::error('MercadoPago Webhook(IPN) Payment failed.', [
                'error' => $ex->getMessage(),
                'topic' => $topic,
                'action' => $action,
                'notification_id' => $notificationId,
            ]);
            return response()->json(['success' => false], 500);
        }

        return response()->json(['success' => true], 200);
    }
}
