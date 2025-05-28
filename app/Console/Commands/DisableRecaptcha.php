<?php

namespace App\Console\Commands;

use App\Settings\GeneralSettings;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class DisableRecaptcha extends Command
{
    protected $signature = 'cp:recaptcha:toggle';
    protected $description = 'Toggle Recaptcha version between null, v2, and v3';

    protected GeneralSettings $settings;

    public function __construct(GeneralSettings $settings)
    {
        parent::__construct();
        $this->settings = $settings;
    }


    protected function getNextVersion(?string $current): ?string
    {
        return match ($current) {
            null => 'v2',
            'v2' => 'v3',
            'v3' => null,
            default => null,
        };
    }

    public function handle(): int
    {
        try {
            $current = $this->settings->recaptcha_version;
            $next = $this->getNextVersion($current);


            $this->settings->recaptcha_version = $next;
            $this->settings->save();

            $this->info("Recaptcha version is now: " . ($next ?? 'disabled') . ". Run again to set it to " . ($this->getNextVersion($next) ?? 'disabled') . ".");
        } catch (Exception $e) {
            $this->error('An error occurred: ' . $e->getMessage());
            Log::error($e);
        }

        return Command::SUCCESS;
    }
}
