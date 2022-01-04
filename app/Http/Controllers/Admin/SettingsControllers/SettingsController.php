<?php

namespace App\Http\Controllers\Admin\SettingsControllers;

use App\Http\Controllers\Controller;
use App\Models\InvoiceSettings;
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
        //Get all tabs as laravel view paths
        $tabs = [];
        foreach (glob(resource_path('views/admin/settings/tabs/*.blade.php')) as $filename) {
            $tabs[] = 'admin.settings.tabs.' . basename($filename, '.blade.php');
        }

        //Generate a html list item for each tab based on tabs file basename, set first tab as active
        $tabListItems = [];
        foreach ($tabs as $tab) {
            $tabName = str_replace('admin.settings.tabs.', '', $tab);
            $tabListItems[] = '<li class="nav-item">
            <a class="nav-link ' . (empty($tabListItems) ? 'active' : '') . '" data-toggle="pill" href="#' . $tabName . '">
            ' . __(ucfirst($tabName)) . '
            </a></li>';
        }

        return view('admin.settings.index', [
            'tabs' => $tabs,
            'tabListItems' => $tabListItems,
        ]);;
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

        return redirect()->route('admin.settings.index')->with('success', __('Icons updated!'));
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
