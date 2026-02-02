<?php

namespace App\Console\Commands;

use App\Helpers\CurrencyHelper;
use App\Models\Product;
use App\Models\Server;
use App\Models\User;
use App\Notifications\ServerSuspensionWarningNotification;
use Carbon\Carbon;
use Exception;
use Illuminate\Console\Command;

class NotifyServerSuspension extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'servers:notify-suspension';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Notify users 3 days before their servers are suspended';

    /**
     * A list of users and their servers that have to be notified
     * @var array
     */
    protected $usersToNotify = [];

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(protected CurrencyHelper $currencyHelper)
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
        $serversChecked = 0;
        $serversNotified = 0;

        // First, clear warnings for servers that are no longer at risk
        $this->clearResolvedWarnings();

        // Get all servers that are not suspended and haven't been warned yet
        Server::whereNull('suspended')
            ->whereNull('suspension_warning_sent_at')
            ->with(['user', 'product'])
            ->byBillingPriority()
            ->chunk(10, function ($servers) use (&$serversChecked, &$serversNotified) {
                /** @var Server $server */
                foreach ($servers as $server) {
                    $serversChecked++;

                    /** @var Product $product */
                    $product = $server->product;
                    /** @var User $user */
                    $user = $server->user;

                    if (!$product || !$user) {
                        continue;
                    }

                    $billing_period = $product->billing_period;
                    $suspensionDate = null;
                    switch ($billing_period) {
                        case 'annually':
                            $suspensionDate = Carbon::parse($server->last_billed)->addYear();
                            break;
                        case 'half-annually':
                            $suspensionDate = Carbon::parse($server->last_billed)->addMonths(6);
                            break;
                        case 'quarterly':
                            $suspensionDate = Carbon::parse($server->last_billed)->addMonths(3);
                            break;
                        case 'monthly':
                            $suspensionDate = Carbon::parse($server->last_billed)->addMonth();
                            break;
                        case 'weekly':
                            $suspensionDate = Carbon::parse($server->last_billed)->addWeek();
                            break;
                        case 'daily':
                            $suspensionDate = Carbon::parse($server->last_billed)->addDay();
                            break;
                        case 'hourly':
                            $suspensionDate = Carbon::parse($server->last_billed)->addHour();
                            break;
                        default:
                            $suspensionDate = Carbon::parse($server->last_billed)->addHour();
                            break;
                    }
                    $hasInsufficientCredits = $this->hasInsufficientCredits($user, $product);

                    if (!$hasInsufficientCredits) {
                        continue;
                    }

                    $now = Carbon::now();
                    $daysUntilSuspension = $now->diffInDays($suspensionDate, false);

                    if ($daysUntilSuspension > 0 && $daysUntilSuspension <= 3) {
                        $this->line("<fg=yellow>{$server->name}</> from user: <fg=blue>{$user->name}</> will be suspended in <fg=cyan>{$daysUntilSuspension}</> days. Sending warning...");

                        $server->update(['suspension_warning_sent_at' => now()]);
                        $serversNotified++;

                        if (!isset($this->usersToNotify[$user->id])) {
                            $this->usersToNotify[$user->id] = [
                                'user' => $user,
                                'servers' => collect()
                            ];
                        }
                        $this->usersToNotify[$user->id]['servers']->push([
                            'server' => $server,
                            'suspension_date' => $suspensionDate
                        ]);
                    }
                }

                // Notifications will be sent after processing all chunks
            });

        $this->notifyUsers();

        $this->info("Completed! Checked: {$serversChecked} servers, Sent warnings for: {$serversNotified} servers");

        return 0;
    }

    /**
     * Clear suspension warnings for servers that are no longer at risk
     * (i.e., servers that were warned but now have sufficient credits)
     */
    private function clearResolvedWarnings()
    {
        $clearedCount = 0;

        Server::whereNotNull('suspension_warning_sent_at')
            ->whereNull('suspended')
            ->with(['user', 'product'])
            ->chunk(10, function ($servers) use (&$clearedCount) {
                foreach ($servers as $server) {
                    /** @var Product $product */
                    $product = $server->product;
                    /** @var User $user */
                    $user = $server->user;

                    if (!$product || !$user) {
                        continue;
                    }

                    $hasSufficientCredits = !$this->hasInsufficientCredits($user, $product);

                    if ($hasSufficientCredits) {
                        $server->update(['suspension_warning_sent_at' => null]);
                        $clearedCount++;
                        $this->line("<fg=green>{$server->name}</> from user: <fg=blue>{$user->name}</> - Warning cleared (sufficient credits)");
                    }
                }
            });

        if ($clearedCount > 0) {
            $this->info("Cleared warnings for {$clearedCount} servers that now have sufficient credits");
        }
    }

    /**
     * @return bool
     */
    public function notifyUsers()
    {
        if (!empty($this->usersToNotify)) {
            foreach ($this->usersToNotify as $userData) {
                $user = $userData['user'];
                $servers = $userData['servers'];

                if ($servers->isNotEmpty()) {
                    $this->line("<fg=yellow>Notified user:</> <fg=blue>{$user->name}</>");

                    $user->notify(new ServerSuspensionWarningNotification($servers));
                }
            }
        }

        // Reset array
        $this->usersToNotify = [];
        return true;
    }

    /**
     * Check if user has insufficient credits for the product
     *
     * @param $user
     * @param $product
     * @return bool
     */
    private function hasInsufficientCredits($user, $product)
    {
        // Direct integer comparison; both values are in thousandths
        return $user->credits < $product->price && $product->price != 0;
    }
}
