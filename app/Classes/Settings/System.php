<?php

namespace App\Classes\Settings;

use App\Models\Settings;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;

class System
{


    public function __construct()
    {
        return;
    }



    public function updateSettings(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "register-ip-check" => "boolean",
            "server-create-charge-first-hour" => "boolean",
            "credits-display-name" => "required|string",
            "allocation-limit" => "required|min:0|integer",
            "force-email-verification" => "boolean",
            "force-discord-verification" => "boolean",
            "initial-credits" => "required|min:0|integer",
            "initial-server-limit" => "required|min:0|integer",
            "credits-reward-amount-discord" => "required|min:0|integer",
            "credits-reward-amount-email" => "required|min:0|integer",
            "server-limit-discord" => "required|min:0|integer",
            "server-limit-email" => "required|min:0|integer",

        ]);

        if ($validator->fails()) {
            return redirect(route('admin.settings.index') . '#system')->with('error', __('System settings not updated!'))->withErrors($validator)
                ->withInput();
        }


        $values = [
            "SETTINGS::SYSTEM:REGISTER_IP_CHECK" => "register-ip-check",
            "SETTINGS::SYSTEM:SERVER_CREATE_CHARGE_FIRST_HOUR" => "server-create-charge-first-hour",
            "SETTINGS::SYSTEM:CREDITS_DISPLAY_NAME" => "credits-display-name",
            "SETTINGS::SERVER:ALLOCATION_LIMIT" => "allocation-limit",
            "SETTINGS::USER:FORCE_DISCORD_VERIFICATION" => "force-discord-verification",
            "SETTINGS::USER:FORCE_EMAIL_VERIFICATION" => "force-email-verification",
            "SETTINGS::USER:INITIAL_CREDITS" => "initial-credits",
            "SETTINGS::USER:INITIAL_SERVER_LIMIT" => "initial-server-limit",
            "SETTINGS::USER:CREDITS_REWARD_AFTER_VERIFY_DISCORD" => "credits-reward-amount-discord",
            "SETTINGS::USER:CREDITS_REWARD_AFTER_VERIFY_EMAIL" => "credits-reward-amount-email",
            "SETTINGS::USER:SERVER_LIMIT_REWARD_AFTER_VERIFY_DISCORD" => "server-limit-discord",
            "SETTINGS::USER:SERVER_LIMIT_REWARD_AFTER_VERIFY_EMAIL" => "server-limit-email",
            "SETTINGS::MISC:PHPMYADMIN:URL" => "phpmyadmin-url",
        ];


        foreach ($values as $key => $value) {
            $param = $request->get($value);

            Settings::where('key', $key)->updateOrCreate(['key' => $key], ['value' => $param]);
            Cache::forget("setting" . ':' . $key);
        }
        return redirect(route('admin.settings.index') . '#system')->with('success', __('System settings updated!'));
    }
}
