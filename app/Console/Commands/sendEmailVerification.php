<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class sendEmailVerification extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'email:send {user}';

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
     * @return int
     */
    public function handle()
    {
        echo $this->argument('user');
        User::find($this->argument('user'))->get()[0]->sendEmailVerificationNotification();
        return 0;
    }
}
