<?php

namespace App\Http\Controllers\Admin;

use App\Constants\PermissionGroups;
use App\Facades\Currency;
use App\Helpers\ExtensionHelper;
use App\Http\Controllers\Controller;
use App\Classes\HtmlSanitizer;
use App\Classes\GatewayFeeSettings;
use App\Settings\GeneralSettings;
use App\Settings\TermsSettings;
use App\Settings\TicketSettings;
use App\Settings\WebsiteSettings;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Qirolab\Theme\Theme;
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
     * Whether an option is a secret that must be masked in the settings page.
     *
     * @param string $type
     * @return bool
     */
    private function isSecretFieldType(string $type): bool
    {
        return in_array($type, ['secret', 'password'], true);
    }

    /**
     * Mask a field value according to its type. Secrets keep a few characters
     * of the beginning and end visible, passwords are fully hidden.
     *
     * @param string $type
     * @param string|null $value
     * @return string|null
     */
    private function maskValueForType(string $type, ?string $value): ?string
    {
        if (!$this->isSecretFieldType($type)) {
            return $value;
        }

        return $type === 'password'
            ? self::maskPasswordValue($value)
            : self::maskSecretValue($value);
    }

    /**
     * Mask a secret value so only a few characters of the beginning and end
     * remain visible, enough to identify which secret is set without leaking
     * the full value. Returns null for empty values.
     *
     * @param string|null $value
     * @return string|null
     */
    private static function maskSecretValue(?string $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        $length = strlen($value);
        $visible = 4;

        if ($length <= $visible * 2) {
            return str_repeat('*', $length);
        }

        return substr($value, 0, $visible) . str_repeat('*', 10) . substr($value, -$visible);
    }

    /**
     * Fully mask a password so its value and length are never exposed.
     * Returns null for empty values.
     *
     * @param string|null $value
     * @return string|null
     */
    private static function maskPasswordValue(?string $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        return str_repeat('*', 12);
    }

    /**
     * Display a listing of the resource.
     *
     * @return Application|Factory|View|Response
     */
    public function index()
    {
        $this->checkAnyPermission(array_merge(
            PermissionGroups::settingsPermissions(),
            [self::ICON_PERMISSION]
        ));

        $settings = collect();
        $settingsFiles = $this->getAvailableSettingsClasses();

        foreach ($settingsFiles as $file) {

            $className = $file;
            // instantiate the class and call toArray method to get all options
            $settingsInstance = new $className();
            $options = $settingsInstance->toArray();

            // call getOptionInputData method to get all options
            if (method_exists($className, 'getOptionInputData')) {
                $optionInputData = $className::getOptionInputData();
            } else {
                $optionInputData = [];
            }

            // collect all option input data
            $optionsData = [];
            $sectionDefinitions = $optionInputData['sections'] ?? [];
            $categoryName = str_replace('Settings', '', class_basename($className));
            $categoryDescription = $optionInputData['category_description'] ?? null;

            foreach ($options as $key => $value) {
                $type = $optionInputData[$key]['type'] ?? 'string';

                $optionsData[$key] = [
                    'value' => $this->maskValueForType($type, $value),
                    'label' => $optionInputData[$key]['label'] ?? ucwords(str_replace('_', ' ', $key)),
                    'type' => $type,
                    'description' => $optionInputData[$key]['description'] ?? '',
                    'options' => $optionInputData[$key]['options'] ?? [],
                    'identifier' => $optionInputData[$key]['identifier'] ?? 'option',
                    'section' => $optionInputData[$key]['section'] ?? null,
                    'visible_when' => $optionInputData[$key]['visible_when'] ?? null,
                    'suffix' => $optionInputData[$key]['suffix'] ?? null,
                ];

                if($type === 'number') {
                    $optionsData[$key]['step'] = $optionInputData[$key]['step'] ?? '1';

                    if ($optionInputData[$key]['mustBeConverted'] ?? false) {
                        $optionsData[$key]['converted_value'] = Currency::formatForForm($value);
                    }
                }
            }

            // Payment gateway fee settings are managed by the core for every
            // gateway, so gateway creators do not have to declare them.
            if (GatewayFeeSettings::isGatewaySettings($className)) {
                $sectionDefinitions = array_merge($sectionDefinitions, GatewayFeeSettings::sections());

                foreach (GatewayFeeSettings::optionDefinitions() as $key => $definition) {
                    $feeValues = GatewayFeeSettings::values($settingsInstance);

                    $optionsData[$key] = [
                        'value' => $feeValues[$key],
                        'label' => $definition['label'] ?? ucwords(str_replace('_', ' ', $key)),
                        'type' => $definition['type'] ?? 'string',
                        'description' => $definition['description'] ?? '',
                        'options' => $definition['options'] ?? [],
                        'identifier' => $definition['identifier'] ?? 'option',
                        'section' => $definition['section'] ?? null,
                        'visible_when' => $definition['visible_when'] ?? null,
                        'suffix' => $definition['suffix'] ?? null,
                    ];

                    if (($definition['type'] ?? null) === 'number') {
                        $optionsData[$key]['step'] = $definition['step'] ?? '1';

                        if ($definition['mustBeConverted'] ?? false) {
                            $optionsData[$key]['converted_value'] = Currency::formatForForm((int) $feeValues[$key]);
                        }
                    }
                }
            }

            // Group options into named sections so the view renders each
            // section header exactly once.
            $optionsData = $this->groupOptionsIntoSections($optionsData, $sectionDefinitions, $categoryName, $categoryDescription);

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

            $settings[$categoryName] = $optionsData;
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
     * Group option fields into sections.
     *
     * Each option can declare the section it belongs to via the "section" key.
     * Section metadata (label + description) is defined once under the
     * "sections" key of getOptionInputData(). Sections are rendered in the
     * order they are first referenced by an option, followed by any declared
     * sections that were not used. Options without a section are placed in a
     * leading section titled with the page name, so every settings page is
     * rendered uniformly with a header.
     *
     * @param array<string, array<string, mixed>> $optionsData
     * @param array<string, array<string, string>> $sectionDefinitions
     * @param string $categoryName Display name of the settings page.
     * @param string|null $categoryDescription Description for the default section.
     * @return array<string, mixed>
     */
    private function groupOptionsIntoSections(array $optionsData, array $sectionDefinitions, string $categoryName, ?string $categoryDescription): array
    {
        $sections = [];
        $ungrouped = [];

        foreach ($optionsData as $key => $optionData) {
            $sectionKey = $optionData['section'];
            unset($optionData['section']);

            if ($sectionKey === null) {
                $ungrouped[$key] = $optionData;
                continue;
            }

            $sections[$sectionKey]['options'][$key] = $optionData;
            if (!isset($sections[$sectionKey]['label'])) {
                $sections[$sectionKey]['label'] = $sectionDefinitions[$sectionKey]['label'] ?? ucwords(str_replace('_', ' ', $sectionKey));
                $sections[$sectionKey]['description'] = $sectionDefinitions[$sectionKey]['description'] ?? '';
            }
        }

        // Include declared sections in definition order, even when empty.
        foreach ($sectionDefinitions as $sectionKey => $definition) {
            if (!isset($sections[$sectionKey])) {
                $sections[$sectionKey] = [
                    'label' => $definition['label'] ?? ucwords(str_replace('_', ' ', $sectionKey)),
                    'description' => $definition['description'] ?? '',
                    'options' => [],
                ];
            }
        }

        $grouped = ['sections' => $sections];

        if (!empty($ungrouped)) {
            array_unshift($grouped['sections'], [
                'label' => $categoryName,
                'description' => $categoryDescription,
                'options' => $ungrouped,
            ]);
        }

        return $grouped;
    }

    /**
     * Update the specified resource in storage.
     *
     */
    public function update(Request $request)
    {
        $category = strtolower((string) $request->input('category'));
        $settingsClassMap = $this->getSettingsCategoryClassMap();

        if (!isset($settingsClassMap[$category])) {
            abort(400, 'Invalid settings category.');
        }

        $resolvedSettingsClass = $settingsClassMap[$category];
        $requestedSettingsClass = (string) $request->input('settings_class');
        if ($requestedSettingsClass !== $resolvedSettingsClass) {
            abort(400, 'Invalid settings class.');
        }

        $redirectCategory = str_replace('Settings', '', class_basename($resolvedSettingsClass));

        $this->checkPermission("settings." . $category . ".write");

        if (method_exists($resolvedSettingsClass, 'getValidations')) {
            $validations = $resolvedSettingsClass::getValidations();
        } else {
            $validations = [];
        }


        $validator = Validator::make($request->all(), $validations);
        if ($validator->fails()) {
            return Redirect::to('admin/settings' . '#' . $redirectCategory)->withErrors($validator)->withInput();
        }

        $settingsClass = new $resolvedSettingsClass();
        $optionInputData = method_exists($resolvedSettingsClass, 'getOptionInputData')
            ? $resolvedSettingsClass::getOptionInputData()
            : [];

        foreach ($settingsClass->toArray() as $key => $value) {
            // Get the type of the settingsclass property
            $rp = new \ReflectionProperty($settingsClass, $key);
            $rpType = $rp->getType();

            if ($rpType && $rpType->getName() === 'bool') {
                $settingsClass->$key = $request->has($key);
                continue;
            }
            if ($rp->name == 'available') {
                $settingsClass->$key = implode(",", $request->$key);
                continue;
            }

            $inputValue = $request->input($key);

            // Secret fields are only rendered as a masked placeholder. If the
            // submitted value is empty or still equals that placeholder, keep
            // the stored secret so saving other settings does not wipe it.
            if ($this->isSecretFieldType($optionInputData[$key]['type'] ?? 'string')) {
                $type = $optionInputData[$key]['type'] ?? 'string';
                if ($inputValue === null || trim((string) $inputValue) === '' || $inputValue === $this->maskValueForType($type, $value)) {
                    continue;
                }
            }

            // User/referral currency values are stored in thousandths.
            if (isset($optionInputData[$key]['mustBeConverted']) && $optionInputData[$key]['mustBeConverted'] && !is_null($inputValue) && $inputValue !== '') {
                $inputValue = Currency::prepareForDatabase($inputValue);
            }

            $nullable = $rpType ? $rpType->allowsNull() : true;
            if ($nullable) {
                $settingsClass->$key = $inputValue ?? null;
            } else {
                $settingsClass->$key = $inputValue;
            }
        }

        // Sanitize HTML rendered with {!! !!} on the public pages: legal pages
        // (imprint, privacy policy, tos), MOTD, global alert and ticket info.
        if ($resolvedSettingsClass === TermsSettings::class
            || $resolvedSettingsClass === WebsiteSettings::class
            || $resolvedSettingsClass === GeneralSettings::class
            || $resolvedSettingsClass === TicketSettings::class) {
            $htmlKeys = array_merge(
                ['imprint', 'privacy_policy', 'terms_of_service'],
                ['motd_message', 'alert_message', 'information'],
            );

            foreach ($htmlKeys as $key) {
                if (isset($settingsClass->$key) && $settingsClass->$key !== null) {
                    $settingsClass->$key = (new HtmlSanitizer())->clean($settingsClass->$key);
                }
            }
        }

        if (GatewayFeeSettings::isGatewaySettings($resolvedSettingsClass)) {
            GatewayFeeSettings::saveFromRequest($settingsClass, $request->all());
        }

        $settingsClass->save();


        return Redirect::to('admin/settings' . '#' . $redirectCategory)->with('success', 'Settings updated successfully.');
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
}
