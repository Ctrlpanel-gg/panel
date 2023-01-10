<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
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
            $tabs[] = 'admin.settings.tabs.'.basename($filename, '.blade.php');
        }

        //Generate a html list item for each tab based on tabs file basename, set first tab as active
        $tabListItems = [];
        foreach ($tabs as $tab) {
            $tabName = str_replace('admin.settings.tabs.', '', $tab);
            $tabListItems[] = '<li class="nav-item">
            <a class="nav-link '.(empty($tabListItems) ? 'active' : '').'" data-toggle="pill" href="#'.$tabName.'">
            '.__(ucfirst($tabName)).'
            </a></li>';
        }

        return view('admin.settings.index', [
            'tabs' => $tabs,
            'tabListItems' => $tabListItems,
        ]);
    }
}
