<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\invoiceSettings;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class SettingsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return Application|Factory|View|Response
     */
    public function index()
    {
        return view('admin.settings.index',
            [
                'company_name' => invoiceSettings::get()->first()->company_name,
                'company_adress' => invoiceSettings::get()->first()->company_adress,
                'company_phone' => invoiceSettings::get()->first()->company_phone,
                'company_vat' => invoiceSettings::get()->first()->company_vat,
                'company_mail' => invoiceSettings::get()->first()->company_mail,
                'company_web' => invoiceSettings::get()->first()->company_web
            ]);
    }

    public function updateIcons(Request $request)
    {
        $request->validate([
            'icon' => 'nullable|max:10000|mimes:jpg,png,jpeg',
            'favicon' => 'nullable|max:10000|mimes:ico',
        ]);

        if ($request->hasFile('icon')) {
            $request->file('icon')->storeAs('public', 'icon.png');
        }

        if ($request->hasFile('favicon')) {
            $request->file('favicon')->storeAs('public', 'favicon.ico');
        }

        return redirect()->route('admin.settings.index')->with('success', 'Icons updated!');
    }

    public function updateInvoiceSettings(Request $request)
    {
        $request->validate([
            'logo' => 'nullable|max:10000|mimes:jpg,png,jpeg',
        ]);

        invoiceSettings::updateOrCreate(['id' => "1"], ['company_name' => $request->get('company-name')]);
        invoiceSettings::updateOrCreate(['id' => "1",], ['company_adress' => $request->get('company-adress')]);
        invoiceSettings::updateOrCreate(['id' => "1",], ['company_phone' => $request->get('company-phone')]);
        invoiceSettings::updateOrCreate(['id' => "1",], ['company_mail' => $request->get('company-mail')]);
        invoiceSettings::updateOrCreate(['id' => "1",], ['company_vat' => $request->get('company-vat')]);
        invoiceSettings::updateOrCreate(['id' => "1",], ['company_web' => $request->get('company-web')]);

        if ($request->hasFile('logo')) {
            $request->file('logo')->storeAs('public', 'logo.png');
        }


        return redirect()->route('admin.settings.index')->with('success', 'Invoice settings updated!');
    }

}
