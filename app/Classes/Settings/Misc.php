<?php

namespace App\Classes\Settings;

use App\Models\Settings;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Validator;

class Misc
{
    public function __construct()
    {
        return;
    }



    public function updateSettings(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'icon' => 'nullable|max:10000|mimes:jpg,png,jpeg',
            'favicon' => 'nullable|max:10000|mimes:ico',
            'discord-bot-token' => 'nullable|string',
            'discord-client-id' => 'nullable|string',
            'discord-client-secret' => 'nullable|string',
            'discord-guild-id' => 'nullable|string',
            'discord-invite-url' => 'nullable|string',
            'discord-role-id' => 'nullable|string',
            'recaptcha-site-key' => 'nullable|string',
            'recaptcha-secret-key' => 'nullable|string',
            'enable-recaptcha' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return redirect(route('admin.settings.index') . '#misc')->with('error', __('Misc settings have not been updated!'))->withErrors($validator)
                ->withInput();
        }

        if ($request->hasFile('icon')) {
            $request->file('icon')->storeAs('public', 'icon.png');
        }
        if ($request->hasFile('favicon')) {
            $request->file('favicon')->storeAs('public', 'favicon.ico');
        }

        $values = [
            "SETTINGS::DISCORD:BOT_TOKEN" => "discord-bot-token",
            "SETTINGS::DISCORD:CLIENT_ID" => "discord-client-id",
            "SETTINGS::DISCORD:CLIENT_SECRET" => "discord-client-secret",
            "SETTINGS::DISCORD:GUILD_ID" => "discord-guild-id",
            "SETTINGS::DISCORD:INVITE_URL" => "discord-invite-url",
            "SETTINGS::DISCORD:ROLE_ID" => "discord-role-id",
            "SETTINGS::RECAPTCHA:SITE_KEY" => "recaptcha-site-key",
            "SETTINGS::RECAPTCHA:SECRET_KEY" => "recaptcha-secret-key",
            "SETTINGS::RECAPTCHA:ENABLED" => "enable-recaptcha",
        ];

        Config::set('services.discord.client_id', $request->get("discord-client-id"));
        Config::set('services.discord.client_secret', $request->get("discord-client-secret"));


        foreach ($values as $key => $value) {
            $param = $request->get($value);

            Settings::where('key', $key)->updateOrCreate(['key' => $key], ['value' => $param]);
            Cache::forget("setting" . ':' . $key);
        }


        return redirect(route('admin.settings.index') . '#misc')->with('success', __('Misc settings updated!'));
    }
}
