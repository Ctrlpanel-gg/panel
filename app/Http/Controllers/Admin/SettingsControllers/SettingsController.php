<?php

namespace App\Http\Controllers\Admin\SettingsControllers;

use App\Http\Controllers\Controller;
use App\Models\Settings;
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
        ]);
    }


    public function updatevalue(Request $request)
    {
        $setting = Settings::findOrFail($request->input('key'));

        $request->validate([
            'key'   => 'required|string|max:191',
            'value' => 'required|string|max:191',
        ]);

        $setting->update($request->all());

        return redirect()->route('admin.settings.index')->with('success', __('configuration has been updated!'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param Settings $setting
     * @return Response
     */
    public function destroy(Settings $setting)
    {
        //
    }

    public function datatable()
    {
        $query = Settings::
            where('key', 'like', '%SYSTEM%')
            ->orWhere('key', 'like', '%USER%')
            ->orWhere('key', 'like', '%SERVER%');

        return datatables($query)
            ->addColumn('actions', function (Settings $setting) {
                return '<button data-content="' . __("Edit") . '" data-toggle="popover" data-trigger="hover" data-placement="top" onclick="configuration.parse(\'' . $setting->key . '\',\'' . $setting->value . '\',\'' . $setting->type . '\')" data-content="Edit" data-trigger="hover" data-toggle="tooltip" class="btn btn-sm btn-info mr-1"><i class="fas fa-pen"></i></button> ';
            })
            ->editColumn('created_at', function (Settings $setting) {
                return $setting->created_at ? $setting->created_at->diffForHumans() : '';
            })
            ->rawColumns(['actions'])
            ->make();
    }

}
