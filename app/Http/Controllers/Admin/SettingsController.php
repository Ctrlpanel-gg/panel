<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Models\invoiceSettings;

class SettingsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return Application|Factory|View|Response
     */
    public function index()
    {
        return view('admin.settings.index');
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



        if($request->filled('company-name')) {
            invoiceSettings::updateOrCreate(['id'   => "1"],['company_name' => $request->get('company-name')]);
        }
        if($request->filled('company-adress')) {
            invoiceSettings::updateOrCreate(['id'   => "1",],['company_adress' => $request->get('company-adress')]);
        }
        if($request->filled('company-phone')) {
            invoiceSettings::updateOrCreate(['id'   => "1",],['company_phone' => $request->get('company-phone')]);
        }
        if($request->filled('company-vat')) {
            invoiceSettings::updateOrCreate(['id'   => "1",],['company_vat' => $request->get('company-vat')]);
        }
        if($request->filled('company-mail')) {
            invoiceSettings::updateOrCreate(['id'   => "1",],['company_mail' => $request->get('company-mail')]);
        }
        if($request->filled('company-web')) {
            invoiceSettings::updateOrCreate(['id'   => "1",],['company_web' => $request->get('company-web')]);
        }
        if ($request->hasFile('logo')) {
            $request->file('logo')->storeAs('public', 'logo.png');
        }



        return redirect()->route('admin.settings.index')->with('success', 'Invoice settings updated!');
    }

}
