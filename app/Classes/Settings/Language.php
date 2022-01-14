<?php

namespace App\Classes\Settings;

use App\Models\Settings;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Session;

class Language
{
    public function __construct()
    {
        return;
    }


    public function updateSettings(Request $request)
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
            $param = $request->get($value);

            Settings::where('key', $key)->updateOrCreate(['key' => $key], ['value' => $param]);
            Cache::forget("setting" . ':' . $key);
            Session::remove("locale");
        }


        return redirect(route('admin.settings.index') . '#language')->with('success', __('Language settings updated!'));
    }
}
