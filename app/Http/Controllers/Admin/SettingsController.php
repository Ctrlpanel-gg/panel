<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Response;
use Qirolab\Theme\Theme;

class SettingsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return Application|Factory|View|Response
     */
    public function index()
    {

        // get all other settings in app/Settings directory
        // group items by file name like $categories
        $settings = collect();
        foreach (scandir(app_path('Settings')) as $file) {
            if (in_array($file, ['.', '..'])) {
                continue;
            }
            $className = 'App\\Settings\\' . str_replace('.php', '', $file);
            $settings[str_replace('Settings.php', '', $file)] = (new $className())->toCollection()->all();
        }
        $settings->sort();


        $themes = array_diff(scandir(base_path('themes')), array('..', '.'));

        return view('admin.settings.index', [
            'settings' => $settings->all(),
            'themes' => $themes,
            'active_theme' => Theme::active(),
        ]);
    }
}
