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

        $user->notify(new ConfirmPaymentNotification($payment));
        event(new PaymentEvent($user, $payment, $shopProduct));
        event(new UserUpdateCreditsEvent($user));

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
