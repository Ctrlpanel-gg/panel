<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Validator;
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
            $options = (new $className())->toArray();

            if (method_exists($className, 'getOptionInputData')) {
                $optionInputData = $className::getOptionInputData();
            } else {
                $optionInputData = [];
            }

            $optionsData = [];

            foreach ($options as $key => $value) {
                $optionsData[$key] = [
                    'value' => $value,
                    'label' => $optionInputData[$key]['label'] ?? ucwords(str_replace('_', ' ', $key)),
                    'type' => $optionInputData[$key]['type'] ?? 'string',
                    'description' => $optionInputData[$key]['description'] ?? '',
                    'options' => $optionInputData[$key]['options'] ?? [],
                ];
            }

            $settings[str_replace('Settings.php', '', $file)] = $optionsData;
        }

        $settings->sort();


        $themes = array_diff(scandir(base_path('themes')), array('..', '.'));

        return view('admin.settings.index', [
            'settings' => $settings->all(),
            'themes' => $themes,
            'active_theme' => Theme::active(),
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     */
    public function update(Request $request)
    {
        $category = request()->get('category');

        error_log($category);


        $className = 'App\\Settings\\' . $category . 'Settings';
        if (method_exists($className, 'getValidations')) {
            $validations = $className::getValidations();
        } else {
            $validations = [];
        }


        $validator = Validator::make($request->all(), $validations);
        if ($validator->fails()) {
            return Redirect::to('admin/settings' . '#' . $category)->withErrors($validator)->withInput();
        }


        return Redirect::to('admin/settings' . '#' . $category)->with('success', 'Settings updated successfully.');
    }
}
