<?php

namespace App\Listeners;

use App\Enums\PaymentStatus;
use App\Events\PaymentEvent;
use App\Settings\InvoiceSettings;
use App\Traits\Invoiceable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;
use Throwable;

class CreateInvoice implements ShouldQueue
{
    use Invoiceable;

    private $invoice_enabled;
    private $invoice_settings;

    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct(InvoiceSettings $invoice_settings)
    {
        $this->invoice_enabled = $invoice_settings->enabled;
        $this->invoice_settings = $invoice_settings;
    }

    /**
     * Handle the event.
     *
     * @param  \App\Events\PaymentEvent  $event
     * @return void
     */
    public function handle(PaymentEvent $event)
    {
        if ($event->payment->status !== PaymentStatus::PAID) {
            return;
        }

        if ($this->invoice_enabled) {
            try {
                // create invoice using the trait
                $this->createInvoice($event->payment, $event->shopProduct, $this->invoice_settings);
            } catch (Throwable $e) {
                Log::error('Invoice creation failed after payment completion.', [
                    'payment_id' => $event->payment->id,
                    'user_id' => $event->user->id,
                    'error' => $e->getMessage(),
                    'exception' => get_class($e),
                ]);

                report($e);
            }
        }
    }
}
