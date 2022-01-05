<?php

namespace App\Classes\Settings;

use App\Models\Settings;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class LanguageSettingsC
{
    public $tabTitle = 'Language Settings';
    public $languageSettings;

    public function __construct()
    {
        return;
    }


    public function updateLanguageSettings(Request $request)
    {

        $values = [
            //SETTINGS::VALUE => REQUEST-VALUE (coming from the html-form)
            "SETTINGS::LOCALE:DEFAULT" => "defaultLanguage",
            "SETTINGS::LOCALE:DYNAMIC" => "autotranslate",
            "SETTINGS::LOCALE:CLIENTS_CAN_CHANGE" => "canClientChangeLanguage",
            "SETTINGS::LOCALE:AVAILABLE" => "languages",
            "SETTINGS::LOCALE:DATATABLES" => "datatable-language"
        ];

        foreach ($values as $key => $value) {
            Settings::where('key', $key)->update(['value' => $request->get($value)]);
            Cache::forget("setting" . ':' . $key);
        }


        return redirect()->route('admin.settings.index')->with('success', 'Language settings updated!');
    }

}
