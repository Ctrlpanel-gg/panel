<?php

namespace App\Classes\Settings;

use App\Models\Settings;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class Invoices
{
    public $tabTitle = 'Invoice Settings';
    public $invoiceSettings;

    public function __construct()
    {
        return;
    }


    public function updateInvoiceSettings(Request $request)
    {
        $request->validate([
            'logo' => 'nullable|max:10000|mimes:jpg,png,jpeg',
        ]);

        $values = [
            //SETTINGS::VALUE => REQUEST-VALUE (coming from the html-form)
            "SETTINGS::INVOICE:COMPANY_NAME" => "company-name",
            "SETTINGS::INVOICE:COMPANY_ADDRESS" => "company-address",
            "SETTINGS::INVOICE:COMPANY_PHONE" => "company-phone",
            "SETTINGS::INVOICE:COMPANY_MAIL" => "company-mail",
            "SETTINGS::INVOICE:COMPANY_VAT" => "company-vat",
            "SETTINGS::INVOICE:COMPANY_WEBSITE" => "company-web",
            "SETTINGS::INVOICE:PREFIX" => "invoice-prefix"
        ];

        foreach ($values as $key => $value) {
            $param = $request->get($value);
            if (!$param) {
                $param = "";
            }
            Settings::where('key', $key)->update(['value' => $param]);
            Cache::forget("setting" . ':' . $key);
        }


        if ($request->hasFile('logo')) {
            $request->file('logo')->storeAs('public', 'logo.png');
        }


        return redirect()->route('admin.settings.index')->with('success', 'Invoice settings updated!');
    }
}
