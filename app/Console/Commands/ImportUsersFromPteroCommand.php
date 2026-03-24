<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

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
    protected $description = 'Command description';

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
     * @return bool
     */
    public function handle()
    {

        //check if json file exists
        if (! Storage::disk('local')->exists('users.json')) {
            $this->error('[ERROR] '.storage_path('app').'/'.$this->importFileName.' is missing');

            return false;
        }

        //check if json file is valid
        $json = json_decode(Storage::disk('local')->get('users.json'));
        if (! array_key_exists(2, $json)) {
            $this->error('[ERROR] Invalid json file');

            return false;
        }
        if (! $json[2]->data) {
            $this->error('[ERROR] Invalid json file / No users found!');

            return false;
        }

        //ask questions :)
        $initial_credits = $this->option('initial_credits') ?? $this->ask('Please specify the amount of starting credits users should get. ');
        $initial_server_limit = $this->option('initial_server_limit') ?? $this->ask('Please specify the initial server limit users should get.');
        $confirm = strtolower($this->option('confirm') ?? $this->ask('[y/n] Are you sure you want to import users from the JSON file?'));

        //cancel
        if ($confirm !== 'y') {
            $this->error('[ERROR] Stopped import script!');

            return false;
        }

        $validationErrors = $this->validateImportPayload($json, $initial_credits, $initial_server_limit);
        if ($validationErrors !== []) {
            foreach ($validationErrors as $error) {
                $this->error('[ERROR] ' . $error);
            }

            return false;
        }

        if (User::query()->exists()) {
            $this->error('[ERROR] Import aborted. The database already contains users, and this command no longer deletes existing accounts automatically.');

            return false;
        }

        //import users
        $this->importUsingJsonFile($json, $initial_credits, $initial_server_limit);

        return true;
    }

    /**
     * @param $json
     * @param $initial_credits
     * @param $initial_server_limit
     * @return void
     */
    private function importUsingJsonFile($json, $initial_credits, $initial_server_limit)
    {
        DB::transaction(function () use ($json, $initial_credits, $initial_server_limit) {
            $this->withProgressBar($json[2]->data, function ($user) use ($initial_server_limit, $initial_credits) {
                $role = $user->root_admin == '0' ? 'member' : 'admin';

                User::create([
                    'pterodactyl_id' => $user->id,
                    'name' => $user->name_first,
                    'email' => $user->email,
                    'password' => $user->password,
                    'role' => $role,
                    'credits' => $initial_credits,
                    'server_limit' => $initial_server_limit,
                    'created_at' => $user->created_at,
                    'updated_at' => $user->updated_at,
                ]);
            });
        });

        $this->newLine();
        $this->line('Done importing, you can now login using your pterodactyl credentials.');
        $this->newLine();
    }

    private function validateImportPayload($json, $initial_credits, $initial_server_limit): array
    {
        $errors = [];
        $seenEmails = [];
        $seenPterodactylIds = [];

        foreach ($json[2]->data as $index => $user) {
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

            $seenEmails[] = $user->email;
            $seenPterodactylIds[] = $user->id;
        }

        if (! is_numeric($initial_credits) || ! is_numeric($initial_server_limit)) {
            $errors[] = 'Initial credits and server limit must be numeric.';
        }

        return $errors;
    }
}
