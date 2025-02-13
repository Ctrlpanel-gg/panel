<?php

namespace App\Listeners;

use App\Events\PaymentEvent;
use App\Settings\InvoiceSettings;
use App\Traits\Invoiceable;

class CreateInvoice
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
        if ($this->invoice_enabled) {
            // create invoice using the trait
            $this->createInvoice($event->payment, $event->shopProduct, $this->invoice_settings);
        }
    }
}
