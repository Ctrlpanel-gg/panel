<?php

namespace App\Http\Controllers;

use App\Settings\LocaleSettings;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class PreferencesController extends Controller
{
    private $localeSettings;

    public function __construct(LocaleSettings $localeSettings)
    {
        $this->localeSettings = $localeSettings;
    }

    public function index(Request $request)
    {
        $localeSettings = $this->localeSettings;

        return view('preferences.index', compact('localeSettings'));
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
