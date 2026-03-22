<?php

namespace App\Providers;

use App\Settings\GeneralSettings;
use App\Settings\MailSettings;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;
use App\Settings\DiscordSettings;
use Qirolab\Theme\Theme;
use Exception;

class SettingsServiceProvider extends ServiceProvider
{
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
     * @return void
     */
    public function boot()
    {
        if (config('app.key') == null) return;
        if (!Schema::hasColumn('settings', 'payload')) return;

        try {
            $discordSettings = $this->app->make(DiscordSettings::class);
            $generalSettings = $this->app->make(GeneralSettings::class);

            /*
             * DISCORD
             */
            // Inject the settings into the config
            Config::set('services.discord.client_id', $discordSettings->client_id ?: "");
            Config::set('services.discord.client_secret', $discordSettings->client_secret ?: "");
            Config::set('services.discord.redirect', config('app.url', 'http://localhost') . '/auth/callback');
            // optional
            Config::set('services.discord.allow_gif_avatars',  true);
            Config::set('services.discord.avatar_default_extension', 'jpg');

            /*
             * RECAPTCHA
             */
            Config::set('recaptcha.api_site_key', $generalSettings->recaptcha_site_key ?: "");
            Config::set('recaptcha.api_secret_key', $generalSettings->recaptcha_secret_key ?: "");

            Config::set('recaptchav3.sitekey', $generalSettings->recaptcha_site_key ?: "");
            Config::set('recaptchav3.secret', $generalSettings->recaptcha_secret_key ?: "");

            Config::set('turnstile.turnstile_site_key', $generalSettings->recaptcha_site_key ?: "");
            Config::set('turnstile.turnstile_secret_key', $generalSettings->recaptcha_secret_key ?: "");

        } catch (Exception $e) {
            Log::error("Couldn't find settings. Probably the installation is not complete. " . $e);
        }

        try {
            $generalSettings = $this->app->make(GeneralSettings::class);

            if (!file_exists(base_path('themes') . "/" . $generalSettings->theme)) {
                $generalSettings->theme = "default";
            }

            if ($generalSettings->theme && $generalSettings->theme !== config('theme.active')) {
                Theme::set($generalSettings->theme, "default");
            } else {
                Theme::set("default", "default");
            }

            $settings = $this->app->make(MailSettings::class);
            $settings->setConfig();

        } catch (Exception $e) {
            Log::error("Couldnt load Settings. Probably the installation is not completet. " . $e);
        }
    }
}
