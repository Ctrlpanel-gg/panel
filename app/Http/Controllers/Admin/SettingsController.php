<?php

namespace App\Http\Controllers\Admin;

use App\Facades\Currency;
use App\Helpers\ExtensionHelper;
use App\Http\Controllers\Controller;
use App\Support\HtmlSanitizer;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Qirolab\Theme\Theme;
use ReflectionProperty;
use Spatie\LaravelSettings\Settings;

class SettingsController extends Controller
{
    const ICON_PERMISSION = "admin.icons.edit";

    /**
     * Build the list of available settings classes from app and extensions.
     */
    private function getAvailableSettingsClasses(): array
    {
        $settingsClasses = [];

        $appSettings = scandir(app_path('Settings'));
        $appSettings = array_diff($appSettings, ['.', '..']);

        foreach ($appSettings as $appSetting) {
            $settingsClasses[] = 'App\\Settings\\' . str_replace('.php', '', $appSetting);
        }

        return array_values(array_filter(
            array_merge($settingsClasses, ExtensionHelper::getAllExtensionSettingsClasses()),
            static fn (string $className): bool => class_exists($className) && is_subclass_of($className, Settings::class)
        ));
    }

    /**
     * Build a category => class map used to validate update requests.
     */
    private function getSettingsCategoryClassMap(): array
    {
        $categoryMap = [];

        foreach ($this->getAvailableSettingsClasses() as $className) {
            $categoryMap[strtolower(str_replace('Settings', '', class_basename($className)))] = $className;
        }

        return $categoryMap;
    }

    /**
     * Display a listing of the resource.
     *
     * @return Application|Factory|View|Response
     */
    public function index()
    {
        if (! $this->canManageSettings()) {
            abort(403, __('User does not have the right permissions.'));
        }

        // get all other settings in app/Settings directory
        // group items by file name like $categories
        $settings = collect();
        $settingsFiles = $this->getAvailableSettingsClasses();

        foreach ($settingsFiles as $file) {

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

                if (($optionInputData[$key]['type'] ?? null) === 'password') {
                    $optionsData[$key]['configured'] = ! empty($value);
                    $optionsData[$key]['value'] = '';
                }

                if (($optionInputData[$key]['type'] ?? null) === 'number') {
                    $optionsData[$key]['step'] = $optionInputData[$key]['step'] ?? '1';

                    if ($optionInputData[$key]['mustBeConverted'] ?? false) {
                        $optionsData[$key]['converted_value'] = Currency::formatForForm($value);
                    }
                }
            }

            // collect category icon if available
            if (isset($optionInputData['category_icon'])) {
                $optionsData['category_icon'] = $optionInputData['category_icon'];
            }

            if (isset($optionInputData['position'])) {
                $optionsData['position'] = $optionInputData['position'];
            }else{
                $optionsData['position'] = 99;
            }

            $optionsData['settings_class'] = $className;

            $category = str_replace('Settings', '', class_basename($className));

            if (! $this->canViewSettingsCategory($category)) {
                continue;
            }

            $settings[$category] = $optionsData;
        }

        $settings = $settings->sortBy('position');

        $themes = array_diff(scandir(base_path('themes')), array('..', '.'));

        $images = [
            'icon' => Storage::disk('local')->exists('public/icon.png')
                ? asset('storage/icon.png') . '?v=' . filemtime(Storage::path('public/icon.png'))
                : asset('images/ctrlpanel_logo.png'),

            'logo' => Storage::disk('local')->exists('public/logo.png')
                ? asset('storage/logo.png') . '?v=' . filemtime(Storage::path('public/logo.png'))
                : asset('images/ctrlpanel_logo.png'),

            'favicon' => Storage::disk('local')->exists('public/favicon.ico')
                ? asset('storage/favicon.ico') . '?v=' . filemtime(Storage::path('public/favicon.ico'))
                : asset('images/ctrlpanel_logo.png'),
        ];

        return view('admin.settings.index', [
            'settings' => $settings->all(),
            'themes' => $themes,
            'active_theme' => Theme::active(),
            'images' => $images
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     */
    public function update(Request $request)
    {
        $category = strtolower((string) $request->input('category'));
        $settingsClassMap = $this->getSettingsCategoryClassMap();

        if (! isset($settingsClassMap[$category])) {
            abort(400, 'Invalid settings category.');
        }

        $resolvedSettingsClass = $settingsClassMap[$category];

        $this->checkPermission("settings." . $category . ".write");

        if (method_exists($resolvedSettingsClass, 'getValidations')) {
            $validations = $resolvedSettingsClass::getValidations();
        } else {
            $validations = [];
        }


        $validator = Validator::make($request->all(), $validations);
        if ($validator->fails()) {
            return Redirect::to('admin/settings' . '#' . $category)->withErrors($validator)->withInput();
        }

        $settingsClass = new $resolvedSettingsClass();

        $optionInputData = method_exists($resolvedSettingsClass, 'getOptionInputData')
            ? $resolvedSettingsClass::getOptionInputData()
            : [];

        foreach ($settingsClass->toArray() as $key => $value) {
            // Get the type of the settingsclass property
            $rp = new ReflectionProperty($settingsClass, $key);
            $rpType = $rp->getType();

            if ($rpType && $rpType->getName() === 'bool') {
                $settingsClass->$key = $request->has($key);
                continue;
            }
            if ($rp->name == 'available') {
                $settingsClass->$key = implode(",", (array) $request->input($key, []));
                continue;
            }

            $inputValue = $request->input($key);
            $fieldType = $optionInputData[$key]['type'] ?? null;

            if ($fieldType === 'password' && ($inputValue === null || $inputValue === '')) {
                continue;
            }

            // User/referral currency values are stored in thousandths.
            $currencyKeys = [
                'reward',
                'credits_reward_after_verify_discord',
                'credits_reward_after_verify_email',
                'initial_credits',
            ];

            if (in_array($key, $currencyKeys, true) && !is_null($inputValue) && $inputValue !== '') {
                $inputValue = Currency::prepareForDatabase($inputValue);
            }

            if ($this->shouldSanitizeRichText($resolvedSettingsClass, $key)) {
                $inputValue = HtmlSanitizer::sanitizeRichText($inputValue);
            }

            $nullable = $rpType ? $rpType->allowsNull() : true;
            if ($nullable) {
                $settingsClass->$key = $inputValue ?? null;
            } else {
                $settingsClass->$key = $inputValue;
            }
        }
        $settingsClass->save();


        return Redirect::to('admin/settings' . '#' . $category)->with('success', 'Settings updated successfully.');
    }

    public function updateIcons(Request $request)
    {
        $this->checkPermission(self::ICON_PERMISSION);

        $validator = Validator::make($request->all(), [
            'icon' => 'nullable|max:10000|file|mimes:jpg,png,jpeg',
            'logo' => 'nullable|max:10000|file|mimes:jpg,png,jpeg',
            'favicon' => 'nullable|max:10000|file|mimes:ico,x-icon',
        ]);

        if ($validator->fails()) {
            return Redirect::to('admin/settings#icons')->withErrors($validator)->withInput();
        }

        if ($request->hasFile('icon')) {
            $request->file('icon')->storeAs('public', 'icon.png');
        }
        if ($request->hasFile('logo')) {
            $request->file('logo')->storeAs('public', 'logo.png');
        }
        if ($request->hasFile('favicon')) {
            $request->file('favicon')->storeAs('public', 'favicon.ico');
        }

        return Redirect::to('admin/settings#icons')->with('success', 'Icons updated successfully.');
    }

    private function canManageSettings(): bool
    {
        $user = request()->user();

        if (! $user) {
            return false;
        }

        if ($user->can('*') || $user->can(self::ICON_PERMISSION)) {
            return true;
        }

        return $user->getAllPermissions()
            ->pluck('name')
            ->contains(fn (string $permission) => str_starts_with($permission, 'settings.'));
    }

    private function canViewSettingsCategory(string $category): bool
    {
        $user = request()->user();

        if (! $user) {
            return false;
        }

        $permission = 'settings.' . strtolower($category) . '.write';

        return $user->can('*') || $user->can($permission);
    }

    private function shouldSanitizeRichText(string $settingsClass, string $key): bool
    {
        return in_array([$settingsClass, $key], [
            ['App\\Settings\\GeneralSettings', 'alert_message'],
            ['App\\Settings\\WebsiteSettings', 'motd_message'],
            ['App\\Settings\\TicketSettings', 'information'],
            ['App\\Settings\\TermsSettings', 'terms_of_service'],
            ['App\\Settings\\TermsSettings', 'privacy_policy'],
            ['App\\Settings\\TermsSettings', 'imprint'],
            ['App\\Settings\\InvoiceSettings', 'additional_notes'],
        ], true);
    }
}
