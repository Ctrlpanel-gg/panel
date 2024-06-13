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
                    'identifier' => $optionInputData[$key]['identifier'] ?? 'option'
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
            'tabs' => $tabs,
            'tabListItems' => $tabListItems,
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

        $this->checkPermission("settings." . strtolower($category) . ".write");

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
            if ($rp->name == 'available') {
                $settingsClass->$key = implode(",", $request->$key);
                continue;
            }

            $nullable = $rpType->allowsNull();
            if ($nullable) $settingsClass->$key = $request->input($key) ?? null;
            else $settingsClass->$key = $request->input($key);
        }
        $settingsClass->save();


        return Redirect::to('admin/settings' . '#' . $category)->with('success', 'Settings updated successfully.');
    }

    public function updateIcons(Request $request)
    {
        $request->validate([
            'icon' => 'nullable|max:10000|mimes:jpg,png,jpeg',
            'logo' => 'nullable|max:10000|mimes:jpg,png,jpeg',
            'favicon' => 'nullable|max:10000|mimes:ico',
        ]);

        if ($request->hasFile('icon')) {
            $request->file('icon')->storeAs('public', 'icon.png');
        }
        if ($request->hasFile('logo')) {
            $request->file('logo')->storeAs('public', 'logo.png');
        }
        if ($request->hasFile('favicon')) {
            $request->file('favicon')->storeAs('public', 'favicon.ico');
        }

        return Redirect::to('admin/settings')->with('success', 'Icons updated successfully.');
    }
}
