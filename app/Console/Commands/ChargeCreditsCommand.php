<?php

namespace App\Console\Commands;

use App\Classes\Pterodactyl;
use App\Models\Server;
use Carbon\Carbon;
use Illuminate\Console\Command;

class ChargeCreditsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'credits:charge';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Charge all users with active servers';

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
     * @return string
     */
    public function handle()
    {
       Server::chunk(10, function ($servers) {
           /** @var Server $server */
           foreach ($servers as $server) {

               //ignore suspended servers
                if ($server->isSuspended()) {
                    echo Carbon::now()->isoFormat('LLL') .  " Ignoring suspended server";
                    continue;
                }

                //vars
                $user = $server->user;
                $price = ($server->product->price / 30) / 24;


                //remove credits or suspend server
                if ($user->credits >= $price) {
                    $user->decrement('credits', $price);

                    //log
                    echo Carbon::now()->isoFormat('LLL') .  " [CREDIT DEDUCTION] Removed " . number_format($price, 2, '.', '') . " from user (" . $user->name . ") for server (" . $server->name . ")\n";

                } else {
                    $response = Pterodactyl::client()->post("/application/servers/{$server->pterodactyl_id}/suspend");

                    if ($response->successful()) {
                        echo Carbon::now()->isoFormat('LLL') .  " [CREDIT DEDUCTION] Suspended server (" . $server->name . ") from user (" . $user->name . ")\n";
                        $server->update(['suspended' => now()]);
                    } else {
                        echo Carbon::now()->isoFormat('LLL') .  " [CREDIT DEDUCTION] CRITICAL ERROR! Unable to suspend server (" . $server->name . ") from user (" . $user->name . ")\n";
                        dump($response->json());
                    }
                }

            }
        });

        return 'Charged credits for existing servers!\n';
    }
}
