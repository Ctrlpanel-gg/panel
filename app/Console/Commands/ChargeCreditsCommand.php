<?php

namespace App\Console\Commands;

use App\Classes\Pterodactyl;
use App\Models\Product;
use App\Models\Server;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;

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
        return Server::whereNull('suspended')->chunk(10, function ($servers) {
            /** @var Server $server */
            foreach ($servers as $server) {
                /** @var Product $product */
                $product = $server->product;
                /** @var User $user */
                $user = $server->user;

               #charge credits / suspend server
                if ($user->credits >= $product->getHourlyPrice()){
                    $this->line("<fg=blue>{$user->name}</> Current credits: <fg=green>{$user->credits}</> Credits to be removed: <fg=red>{$product->getHourlyPrice()}</>");
                    $user->decrement('credits', $product->getHourlyPrice());
                } else {
                    $this->line("server <fg=blue>{$server->name}</> <fg=red>has been suspended! </>");
                    $server->suspend();
                }
            }
        });
    }
}
