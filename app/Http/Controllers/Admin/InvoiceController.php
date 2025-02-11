<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use ZipArchive;
use Throwable;
use Illuminate\Http\Request;

class InvoiceController extends Controller
{
    public function downloadAllInvoices()
    {
        $zip = new ZipArchive;
        $zip_save_path = storage_path('invoices.zip');

        if ($zip->open($zip_save_path, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            Log::error("Failed to create zip archive at path: " . $zipPath);
            return response()->json(['message' => 'Failed to create zip archive'], 500);
        }

        try {
            $allInvoices = $this->rglob(storage_path('app/invoice/*'));
            $invoiceFiles = Storage::disk('local')->files('invoice');
            $zip->addFromString('1. Info.txt', __('Created at') . ' ' . now()->format('d.m.Y'));

            foreach ($allInvoices as $file) {
                if (file_exists($file) && is_file($file)) {
                    $zip->addFile($file, basename($file));
                }
            }

            $zip->close();

        } catch (Throwable $e) {
            Log::error("Error while adding files to zip: " . $e->getMessage());
            return response()->json(['message' => 'Failed to add files to zip'], 500);
        }

        return response()->download($zip_save_path)->deleteFileAfterSend(true);
    }

    public function downloadSingleInvoice(Request $request)
    {
        $id = $request->input('id');
        try {
            $invoice = Invoice::where('payment_id', '=', $id)->firstOrFail();
        } catch (Throwable $e) {
            Log::error("Error finding invoice: " . $e->getMessage());
            return redirect()->back()->withErrors(['message' => __('An unexpected error occurred. Please check the logs!')]);
        }

        $filePath = storage_path('app/invoice/' . $invoice->invoice_user . '/' . $invoice->created_at->format('Y') . '/' . $invoice->invoice_name . '.pdf');

        if (!file_exists($filePath)) {
            Log::error("Invoice file not found: " . $filePath);
            return redirect()->back()->withErrors(['message' => __('Invoice does not exist on filesystem!')]);
        }

        return response()->download($filePath);
    }

    /**
     * @param $pattern
     * @param $flags
     * @return array|false
     */
    public function rglob($pattern, $flags = 0)
    {
        $files = glob($pattern, $flags);
        foreach (glob(dirname($pattern).'/*', GLOB_ONLYDIR | GLOB_NOSORT) as $dir) {
            $files = array_merge($files, $this->rglob($dir.'/'.basename($pattern), $flags));
        }

        return $files;
    }
}
