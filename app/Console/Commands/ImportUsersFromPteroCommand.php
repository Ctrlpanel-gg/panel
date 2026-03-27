<?php

namespace App\Console\Commands;

use App\Models\User;
use JsonException;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Spatie\Permission\Models\Role;

class ImportUsersFromPteroCommand extends Command
{
    /**
     * @var string
     */
    private $importFileName = 'users.json';

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:users {--initial_credits=} {--initial_server_limit=} {--confirm=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import users from a Pterodactyl export payload';

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
    public function handle()
    {

        //check if json file exists
        if (! Storage::disk('local')->exists('users.json')) {
            $this->error('[ERROR] '.storage_path('app').'/'.$this->importFileName.' is missing');

            return Command::FAILURE;
        }

        try {
            $json = json_decode(Storage::disk('local')->get('users.json'), false, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $exception) {
            $this->error('[ERROR] Invalid json file: ' . $exception->getMessage());

            return Command::FAILURE;
        }

        $users = $this->extractUsersFromPayload($json);
        if ($users === []) {
            $this->error('[ERROR] Invalid json file / No users found!');

            return Command::FAILURE;
        }

        //ask questions :)
        $initial_credits = $this->option('initial_credits') ?? $this->ask('Please specify the amount of starting credits users should get. ');
        $initial_server_limit = $this->option('initial_server_limit') ?? $this->ask('Please specify the initial server limit users should get.');
        $confirm = strtolower($this->option('confirm') ?? $this->ask('[y/n] Are you sure you want to import users from the JSON file?'));

        //cancel
        if ($confirm !== 'y') {
            $this->error('[ERROR] Stopped import script!');

            return Command::INVALID;
        }

        $validationErrors = $this->validateImportPayload($users, $initial_credits, $initial_server_limit);
        if ($validationErrors !== []) {
            foreach ($validationErrors as $error) {
                $this->error('[ERROR] ' . $error);
            }

            return Command::FAILURE;
        }

        if (User::query()->exists()) {
            $this->error('[ERROR] Import aborted. The database already contains users, and this command no longer deletes existing accounts automatically.');

            return Command::FAILURE;
        }

        //import users
        $this->importUsingJsonFile($users, $initial_credits, $initial_server_limit);

        return Command::SUCCESS;
    }

    /**
     * @param $json
     * @param $initial_credits
     * @param $initial_server_limit
     * @return void
     */
    private function importUsingJsonFile(array $users, $initial_credits, $initial_server_limit)
    {
        DB::transaction(function () use ($users, $initial_credits, $initial_server_limit) {
            $this->withProgressBar($users, function ($user) use ($initial_server_limit, $initial_credits) {
                $importedUser = User::create([
                    'pterodactyl_id' => $user->id,
                    'name' => $user->name_first,
                    'email' => $user->email,
                    'password' => $user->password,
                    'credits' => $initial_credits,
                    'server_limit' => $initial_server_limit,
                    'created_at' => $user->created_at,
                    'updated_at' => $user->updated_at,
                ]);

                $roleName = $user->root_admin == '0' ? 'User' : 'Admin';
                $role = Role::query()->where('name', $roleName)->first();
                if ($role) {
                    $importedUser->syncRoles($role);
                }
            });
        });

        $this->newLine();
        $this->line('Done importing, you can now login using your pterodactyl credentials.');
        $this->newLine();
    }

    private function validateImportPayload(array $users, $initial_credits, $initial_server_limit): array
    {
        $errors = [];
        $seenEmails = [];
        $seenPterodactylIds = [];

        foreach ($users as $index => $user) {
            $validator = Validator::make((array) $user, [
                'id' => 'required|integer',
                'email' => 'required|email',
                'name_first' => 'required|string|min:1|max:255',
                'password' => 'required|string|min:1',
                'root_admin' => 'required',
            ]);

            if ($validator->fails()) {
                foreach ($validator->errors()->all() as $error) {
                    $errors[] = "Row {$index}: {$error}";
                }
            }

            if (in_array($user->email, $seenEmails, true)) {
                $errors[] = "Row {$index}: duplicate email {$user->email}";
            }

            if (in_array($user->id, $seenPterodactylIds, true)) {
                $errors[] = "Row {$index}: duplicate pterodactyl id {$user->id}";
            }

            if (password_get_info($user->password)['algo'] === null) {
                $errors[] = "Row {$index}: password is not a supported password hash.";
            }

            $seenEmails[] = $user->email;
            $seenPterodactylIds[] = $user->id;
        }

        if (! is_numeric($initial_credits) || ! is_numeric($initial_server_limit)) {
            $errors[] = 'Initial credits and server limit must be numeric.';
        }

        return $errors;
    }

    private function extractUsersFromPayload(mixed $payload): array
    {
        if (is_array($payload) && isset($payload[2]->data) && is_array($payload[2]->data)) {
            return $payload[2]->data;
        }

        if (is_object($payload) && isset($payload->data) && is_array($payload->data)) {
            return $payload->data;
        }

        if (is_object($payload) && isset($payload->users) && is_array($payload->users)) {
            return $payload->users;
        }

        return [];
    }
}
