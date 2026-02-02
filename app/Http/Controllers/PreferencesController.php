<?php

namespace App\Http\Controllers;

use App\Settings\GeneralSettings;
use App\Settings\LocaleSettings;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class PreferencesController extends Controller
{
    private $localeSettings;
    private $generalSettings;

    public function __construct(LocaleSettings $localeSettings, GeneralSettings $generalSettings)
    {
        $this->localeSettings = $localeSettings;
        $this->generalSettings = $generalSettings;
    }

    public function index(Request $request)
    {
        $localeSettings = $this->localeSettings;
        $generalSettings = $this->generalSettings;

        $currencyOverrideAlert = null;
        // Only show alert when an override is actively set (non-empty string)
        if (!empty($generalSettings->currency_format_override)) {
            $currencyOverrideAlert = __('Global currency format override is enabled. All currency and number displays use :locale formatting. Your language preference does not affect currency formatting.', ['locale' => $generalSettings->currency_format_override]);
        }

        return view('preferences.index', compact('localeSettings', 'currencyOverrideAlert'));
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'locale' => 'required_with:locale|in:' . $this->localeSettings->available,
        ]);

        if (isset($data['locale'])) {
            Session::put('locale', $data['locale']);
        }

        return redirect()->route('preferences.index')->with('success', __('Your preferences have been updated.', locale: Session::get('locale')));
    }
}
