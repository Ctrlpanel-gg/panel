<?php

namespace App\Console\Commands;

use App\Classes\PterodactylClient;
use App\Exceptions\Pterodactyl\PterodactylException;
use App\Models\User;
use App\Settings\PterodactylSettings;
use App\Settings\UserSettings;
use App\Traits\Referral;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class MakeUserCommand extends Command
{
    use Referral;

    private $pterodactyl;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:user {--ptero_id=} {--password=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create an admin account with the Artisan Console';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }


    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(PterodactylSettings $ptero_settings, UserSettings $user_settings)
    {
        $this->pterodactyl = new PterodactylClient($ptero_settings);
        $ptero_id = $this->option('ptero_id') ?? $this->ask('Please specify your Pterodactyl ID.');
        $password = $this->secret('password') ?? $this->ask('Please specify your password.');

        // Validate user input
        $validator = Validator::make([
            'ptero_id' => $ptero_id,
            'password' => $password,
        ], [
            'ptero_id' => 'required|numeric|integer|min:1|max:2147483647',
            'password' => 'required|string|min:8|max:60',
        ]);

        if ($validator->fails()) {
            $this->error($validator->errors()->first());

            return 0;
        }

        // Fetch the user from Pterodactyl. On failure, getUser() throws a
        // typed PterodactylException carrying the HTTP status code.
        try {
            $response = $this->pterodactyl->getUser($ptero_id);
        } catch (PterodactylException $e) {
            $this->printPterodactylError($e->getStatusCode(), $e->getMessage());

            return 0;
        } catch (\Exception $e) {
            $this->error('Failed to fetch the user from Pterodactyl.');
            $this->line($e->getMessage());

            return 0;
        }

        $exists = User::where('email', $response['email'])
            ->orWhere('pterodactyl_id', $response['id'])
            ->exists();

        if ($exists) {
            $this->error('A user with this email or Pterodactyl ID already exists.');
            return 0;
        }

        $user = User::create([
            'name' => $response['first_name'],
            'email' => $response['email'],
            'password' => Hash::make($password),
            'credits' => $user_settings->initial_credits,
            'server_limit' => $user_settings->initial_server_limit,
            'referral_code' => $this->createReferralCode(),
            'pterodactyl_id' => $response['id'],
        ]);

        $this->table(['Field', 'Value'], [
            ['ID', $user->id],
            ['Email', $user->email],
            ['Username', $user->name],
            ['Ptero-ID', $user->pterodactyl_id],
            ['Referral code', $user->referral_code],
        ]);

        $user->syncRoles(1);

        return 1;
    }

    /**
     * Print a clean, human-readable error for a failed Pterodactyl request.
     *
     * @param  int|null  $statusCode
     * @param  string  $detail
     * @return void
     */
    private function printPterodactylError(?int $statusCode, string $detail): void
    {
        $hints = [
            400 => 'The request was invalid. Check the Pterodactyl ID and try again.',
            401 => 'The Pterodactyl API token is missing or invalid. Check the token in the panel settings.',
            403 => 'The Pterodactyl API token does not have permission to access this resource.',
            404 => 'No user with this Pterodactyl ID was found. Double-check the ID.',
            422 => 'Pterodactyl rejected the request due to validation errors.',
            429 => 'Too many requests to Pterodactyl. Wait a moment and try again.',
            500 => 'Pterodactyl encountered an internal server error. Try again later.',
            502 => 'Pterodactyl is down or unreachable. Check that the panel is online.',
            503 => 'Pterodactyl is temporarily unavailable. Check that the panel is online.',
        ];

        $this->error('Failed to fetch the user from Pterodactyl (HTTP ' . ($statusCode ?? 'unknown') . ').');
        $this->line($hints[$statusCode] ?? 'An unexpected error occurred while contacting Pterodactyl.');
        $this->line($detail);
    }
}
