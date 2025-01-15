<?php

namespace Database\Seeders;

use App\Settings\TermsSettings;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use Qirolab\Theme\Theme;

class TermsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $settings = app(TermsSettings::class);

        if (empty($settings->terms_of_service)) {
            $settings->terms_of_service = File::get(Theme::path($path = 'views', "default") . '/information/tos-content.blade.php');
        }

        if (empty($settings->privacy_policy)) {
            $settings->privacy_policy = File::get(Theme::path($path = 'views', "default") . '/information/privacy-content.blade.php');
        }

        if (empty($settings->imprint)) {
            $settings->imprint = File::get(Theme::path($path = 'views', "default") . '/information/imprint-content.blade.php');
        }

        $settings->save();
    }
}
