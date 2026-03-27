<?php

namespace App\Console\Commands;

use App\Settings\GeneralSettings;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class DisableRecaptcha extends Command
{
    protected $signature = 'cp:recaptcha:toggle';
    protected $description = 'Toggle Recaptcha version between null, v2, and v3 and Cloudflare turnstile.';

    protected ?GeneralSettings $settings = null;

    public function __construct()
    {
        parent::__construct();
    }


    protected function getNextVersion(?string $current): ?string
    {
        return match ($current) {
            null => 'v2',
            'v2' => 'v3',
            'v3' => "turnstile",
            'turnstile' => null,
            default => null,
        };
    }

    public function handle(): int
    {
        try {
            $settings = $this->settings();
            $current = $settings->recaptcha_version;
            $next = $this->getNextVersion($current);


            $settings->recaptcha_version = $next;
            $settings->save();

            $this->info("Recaptcha version is now: " . ($next ?? 'disabled') . ". Run again to set it to " . ($this->getNextVersion($next) ?? 'disabled') . ".");
        } catch (Exception $e) {
            $this->error('An error occurred: ' . $e->getMessage());
            Log::error($e);
        }

        return Command::SUCCESS;
    }

    private function settings(): GeneralSettings
    {
        return $this->settings ??= app(GeneralSettings::class);
    }
}
