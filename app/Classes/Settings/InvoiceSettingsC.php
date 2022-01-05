<?php

namespace App\Classes\Settings;

use App\Models\Settings;
use Illuminate\Http\Request;

class InvoiceSettingsC
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

        $name = Settings::find("SETTINGS::INVOICE:COMPANY_NAME");
        $address = Settings::find("SETTINGS::INVOICE:COMPANY_ADDRESS");
        $phone = Settings::find("SETTINGS::INVOICE:COMPANY_PHONE");
        $mail = Settings::find("SETTINGS::INVOICE:COMPANY_MAIL");
        $vat = Settings::find("SETTINGS::INVOICE:COMPANY_VAT");
        $web = Settings::find("SETTINGS::INVOICE:COMPANY_WEBSITE");
        $prefix = Settings::find("SETTINGS::INVOICE:PREFIX");

        $name->value=$request->get('company-name');
        $address->value=$request->get('company-address');
        $phone->value=$request->get('company-phone');
        $mail->value=$request->get('company-mail');
        $vat->value=$request->get('company-vat');
        $web->value=$request->get('company-web');
        $prefix->value=$request->get('invoice-prefix');

        $name->save();
        $address->save();
        $phone->save();
        $mail->save();
        $vat->save();
        $web->save();
        $prefix->save();


        if ($request->hasFile('logo')) {
            $request->file('logo')->storeAs('public', 'logo.png');
        }


        return redirect()->route('admin.settings.index')->with('success', 'Invoice settings updated!');
    }

}
