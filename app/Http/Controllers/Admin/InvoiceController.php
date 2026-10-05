<?php

namespace App\Http\Controllers\Admin;

use App\Exceptions\Payment\InvoiceException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Http\Controllers\Controller;
use App\Models\Invoice;
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
            throw new InvoiceException('Failed to create invoice archive.');
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
            throw new InvoiceException('Error while adding files to zip', 500, $e);
        }

        return response()->download($zip_save_path)->deleteFileAfterSend(true);
    }

    public function downloadSingleInvoice(Request $request)
    {
        $id = $request->input('id');
        try {
            $invoice = Invoice::where('payment_id', '=', $id)->firstOrFail();
        } catch (ModelNotFoundException $e) {
            throw new InvoiceException('Error finding invoice', 404, $e);
        }

        $filePath = storage_path('app/invoice/' . $invoice->invoice_user . '/' . $invoice->created_at->format('Y') . '/' . $invoice->invoice_name . '.pdf');

        if (!file_exists($filePath)) {
            throw new InvoiceException('Invoice file not found', 404);
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
        foreach (glob(dirname($pattern) . '/*', GLOB_ONLYDIR | GLOB_NOSORT) as $dir) {
            $files = array_merge($files, $this->rglob($dir . '/' . basename($pattern), $flags));
        }

        return $files;
    }
}
