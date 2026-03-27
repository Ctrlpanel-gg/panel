<?php

namespace App\Traits;

use App\Helpers\CurrencyHelper;
use App\Models\PartnerDiscount;
use App\Models\Payment;
use App\Models\ShopProduct;
use App\Models\Invoice;
use App\Notifications\InvoiceNotification;
use App\Settings\InvoiceSettings;
use App\Support\HtmlSanitizer;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use LaravelDaily\Invoices\Classes\Buyer;
use LaravelDaily\Invoices\Classes\InvoiceItem;
use LaravelDaily\Invoices\Classes\Party;
use LaravelDaily\Invoices\Invoice as DailyInvoice;
use Symfony\Component\Intl\Currencies;

trait Invoiceable
{
    public function createInvoice(Payment $payment, ShopProduct $shopProduct, InvoiceSettings $invoice_settings)
    {
        $user = $payment->user;
        $logoPath = storage_path('app/public/logo.png');
        $invoiceRecord = Invoice::create([
            'invoice_user' => $user->id,
            'invoice_name' => 'pending-' . now()->format('YmdHisu') . '-' . $payment->payment_id,
            'payment_id' => $payment->payment_id,
        ]);
        $invoiceSerialNumber = sprintf('%s-%s%d', $invoice_settings->prefix, now()->format('mY'), $invoiceRecord->id);
        $invoiceRecord->update(['invoice_name' => $invoiceSerialNumber]);

        $seller = new Party([
            'name' => $invoice_settings->company_name,
            'phone' => $invoice_settings->company_phone,
            'address' => $invoice_settings->company_address,
            'vat' => $invoice_settings->company_vat,
            'custom_fields' => [
                'E-Mail' => $invoice_settings->company_mail,
                "Web" => $invoice_settings->company_website
            ],
        ]);

        $customer = new Buyer([
            'name' => $user->name,
            'custom_fields' => [
                'E-Mail' => $user->email,
                'Client ID' => $user->id,
            ],
        ]);
        $item = (new InvoiceItem())
            ->title($shopProduct->description)
            ->pricePerUnit(($shopProduct->price/1000));

        $notes = [
            __("Payment method") . ": " . $payment->payment_method,
            HtmlSanitizer::sanitizeRichText($invoice_settings->additional_notes) ?: "",
        ];
        $notes = implode("<br>", $notes);

        $invoice = DailyInvoice::make()
            ->template('ctrlpanel')
            ->name(__("Invoice"))
            ->buyer($customer)
            ->seller($seller)
            ->discountByPercent(PartnerDiscount::getDiscount())
            ->taxRate(floatval($shopProduct->getTaxPercent()))
            ->shipping(0)
            ->addItem($item)
            ->status(__($payment->status->value))
            ->series(now()->format('mY'))
            ->delimiter("-")
            ->sequence($invoiceRecord->id)
            ->serialNumberFormat($invoice_settings->prefix . '{DELIMITER}{SERIES}{SEQUENCE}')
            ->currencyCode(strtoupper($payment->currency_code))
            ->currencySymbol(Currencies::getSymbol(strtoupper($payment->currency_code)))
            ->notes("<br/>".$notes);

        if (file_exists($logoPath)) {
            $invoice->logo($logoPath);
        }

        //Save the invoice in "storage\app\invoice\USER_ID\YEAR"
        try {
            $invoice->filename = $invoiceSerialNumber . '.pdf';
            $invoice->render();
            Storage::disk("local")->put("invoice/" . $user->id . "/" . now()->format('Y') . "/" . $invoice->filename, $invoice->output);
        } catch (\Throwable $exception) {
            $invoiceRecord->delete();

            throw $exception;
        }

        //Send Invoice per Mail
        $user->notify(new InvoiceNotification($invoice->filename, $user, $payment));
    }
}
