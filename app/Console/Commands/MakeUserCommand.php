<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Classes\Pterodactyl;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class createUser extends Command
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

        $ptero_id = $this->option('ptero_id') ?? $this->ask('Please specify your Pterodactyl ID.');

        $password = $this->option('password') ?? $this->ask('Please specify your password.');
        
        
        if (strlen($password) < 8) {
            print_r('Your password need to be atleast 8 characters long');

            return false;
        };
        
        $response = Pterodactyl::getUser($ptero_id);


        if (is_null($response)) {
            print_r('It seems that your Pterodactyl ID isnt correct. Rerun the command and input an correct ID');

            return false;
        };

        $user = User::create([
            'name'         => $response['first_name'],
            'email'        => $response['email'],
            'role'         => 'admin',
            'password'     => Hash::make($password),
            'pterodactyl_id' => $response['id']
        ]);

        $this->table(['Field', 'Value'], [
            ['ID', $user->id],
            ['Email', $user->email],
            ['Username', $user->name],
            ['Ptero-ID', $user->pterodactyl_id],
            ['Admin', $user->role],
        ]);
        return true;
    }
}
