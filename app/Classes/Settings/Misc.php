<?php

namespace App\Classes\Settings;

use App\Models\Settings;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Session;

class Misc
{
    public $tabTitle = 'Misc Settings';
    public $miscSettings;

    public function __construct()
    {
        return;
    }



    public function updateMiscSettings(Request $request)
    {
        $request->validate([
            'icon' => 'nullable|max:10000|mimes:jpg,png,jpeg',
            'favicon' => 'nullable|max:10000|mimes:ico',
        ]);

        if ($request->hasFile('icon')) {
            $request->file('icon')->storeAs('public', 'icon.png');
        }

        if ($request->hasFile('favicon')) {
            $request->file('favicon')->storeAs('public', 'favicon.ico');
        }

        $values = [
            //SETTINGS::VALUE => REQUEST-VALUE (coming from the html-form)
            "SETTINGS::MISC:PHPMYADMIN:URL" => "phpmyadmin-url",
            "SETTINGS::DISCORD:BOT_TOKEN" => "discord-bot-token",
            "SETTINGS::DISCORD:CLIENT_ID" => "discord-client-id",
            "SETTINGS::DISCORD:CLIENT_SECRET" => "discord-client-secret",
            "SETTINGS::DISCORD:GUILD_ID" => "discord-guild-id",
            "SETTINGS::DISCORD:INVITE_URL" => "discord-invite-url",
            "SETTINGS::DISCORD:ROLE_ID" => "discord-role-id"

        ];

        Config::set('services.discord.client_id', $request->get("discord-client-id"));
        Config::set('services.discord.client_secret', $request->get("discord-client-secret"));


        foreach ($values as $key => $value) {
            $param = $request->get($value);
            if (!$param) {
                $param = "";
            }
            Settings::where('key', $key)->updateOrCreate(['key' => $key], ['value' => $param]);
            Cache::forget("setting" . ':' . $key);
        }


        return redirect()->route('admin.settings.index')->with('success', 'Misc settings updated!');
    }
}
