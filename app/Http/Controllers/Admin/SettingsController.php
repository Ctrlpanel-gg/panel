<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\ExtensionHelper;
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
        $settings_classes = [];

        // get all app settings
        $app_settings = scandir(app_path('Settings'));
        $app_settings = array_diff($app_settings, ['.', '..']);
        // append App\Settings to class name
        foreach ($app_settings as $app_setting) {
            $settings_classes[] = 'App\\Settings\\' . str_replace('.php', '', $app_setting);
        }
        // get all extension settings
        $settings_files = array_merge($settings_classes, ExtensionHelper::getAllExtensionSettingsClasses());


        foreach ($settings_files as $file) {

            $className = $file;
            // instantiate the class and call toArray method to get all options
            $options = (new $className())->toArray();

            // call getOptionInputData method to get all options
            if (method_exists($className, 'getOptionInputData')) {
                $optionInputData = $className::getOptionInputData();
            } else {
                $optionInputData = [];
            }

            // collect all option input data
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

            // collect category icon if available
            if (isset($optionInputData['category_icon'])) {
                $optionsData['category_icon'] = $optionInputData['category_icon'];
            }
            $optionsData['settings_class'] = $className;

            $settings[str_replace('Settings', '', class_basename($className))] = $optionsData;
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

        $this->checkPermission("settings.".strtolower($category).".write");

        $settings_class = request()->get('settings_class');

        if (method_exists($settings_class, 'getValidations')) {
            $validations = $settings_class::getValidations();
        } else {
            $validations = [];
        }


        $validator = Validator::make($request->all(), $validations);
        if ($validator->fails()) {
            return Redirect::to('admin/settings' . '#' . $category)->withErrors($validator)->withInput();
        }

        $settingsClass = new $settings_class();

        foreach ($settingsClass->toArray() as $key => $value) {
            // Get the type of the settingsclass property
            $rp = new \ReflectionProperty($settingsClass, $key);
            $rpType = $rp->getType();

            if ($rpType == 'bool') {
                $settingsClass->$key = $request->has($key);
                continue;
            }

            $nullable = $rpType->allowsNull();
            if ($nullable) $settingsClass->$key = $request->input($key) ?? null;
            else $settingsClass->$key = $request->input($key);
        }

        $settingsClass->save();


        return Redirect::to('admin/settings' . '#' . $category)->with('success', 'Settings updated successfully.');
    }
}
