<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Services\TwoFactor\TwoFactorService;
use Illuminate\Console\Command;

class DisableTwoFactorCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cp:user:2fa:disable {search? : The ID, Email, Username or Discord ID of the user}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Forcibly disable 2FA methods for a user';

    protected $twoFactorService;

    public function __construct(TwoFactorService $twoFactorService)
    {
        parent::__construct();
        $this->twoFactorService = $twoFactorService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $search = $this->argument('search');

        if (!$search) {
            $search = $this->ask('Please enter User ID, Email, Username or Discord ID');
        }

        if (!$search) {
            $this->error('No search term provided.');
            return 1;
        }

        $user = User::query()
            ->where('id', $search)
            ->orWhere('email', $search)
            ->orWhere('name', $search)
            ->orWhereHas('discordUser', function ($query) use ($search) {
                $query->where('id', $search);
            })
            ->first();

        if (!$user) {
            $this->error("User not found with term: {$search}");
            return 1;
        }

        $this->info("Found User: {$user->name} ({$user->email}) [ID: {$user->id}]");

        $methods = $user->twoFactorMethods()->where('is_enabled', true)->get();

        if ($methods->isEmpty()) {
            $this->warn('This user does not have any 2FA methods enabled.');
            return 0;
        }

        $choices = $methods->mapWithKeys(function ($m) {
            $label = $this->twoFactorService->getExtension($m->method)?->getLabel() ?? ucfirst($m->method);
            return [$m->method => "{$label} ({$m->method})"];
        })->toArray();

        $choices['all'] = 'Disable ALL methods';

        $selected = $this->choice(
            'Which 2FA methods do you want to disable?',
            $choices,
            null,
            null,
            true // Multiple selection
        );

        if (empty($selected)) {
            $this->info('Nothing selected. Aborting.');
            return 0;
        }

        if (in_array('Disable ALL methods', $selected) || in_array('all', $selected)) {
            if ($this->confirm("Are you sure you want to disable ALL 2FA methods for {$user->name}?", true)) {
                $user->twoFactorMethods()->delete();
                $this->twoFactorService->clearVerified(request(), $user);
                $this->success("Successfully disabled all 2FA methods for {$user->name}.");
            }
            return 0;
        }

        // Map back titles to keys if necessary (Laravel's choice with multiple can return values or keys depending on version/selection)
        $methodKeys = [];
        foreach ($selected as $choice) {
            $key = array_search($choice, $choices);
            if ($key !== false) {
                $methodKeys[] = $key;
            } else {
                // If it already returned the key
                if (isset($choices[$choice])) {
                    $methodKeys[] = $choice;
                }
            }
        }

        if ($this->confirm("Disable selected methods: " . implode(', ', $methodKeys) . "?", true)) {
            $user->twoFactorMethods()->whereIn('method', $methodKeys)->delete();

            // If we disabled everything, clear verified state
            if ($user->twoFactorMethods()->where('is_enabled', true)->count() === 0) {
                $this->twoFactorService->clearVerified(request(), $user);
            }

            $this->success("Successfully disabled " . implode(', ', $methodKeys) . " for {$user->name}.");
        }

        return 0;
    }

    protected function success($message)
    {
        $this->output->writeln("<fg=green;options=bold> SUCCESS </> $message");
    }
}
