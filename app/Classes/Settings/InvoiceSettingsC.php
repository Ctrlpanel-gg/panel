<?php

namespace App\Classes\Settings;

use App\Models\InvoiceSettings;
use Illuminate\Http\Request;
use ZipArchive;

class InvoiceSettingsC
{
    public $tabTitle = 'Invoice Settings';
    public $invoiceSettings;

    public function __construct()
    {
        $this->invoiceSettings = InvoiceSettings::first();
    }


    public function updateInvoiceSettings(Request $request)
    {
        $request->validate([
            'logo' => 'nullable|max:10000|mimes:jpg,png,jpeg',
        ]);

        InvoiceSettings::updateOrCreate([
            'id' => "1"
        ], [
            'company_name' => $request->get('company-name'),
            'company_adress' => $request->get('company-address'),
            'company_phone' => $request->get('company-phone'),
            'company_mail' => $request->get('company-mail'),
            'company_vat' => $request->get('company-vat'),
            'company_web' => $request->get('company-web'),
            'invoice_prefix' => $request->get('invoice-prefix'),
        ]);

        if ($request->hasFile('logo')) {
            $request->file('logo')->storeAs('public', 'logo.png');
        }


        return redirect()->route('admin.settings.index')->with('success', 'Invoice settings updated!');
    }

}
