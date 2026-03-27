<?php

namespace App\Console\Commands;

use App\Models\Product;
use App\Models\Server;
use App\Models\User;
use App\Notifications\ServersSuspendedNotification;
use Carbon\Carbon;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ChargeServers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'servers:charge';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Charge all users with severs that are due to be charged';

    /**
     * A list of users that have to be notified
     * @var array
     */
    protected $usersToNotify = [];

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
        Server::whereNull('suspended')
            ->with(['user', 'product'])
            ->byBillingPriority()
            ->chunk(10, function ($servers) {
                /** @var Server $server */
                foreach ($servers as $server) {
                    /** @var Product $product */
                    $product = $server->product;
                    /** @var User $user */
                    $user = $server->user;

                    $billing_period = $product->billing_period;

                    // check if server is due to be charged by comparing its last_billed date with the current date and the billing period
                    $newBillingDate = null;
                    switch ($billing_period) {
                        case 'annually':
                            $newBillingDate = Carbon::parse($server->last_billed)->addYear();
                            break;
                        case 'half-annually':
                            $newBillingDate = Carbon::parse($server->last_billed)->addMonths(6);
                            break;
                        case 'quarterly':
                            $newBillingDate = Carbon::parse($server->last_billed)->addMonths(3);
                            break;
                        case 'monthly':
                            $newBillingDate = Carbon::parse($server->last_billed)->addMonth();
                            break;
                        case 'weekly':
                            $newBillingDate = Carbon::parse($server->last_billed)->addWeek();
                            break;
                        case 'daily':
                            $newBillingDate = Carbon::parse($server->last_billed)->addDay();
                            break;
                        case 'hourly':
                            $newBillingDate = Carbon::parse($server->last_billed)->addHour();
                        default:
                            $newBillingDate = Carbon::parse($server->last_billed)->addHour();
                            break;
                    }

                    if (!($newBillingDate->isPast())) {
                        continue;
                    }


                    try {
                        $chargeResult = DB::transaction(function () use ($server) {
                            $lockedServer = Server::query()->lockForUpdate()->with(['product', 'user'])->find($server->id);

                            if (! $lockedServer || $lockedServer->suspended !== null) {
                                return 'skip';
                            }

                            $lockedProduct = $lockedServer->product;
                            $lockedUser = User::query()->lockForUpdate()->findOrFail($lockedServer->user_id);

                            $nextBillingDate = match ($lockedProduct->billing_period) {
                                'annually' => Carbon::parse($lockedServer->last_billed)->addYear(),
                                'half-annually' => Carbon::parse($lockedServer->last_billed)->addMonths(6),
                                'quarterly' => Carbon::parse($lockedServer->last_billed)->addMonths(3),
                                'monthly' => Carbon::parse($lockedServer->last_billed)->addMonth(),
                                'weekly' => Carbon::parse($lockedServer->last_billed)->addWeek(),
                                'daily' => Carbon::parse($lockedServer->last_billed)->addDay(),
                                default => Carbon::parse($lockedServer->last_billed)->addHour(),
                            };

                            if (! $nextBillingDate->isPast()) {
                                return 'skip';
                            }

                            if ($lockedServer->canceled) {
                                return 'canceled';
                            }

                            if ($lockedUser->credits < $lockedProduct->price && $lockedProduct->price != 0) {
                                return 'insufficient_credits';
                            }

                            $this->line("<fg=blue>{$lockedUser->name}</> Current credits: <fg=green>{$lockedUser->credits}</> Credits to be removed: <fg=red>{$lockedProduct->price}</>");
                            $lockedUser->decrement('credits', $lockedProduct->price);
                            $lockedServer->update(['last_billed' => $nextBillingDate]);

                            return 'charged';
                        });

                        if ($chargeResult === 'insufficient_credits') {
                            $this->suspendFunc($server, $user);
                        }
                    } catch (Exception $exception) {
                        $this->error($exception->getMessage());
                    }
                }

                return $this->notifyUsers();
            });

        return Command::SUCCESS;
    }

    public function suspendFunc($server, $user)
    {
        // suspend server
        $this->line("<fg=yellow>{$server->name}</> from user: <fg=blue>{$user->name}</> has been <fg=red>suspended!</>");
        $server->suspend();

        // add user to notify list
        if (!in_array($user, $this->usersToNotify)) {
            array_push($this->usersToNotify, $user);
        }
    }

    /**
     * @return bool
     */
    public function notifyUsers()
    {
        if (!empty($this->usersToNotify)) {
            /** @var User $user */
            foreach ($this->usersToNotify as $user) {
                $suspendServers = $user->servers()->whereNotNull('suspended')->get();

                $this->line("<fg=yellow>Notified user:</> <fg=blue>{$user->name}</>");
                $user->notify(new ServersSuspendedNotification($suspendServers));
            }
        }

        #reset array
        $this->usersToNotify = array();
        return true;
    }
}
