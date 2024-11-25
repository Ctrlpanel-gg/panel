<?php

namespace App\Console\Commands;

use App\Settings\GeneralSettings;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;


class DisableRecaptcha extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cp:recaptcha:toggle';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Toggle Recaptcha on and off';

    /**
     * Execute the console command.
     *
     * @return int
     */

    protected GeneralSettings $settings;

    public function __construct(GeneralSettings $settings)
    {
        parent::__construct();
        $this->settings = $settings;
    }
    public function handle()
    {
        try{

            $this->settings->recaptcha_enabled = !$this->settings->recaptcha_enabled;
            $this->settings->save();
            $this->info('Recaptcha enabled: ' . ($this->settings->recaptcha_enabled ? 'true' : 'false'));

        } catch (Exception $e) {
            $this->error('An error occurred: ' . $e->getMessage());
            Log::error($e);
        }
        return Command::SUCCESS;
    }
}
