<?php

namespace App\Providers;

use App\Settings\GeneralSettings;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;
use App\Settings\DiscordSettings;
use Exception;

class SettingsServiceProvider extends ServiceProvider
{
    protected $discordSettings;
    protected $generalSettings;

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        // No need to register anything
    }

    /**
     * Bootstrap any application services.
     *
     * @param  DiscordSettings  $discordSettings
     * @param  GeneralSettings  $generalSettings
     * @return void
     */
    public function boot(DiscordSettings $discordSettings, GeneralSettings $generalSettings)
    {
        $this->discordSettings = $discordSettings;
        $this->generalSettings = $generalSettings;

        if (config('app.key') == null) return;
        if (!Schema::hasColumn('settings', 'payload')) return;

        try {
            /*
             * DISCORD
             */
            // Inject the settings into the config
            Config::set('services.discord.client_id', $this->discordSettings->client_id ?: "");
            Config::set('services.discord.client_secret', $this->discordSettings->client_secret ?: "");
            Config::set('services.discord.redirect', env('APP_URL', 'http://localhost') . '/auth/callback');
            // optional
            Config::set('services.discord.allow_gif_avatars', (bool)env('DISCORD_AVATAR_GIF', true));
            Config::set('services.discord.avatar_default_extension', env('DISCORD_EXTENSION_DEFAULT', 'jpg'));

            /*
             * RECAPTCHA
             */
            Config::set('recaptcha.api_site_key', $this->generalSettings->recaptcha_site_key ?: "");
            Config::set('recaptcha.api_secret_key', $this->generalSettings->recaptcha_secret_key ?: "");

        } catch (Exception $e) {
            Log::error("Couldn't find settings. Probably the installation is not complete. " . $e);
        }
    }
}
