<?php

namespace App\Http\Controllers\Admin\SettingsControllers;

use App\Http\Controllers\Controller;
use App\Models\InvoiceSettings;
use Illuminate\Http\Request;
use ZipArchive;

class InvoiceSettingsController extends Controller
{
    public $tabTitle = 'Invoice Settings';
    public $invoiceSettings;

    public function __construct()
    {
        $this->invoiceSettings = InvoiceSettings::first();
    }

    public function index()
    {
        return view('admin.settings.tabs.invoice', [
            'invoiceSettings' => $this->invoiceSettings,
        ]);
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

    public function downloadAllInvoices()
    {
        $zip = new ZipArchive;
        $zip_safe_path = storage_path('invoices.zip');
        $res = $zip->open($zip_safe_path, ZipArchive::CREATE | ZipArchive::OVERWRITE);
        $result = $this::rglob(storage_path('app/invoice/*'));
        if ($res === TRUE) {
            $zip->addFromString("1. Info.txt", "This Archive contains all Invoices from all Users!\nIf there are no Invoices here, no Invoices have ever been created!");
            foreach ($result as $file) {
                if (file_exists($file) && is_file($file)) {
                    $zip->addFile($file, basename($file));
                }
            }
            $zip->close();
        }
        return response()->download($zip_safe_path);
    }

    public function rglob($pattern, $flags = 0)
    {
        $files = glob($pattern, $flags);
        foreach (glob(dirname($pattern) . '/*', GLOB_ONLYDIR | GLOB_NOSORT) as $dir) {
            $files = array_merge($files, $this::rglob($dir . '/' . basename($pattern), $flags));
        }
        return $files;
    }
}
