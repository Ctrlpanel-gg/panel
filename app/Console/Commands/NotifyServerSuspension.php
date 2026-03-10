<?php

namespace App\Console\Commands;

use App\Helpers\CurrencyHelper;
use App\Models\Product;
use App\Models\Server;
use App\Models\User;
use App\Notifications\ServerSuspensionWarningNotification;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
class NotifyServerSuspension extends Command
{
    /**
     * @var string
     */
    protected $signature = 'servers:notify-suspension';

    /**
     * @var string
     */
    protected $description = 'Notify users 3 days before their servers are suspended';

    /**
     * @var array<int, array{user: User, servers: \Illuminate\Support\Collection}>
     */
    protected $usersToNotify = [];

    public function __construct(protected CurrencyHelper $currencyHelper)
    {
        parent::__construct();
    }

    public function handle()
    {
        $serversChecked = 0;
        $serversNotified = 0;

        // Clear warnings for servers that are no longer at risk.
        $this->clearResolvedWarnings();

        Server::whereNull('suspended')
            ->whereNull('suspension_warning_sent_at')
            ->with(['user', 'product'])
            ->byBillingPriority()
            ->chunk(10, function ($servers) use (&$serversChecked, &$serversNotified) {
                /** @var Server $server */
                foreach ($servers as $server) {
                    $serversChecked++;

                    /** @var Product|null $product */
                    $product = $server->product;
                    /** @var User|null $user */
                    $user = $server->user;

                    if (!$product || !$user) {
                        continue;
                    }

                    $suspensionDate = $this->getSuspensionDate($server, $product->billing_period);

                    if (!$this->hasInsufficientCredits($user, $product)) {
                        continue;
                    }

                    $daysUntilSuspension = Carbon::now()->diffInDays($suspensionDate, false);

                    if ($daysUntilSuspension > 0 && $daysUntilSuspension <= 3) {
                        $this->line("<fg=yellow>{$server->name}</> from user: <fg=blue>{$user->name}</> will be suspended in <fg=cyan>{$daysUntilSuspension}</> days. Queued warning...");

                        $serversNotified++;

                        if (!isset($this->usersToNotify[$user->id])) {
                            $this->usersToNotify[$user->id] = [
                                'user' => $user,
                                'servers' => collect(),
                            ];
                        }

                        $this->usersToNotify[$user->id]['servers']->push([
                            'server' => $server,
                            'suspension_date' => $suspensionDate,
                        ]);
                    }
                }
            });

        $this->notifyUsers();

        $this->info("Completed! Checked: {$serversChecked} servers, Sent warnings for: {$serversNotified} servers");

        return 0;
    }

    private function getSuspensionDate(Server $server, string $billingPeriod): Carbon
    {
        return match ($billingPeriod) {
            'annually' => Carbon::parse($server->last_billed)->addYear(),
            'half-annually' => Carbon::parse($server->last_billed)->addMonths(6),
            'quarterly' => Carbon::parse($server->last_billed)->addMonths(3),
            'monthly' => Carbon::parse($server->last_billed)->addMonth(),
            'weekly' => Carbon::parse($server->last_billed)->addWeek(),
            'daily' => Carbon::parse($server->last_billed)->addDay(),
            default => Carbon::parse($server->last_billed)->addHour(),
        };
    }

    private function clearResolvedWarnings(): void
    {
        $clearedCount = 0;

        Server::whereNotNull('suspension_warning_sent_at')
            ->whereNull('suspended')
            ->with(['user', 'product'])
            ->chunk(10, function ($servers) use (&$clearedCount) {
                foreach ($servers as $server) {
                    /** @var Product|null $product */
                    $product = $server->product;
                    /** @var User|null $user */
                    $user = $server->user;

                    if (!$product || !$user) {
                        continue;
                    }

                    if (!$this->hasInsufficientCredits($user, $product)) {
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

    public function notifyUsers(): bool
    {
        if (!empty($this->usersToNotify)) {
            foreach ($this->usersToNotify as $userData) {
                $user = $userData['user'];
                $servers = $userData['servers'];

                if ($servers->isNotEmpty()) {
                    try {
                        $this->line("<fg=yellow>Notified user:</> <fg=blue>{$user->name}</>");
                        $user->notify(new ServerSuspensionWarningNotification($servers));

                        // mark each server as warned only after successful notification
                        foreach ($servers as $entry) {
                            $entry['server']->update(['suspension_warning_sent_at' => now()]);
                        }
                    } catch (\Throwable $e) {
                        Log::error('Failed to notify user ' . $user->id . ' about suspension warning: ' . $e->getMessage());
                        // keep servers unmarked so that next run may retry
                    }
                }
            }
        }

        $this->usersToNotify = [];

        return true;
    }

    private function hasInsufficientCredits($user, $product): bool
    {
        // Direct integer comparison; both values are in thousandths.
        return $user->credits < $product->price && $product->price != 0;
    }
}
