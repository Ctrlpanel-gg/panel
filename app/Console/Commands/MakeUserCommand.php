<?php

namespace App\Console\Commands;

use App\Classes\Pterodactyl;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class MakeUserCommand extends Command
{
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

    private Pterodactyl $pterodactyl;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(Pterodactyl $pterodactyl)
    {
        parent::__construct();
        $this->pterodactyl = $pterodactyl;
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $ptero_id = $this->option('ptero_id') ?? $this->ask('Please specify your Pterodactyl ID.');
        $password = $this->option('password') ?? $this->ask('Please specify your password.');

        if (strlen($password) < 8) {
            $this->alert('Your password need to be at least 8 characters long');
            return 0;
        }

        //TODO: Do something with response (check for status code and give hints based upon that)
        $response = $this->pterodactyl->getUser($ptero_id);

        if ($response === []) {
            $this->alert('It seems that your Pterodactyl ID is not correct. Rerun the command and input an correct ID');
            return 0;
        }

        $user = User::create([
            'name'           => $response['first_name'],
            'email'          => $response['email'],
            'role'           => 'admin',
            'password'       => Hash::make($password),
            'pterodactyl_id' => $response['id']
        ]);

        $this->table(['Field', 'Value'], [
            ['ID', $user->id],
            ['Email', $user->email],
            ['Username', $user->name],
            ['Ptero-ID', $user->pterodactyl_id],
            ['Admin', $user->role],
        ]);

        return 1;
    }
}
