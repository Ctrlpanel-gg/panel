<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\InvoiceSettings;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use ZipArchive;

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
                'company_name' => InvoiceSettings::get()->first()->company_name,
                'company_adress' => InvoiceSettings::get()->first()->company_adress,
                'company_phone' => InvoiceSettings::get()->first()->company_phone,
                'company_vat' => InvoiceSettings::get()->first()->company_vat,
                'company_mail' => InvoiceSettings::get()->first()->company_mail,
                'company_web' => InvoiceSettings::get()->first()->company_web,
                'invoice_prefix' => InvoiceSettings::get()->first()->invoice_prefix
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

        InvoiceSettings::updateOrCreate(['id' => "1"], ['company_name' => $request->get('company-name')]);
        InvoiceSettings::updateOrCreate(['id' => "1",], ['company_adress' => $request->get('company-adress')]);
        InvoiceSettings::updateOrCreate(['id' => "1",], ['company_phone' => $request->get('company-phone')]);
        InvoiceSettings::updateOrCreate(['id' => "1",], ['company_mail' => $request->get('company-mail')]);
        InvoiceSettings::updateOrCreate(['id' => "1",], ['company_vat' => $request->get('company-vat')]);
        InvoiceSettings::updateOrCreate(['id' => "1",], ['company_web' => $request->get('company-web')]);
        InvoiceSettings::updateOrCreate(['id' => "1",], ['invoice_prefix' => $request->get('invoice-prefix')]);

        if ($request->hasFile('logo')) {
            $request->file('logo')->storeAs('public', 'logo.png');
        }


        return redirect()->route('admin.settings.index')->with('success', 'Invoice settings updated!');
    }

    public function rglob($pattern, $flags = 0) {
        $files = glob($pattern, $flags);
        foreach (glob(dirname($pattern).'/*', GLOB_ONLYDIR|GLOB_NOSORT) as $dir) {
            $files = array_merge($files, $this::rglob($dir.'/'.basename($pattern), $flags));
        }
        return $files;
    }

    public function downloadAllInvoices(){
        $zip = new ZipArchive;
        $zip_safe_path = storage_path('invoices.zip');
        $res = $zip->open($zip_safe_path, ZipArchive::CREATE|ZipArchive::OVERWRITE);
        $result = $this::rglob(storage_path('app/invoice/*'));
        if ($res === TRUE) {
            foreach($result as $file){
                if (file_exists($file) && is_file($file)) {
                    $zip->addFromString("1. Info.txt","This Archive contains all Invoices from all Users!");
                    $zip->addFile($file,basename($file));
                }
            }
            $zip->close();
        }
        if (file_exists($zip_safe_path) && is_file($zip_safe_path)) {
            return response()->download($zip_safe_path);
        }else{
            $this->index()->with('failure', 'No Invoices in Storage!');
        }
    }

}
