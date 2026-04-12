<?php

namespace App\Traits;

use App\Enums\PaymentStatus;
use App\Events\PaymentEvent;
use App\Events\UserUpdateCreditsEvent;
use App\Models\Payment;
use App\Models\ShopProduct;
use App\Models\User;
use App\Notifications\ConfirmPaymentNotification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

trait HandlesGatewayPayments
{
    protected static function ensureAuthenticatedPaymentOwner(Payment $payment): void
    {
        $userId = Auth::id();

        if (!$userId || (int) $payment->user_id !== (int) $userId) {
            abort(403);
        }
    }

    protected static function setPaymentProcessing(string $paymentId, ?string $gatewayPaymentId = null): void
    {
        DB::transaction(function () use ($paymentId, $gatewayPaymentId) {
            $payment = Payment::query()->whereKey($paymentId)->lockForUpdate()->firstOrFail();

            if ($payment->status === PaymentStatus::PAID || $payment->status === PaymentStatus::CANCELED) {
                return;
            }

            if (!self::canAttachGatewayPaymentId($payment, $gatewayPaymentId)) {
                return;
            }

            if (!empty($gatewayPaymentId)) {
                $payment->payment_id = $gatewayPaymentId;
            }

            if ($payment->status !== PaymentStatus::PROCESSING || $payment->isDirty('payment_id')) {
                $payment->status = PaymentStatus::PROCESSING;
                $payment->save();
            }
        }, 5);
    }

    protected static function setPaymentCanceled(string $paymentId, ?string $gatewayPaymentId = null): void
    {
        DB::transaction(function () use ($paymentId, $gatewayPaymentId) {
            $payment = Payment::query()->whereKey($paymentId)->lockForUpdate()->firstOrFail();

            if ($payment->status === PaymentStatus::PAID || $payment->status === PaymentStatus::CANCELED) {
                return;
            }

            if (!self::canAttachGatewayPaymentId($payment, $gatewayPaymentId)) {
                return;
            }

            if (!empty($gatewayPaymentId)) {
                $payment->payment_id = $gatewayPaymentId;
            }

            if ($payment->status !== PaymentStatus::CANCELED || $payment->isDirty('payment_id')) {
                $payment->status = PaymentStatus::CANCELED;
                $payment->save();
            }
        }, 5);
    }

    protected static function completePayment(string $paymentId, ?string $gatewayPaymentId = null): bool
    {
        $paymentCompleted = false;

        DB::transaction(function () use ($paymentId, $gatewayPaymentId, &$paymentCompleted) {
            $payment = Payment::query()->whereKey($paymentId)->lockForUpdate()->firstOrFail();

            if ($payment->status === PaymentStatus::PAID || $payment->status === PaymentStatus::CANCELED) {
                return;
            }

            if (!self::canAttachGatewayPaymentId($payment, $gatewayPaymentId)) {
                return;
            }

            if (!empty($gatewayPaymentId)) {
                $payment->payment_id = $gatewayPaymentId;
            }

            $payment->status = PaymentStatus::PAID;
            $payment->save();
            $paymentCompleted = true;
        }, 5);

        if (!$paymentCompleted) {
            return false;
        }

        $payment = Payment::findOrFail($paymentId);
        $user = User::findOrFail($payment->user_id);
        $shopProduct = ShopProduct::findOrFail($payment->shop_item_product_id);

        $paymentEventDispatched = true;
        $userUpdateCreditsEventDispatched = true;

        try {
            $user->notify(new ConfirmPaymentNotification($payment));
        } catch (Throwable $e) {
            Log::error('Payment completion notification failed.', [
                'payment_id' => $payment->id,
                'user_id' => $user->id,
                'gateway_payment_id' => $payment->payment_id,
                'error' => $e->getMessage(),
                'exception' => get_class($e),
            ]);

            report($e);
        }

        try {
            event(new PaymentEvent($user, $payment, $shopProduct));
        } catch (Throwable $e) {
            $paymentEventDispatched = false;

            Log::error('Payment completion PaymentEvent dispatch failed.', [
                'payment_id' => $payment->id,
                'user_id' => $user->id,
                'gateway_payment_id' => $payment->payment_id,
                'error' => $e->getMessage(),
                'exception' => get_class($e),
            ]);

            report($e);
        }

        try {
            event(new UserUpdateCreditsEvent($user));
        } catch (Throwable $e) {
            $userUpdateCreditsEventDispatched = false;

            Log::error('Payment completion UserUpdateCreditsEvent dispatch failed.', [
                'payment_id' => $payment->id,
                'user_id' => $user->id,
                'gateway_payment_id' => $payment->payment_id,
                'error' => $e->getMessage(),
                'exception' => get_class($e),
            ]);

            report($e);
        }

        if (!$paymentEventDispatched || !$userUpdateCreditsEventDispatched) {
            Log::critical('Payment marked as paid with incomplete post-payment dispatch.', [
                'payment_id' => $payment->id,
                'user_id' => $user->id,
                'gateway_payment_id' => $payment->payment_id,
                'payment_event_dispatched' => $paymentEventDispatched,
                'user_update_credits_event_dispatched' => $userUpdateCreditsEventDispatched,
                'action_required' => 'Check queue health and reconcile payment side effects if needed.',
            ]);
        }

        return true;
    }

    protected static function canAttachGatewayPaymentId(Payment $payment, ?string $gatewayPaymentId): bool
    {
        if (empty($gatewayPaymentId)) {
            return true;
        }

        if (is_null($payment->payment_id) || $payment->payment_id === $gatewayPaymentId) {
            return true;
        }

        Log::warning('Payment gateway transaction ID mismatch detected.', [
            'payment_id' => $payment->id,
            'stored_gateway_payment_id' => $payment->payment_id,
            'incoming_gateway_payment_id' => $gatewayPaymentId,
        ]);

        return false;
    }
}
