<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class Disable2FA extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cp:user:2fa:disable {user : The ID or Email of the user}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Forcibly disable 2FA for a specific user';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $userInput = $this->argument('user');

        $user = User::where('id', $userInput)
            ->orWhere('email', $userInput)
            ->first();

        if (!$user) {
            $this->error("User with ID or Email '{$userInput}' not found.");
            return 1;
        }

        if (!$user->two_factor_enabled) {
            $this->info("2FA is already disabled for user: {$user->name} ({$user->email})");
            return 0;
        }

        if (!$this->confirm("Are you sure you want to disable 2FA for {$user->name} ({$user->email})?", true)) {
            $this->info('Action cancelled.');
            return 0;
        }

        $user->forceFill([
            'two_factor_enabled' => false,
            'two_factor_secret' => null,
            'two_factor_recovery_codes' => null,
            'two_factor_confirmed_at' => null,
        ])->save();

        $this->info("Successfully disabled 2FA for user: {$user->name}");

        return 0;
    }
}
