<?php

namespace App\Console\Commands;

use App\Helpers\CurrencyHelper;
use App\Models\Product;
use App\Models\Server;
use App\Models\User;
use App\Notifications\ServerSuspensionWarningNotification;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
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
        $warningsCleared = 0;

        User::whereHas('servers', fn ($query) => $query->whereNull('suspended'))
            ->with(['servers' => function ($query) {
                $query->whereNull('suspended')
                    ->with('product')
                    ->byBillingPriority();
            }])
            ->chunk(100, function ($users) use (&$serversChecked, &$serversNotified, &$warningsCleared) {
                /** @var User $user */
                foreach ($users as $user) {
                    $analysis = $this->analyzeUserServers($user);

                    $serversChecked += $analysis['checked_servers'];
                    $serversNotified += $this->queueWarningsForUser($user, $analysis['at_risk_entries']);
                    $warningsCleared += $this->clearResolvedWarningsForUser($user, $analysis['at_risk_server_ids']);
                }
            });

        $this->notifyUsers();

        if ($warningsCleared > 0) {
            $this->info("Cleared warnings for {$warningsCleared} servers that are no longer at risk");
        }

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

    /**
     * @return array{checked_servers:int,at_risk_entries:Collection<int, array{server: Server, suspension_date: Carbon}>,at_risk_server_ids:array<int, string>}
     */
    private function analyzeUserServers(User $user): array
    {
        $now = Carbon::now();
        $servers = $user->servers->filter(fn (Server $server) => $server->product !== null && is_null($server->suspended))->values();

        $serverEntries = $servers->map(function (Server $server) {
            /** @var Product $product */
            $product = $server->product;

            return [
                'server' => $server,
                'suspension_date' => $this->getSuspensionDate($server, $product->billing_period),
            ];
        })
            ->sortBy('suspension_date')
            ->values();

        // Simulate billing in chronological order and identify at-risk servers (O(n))
        $remainingCredits = (int) $user->credits;
        $atRiskEntries = collect();
        $atRiskServerIds = [];

        foreach ($serverEntries as $entry) {
            /** @var Server $server */
            $server = $entry['server'];
            /** @var Carbon $suspensionDate */
            $suspensionDate = $entry['suspension_date'];

            if (!is_null($server->canceled)) {
                continue;
            }

            $daysUntilSuspension = $now->diffInDays($suspensionDate, false);
            if ($daysUntilSuspension <= 0 || $daysUntilSuspension > 3) {
                continue;
            }

            /** @var Product $product */
            $product = $server->product;

            // Check if this server would be suspended due to insufficient credits
            if ($remainingCredits < $product->price && $product->price !== 0) {
                $atRiskEntries->push($entry);
                $atRiskServerIds[] = $server->id;
            } else {
                $remainingCredits -= $product->price;
            }
        }

        return [
            'checked_servers' => $servers->count(),
            'at_risk_entries' => $atRiskEntries->values(),
            'at_risk_server_ids' => $atRiskServerIds,
        ];
    }

    /**
     * @param  Collection<int, array{server: Server, suspension_date: Carbon}>  $atRiskEntries
     */
    private function queueWarningsForUser(User $user, Collection $atRiskEntries): int
    {
        $queuedCount = 0;
        $now = Carbon::now();

        foreach ($atRiskEntries as $entry) {
            /** @var Server $server */
            $server = $entry['server'];
            /** @var Carbon $suspensionDate */
            $suspensionDate = $entry['suspension_date'];

            if (!is_null($server->suspension_warning_sent_at)) {
                continue;
            }

            $daysUntilSuspension = $now->diffInDays($suspensionDate, false);
            $this->line("<fg=yellow>{$server->name}</> from user: <fg=blue>{$user->name}</> will be suspended in <fg=cyan>{$daysUntilSuspension}</> days. Queued warning...");

            if (!isset($this->usersToNotify[$user->id])) {
                $this->usersToNotify[$user->id] = [
                    'user' => $user,
                    'servers' => collect(),
                ];
            }

            $this->usersToNotify[$user->id]['servers']->push($entry);
            $queuedCount++;
        }

        return $queuedCount;
    }

    /**
     * @param  array<int, string>  $atRiskServerIds
     */
    private function clearResolvedWarningsForUser(User $user, array $atRiskServerIds): int
    {
        $serversToClear = [];

        // Build a set-like lookup array for at-risk server IDs to avoid O(n * m) in_array checks.
        $atRiskServerIdSet = [];
        foreach ($atRiskServerIds as $id) {
            // Include type information in the key to preserve strict (===) semantics.
            $atRiskServerIdSet[gettype($id) . ':' . $id] = true;
        }

        foreach ($user->servers as $server) {
            if (is_null($server->suspension_warning_sent_at)) {
                continue;
            }

            $lookupKey = gettype($server->id) . ':' . $server->id;
            if (isset($atRiskServerIdSet[$lookupKey])) {
                continue;
            }

            $serversToClear[] = $server->id;
            $this->line("<fg=green>{$server->name}</> from user: <fg=blue>{$user->name}</> - Warning cleared (no longer at risk)");
        }

        if (empty($serversToClear)) {
            return 0;
        }

        return Server::whereIn('id', $serversToClear)->update(['suspension_warning_sent_at' => null]);
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

}
