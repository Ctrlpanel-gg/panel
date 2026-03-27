<?php

namespace App\Http\Controllers\Admin;

use App\Classes\PterodactylClient;
use App\Helpers\CurrencyHelper;
use App\Settings\PterodactylSettings;
use App\Settings\GeneralSettings;
use App\Http\Controllers\Controller;
use App\Models\Pterodactyl\Egg;
use App\Models\Pterodactyl\Location;
use App\Models\Pterodactyl\Nest;
use App\Models\Pterodactyl\Node;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Server;
use App\Models\Ticket;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class OverViewController extends Controller
{
    const READ_PERMISSION = "admin.overview.read";
    const SYNC_PERMISSION = "admin.overview.sync";
    public const TTL = 86400;

    private $pterodactyl;

    public function __construct(PterodactylSettings $ptero_settings)
    {
        $this->pterodactyl = new PterodactylClient($ptero_settings);
    }

    public function index(GeneralSettings $general_settings, CurrencyHelper $currencyHelper)
    {
        $this->checkAnyPermission([self::READ_PERMISSION,self::SYNC_PERMISSION]);

        //Get counters
        $counters = collect();
        //Set basic variables in the collection
        $counters->put('users', collect());
        $counters['users']->active = User::where("suspended", 0)->count();
        $counters['users']->total = User::query()->count();
        $counters->put('credits', $currencyHelper->formatForDisplay(User::query()->whereHas("roles", function($q){ $q->where("id", "!=", "1"); })->sum('credits')));
        $counters->put('payments', Payment::query()->count());
        $counters->put('eggs', Egg::query()->count());
        $counters->put('nests', Nest::query()->count());
        $counters->put('locations', Location::query()->count());

        //Prepare for counting
        $counters->put('servers', collect());
        $counters['servers']->active = 0;
        $counters['servers']->total = 0;
        $counters->put('earnings', collect());
        $counters['earnings']->active = 0;
        $counters['earnings']->total = 0;
        $counters->put('totalUsagePercent', 0);

        //Prepare subCollection 'payments'
        $counters->put('payments', collect());
        //Prepare collections
        $currentMonthStart = Carbon::today()->startOfMonth();
        $nextMonthStart = (clone $currentMonthStart)->addMonth();
        $previousMonthStart = (clone $currentMonthStart)->subMonth();
        $counters['payments']->put('thisMonth', $this->paymentSummary($currentMonthStart, $nextMonthStart));
        $counters['payments']->put('lastMonth', $this->paymentSummary($previousMonthStart, $currentMonthStart));


        //Prepare subCollection 'taxPayments'
        $counters->put('taxPayments', collect());
        //Prepare collections
        $currentYearStart = Carbon::today()->startOfYear();
        $nextYearStart = (clone $currentYearStart)->addYear();
        $previousYearStart = (clone $currentYearStart)->subYear();
        $counters['taxPayments']->put('thisYear', $this->taxPaymentSummary($currentYearStart, $nextYearStart));
        $counters['taxPayments']->put('lastYear', $this->taxPaymentSummary($previousYearStart, $currentYearStart));

        //sort currencies alphabetically and set some additional variables
        $counters['payments']['thisMonth'] = $counters['payments']['thisMonth']->sortKeys();
        $counters['payments']['thisMonth']->timeStart = $currentMonthStart->toDateString();
        $counters['payments']['thisMonth']->timeEnd = Carbon::today()->toDateString();
        $counters['payments']['lastMonth'] = $counters['payments']['lastMonth']->sortKeys();
        $counters['payments']['lastMonth']->timeStart = $previousMonthStart->toDateString();
        $counters['payments']['lastMonth']->timeEnd = (clone $currentMonthStart)->subDay()->toDateString();
        $counters['payments']->total = Payment::query()->count();

        //sort currencies alphabetically and set some additional variables
        $counters['taxPayments']['thisYear'] = $counters['taxPayments']['thisYear']->sortKeys();
        $counters['taxPayments']['thisYear']->timeStart = $currentYearStart->toDateString();
        $counters['taxPayments']['thisYear']->timeEnd = Carbon::today()->toDateString();
        $counters['taxPayments']['lastYear'] = $counters['taxPayments']['lastYear']->sortKeys();
        $counters['taxPayments']['lastYear']->timeStart = $previousYearStart->toDateString();
        $counters['taxPayments']['lastYear']->timeEnd = (clone $currentYearStart)->subDay()->toDateString();

        $lastEgg = Egg::query()->latest('updated_at')->first();
        $syncLastUpdate = $lastEgg ? $lastEgg->updated_at->isoFormat('LLL') : __('unknown');

        //Get node information and prepare collection
        $pteroNodes = collect($this->pterodactyl->getNodes())
            ->map(fn ($node) => data_get($node, 'attributes'))
            ->filter(fn ($node) => is_array($node) && isset($node['id']))
            ->keyBy('id');
        $pteroNodeIds = $pteroNodes->keys()->all();
        $nodes = collect();
        $DBnodes = Node::query()->get();
        foreach ($DBnodes as $DBnode) { //gets all node information and prepares the structure
            $nodeId = $DBnode['id'];
            if (! $pteroNodes->has($nodeId)) {
                continue;
            } //Check if node exists on pterodactyl too, if not, skip
            $nodes->put($nodeId, collect());
            $nodes[$nodeId]->name = $DBnode['name'];
            $pteroNode = $pteroNodes->get($nodeId);
            $nodes[$nodeId]->usagePercent = $this->calculateNodeUsagePercent($pteroNode);
            $counters['totalUsagePercent'] += $nodes[$nodeId]->usagePercent;

            $nodes[$nodeId]->totalServers = 0;
            $nodes[$nodeId]->activeServers = 0;
            $nodes[$nodeId]->totalEarnings = 0;
            $nodes[$nodeId]->activeEarnings = 0;
        }
        $counters['totalUsagePercent'] = ($nodes->count()) ? round($counters['totalUsagePercent'] / $nodes->count(), 2) : 0;

        $remoteServers = collect($this->pterodactyl->getServers());
        $cpServers = Server::query()
            ->with('product')
            ->whereIn('pterodactyl_id', $remoteServers->pluck('attributes.id')->filter()->all())
            ->get()
            ->keyBy('pterodactyl_id');

        foreach ($remoteServers as $server) { //gets all servers from Pterodactyl and calculates total of credit usage for each node separately + total
            $serverAttributes = data_get($server, 'attributes');

            if (! is_array($serverAttributes)) {
                continue;
            }

            $nodeId = data_get($serverAttributes, 'node');
            $cpServer = $cpServers->get(data_get($serverAttributes, 'id'));

            if (! $cpServer || ! $cpServer->product || ! $nodes->has($nodeId)) {
                continue;
            }

            $price = $cpServer->product->getMonthlyPrice();

            if (! $cpServer->suspended) {
                $counters['earnings']->active += $price;
                $counters['servers']->active++;
                $nodes[$nodeId]->activeEarnings += $price;
                $nodes[$nodeId]->activeServers++;
            }

            $counters['earnings']->total += $price;
            $counters['servers']->total++;
            $nodes[$nodeId]->totalEarnings += $price;
            $nodes[$nodeId]->totalServers++;
        }

        //Get latest tickets
        $tickets = collect();
        foreach (Ticket::query()->with('user')->latest()->take(5)->get() as $ticket) {
            $tickets->put($ticket->ticket_id, collect());
            $tickets[$ticket->ticket_id]->title = $ticket->title;
            $user = $ticket->user;
            $tickets[$ticket->ticket_id]->user_id = $user?->id;
            $tickets[$ticket->ticket_id]->user = $user?->name ?? __('Deleted user');
            $tickets[$ticket->ticket_id]->status = $ticket->status;
            $tickets[$ticket->ticket_id]->last_updated = $ticket->updated_at->diffForHumans();
            switch ($ticket->status) {
                case 'Open':
                    $tickets[$ticket->ticket_id]->statusBadgeColor = 'badge-success';
                    break;
                case 'Closed':
                    $tickets[$ticket->ticket_id]->statusBadgeColor = 'badge-danger';
                    break;
                case 'Answered':
                    $tickets[$ticket->ticket_id]->statusBadgeColor = 'badge-info';
                    break;
                default:
                    $tickets[$ticket->ticket_id]->statusBadgeColor = 'badge-warning';
                    break;
            }
        }

        return view('admin.overview.index', [
            'counters' => $counters,
            'nodes' => $nodes,
            'syncLastUpdate' => $syncLastUpdate,
            'deletedNodesPresent' => ($DBnodes->count() != count($pteroNodeIds)) ? true : false,
            'perPageLimit' => ($remoteServers->count() != Server::query()->count()) ? true : false,
            'tickets' => $tickets,
            'credits_display_name' => $general_settings->credits_display_name
        ]);
    }

    /**
     * @description Sync locations,nodes,nests,eggs with the linked pterodactyl panel
     */
    public function syncPterodactyl()
    {
        $this->checkPermission(self::SYNC_PERMISSION);

        Node::syncNodes();
        Egg::syncEggs();

        return redirect()->back()->with('success', __('Pterodactyl synced'));
    }

    private function paymentSummary(Carbon $from, Carbon $to): Collection
    {
        $summary = collect();

        $rows = Payment::query()
            ->selectRaw('currency_code, SUM(total_price) as total, COUNT(*) as payment_count')
            ->where('status', 'paid')
            ->where('created_at', '>=', $from)
            ->where('created_at', '<', $to)
            ->groupBy('currency_code')
            ->orderBy('currency_code')
            ->get();

        foreach ($rows as $row) {
            $summary->put($row->currency_code, collect([
                'total' => (int) $row->total,
                'count' => (int) $row->payment_count,
            ]));
        }

        return $summary;
    }

    private function taxPaymentSummary(Carbon $from, Carbon $to): Collection
    {
        $summary = collect();

        $rows = Payment::query()
            ->selectRaw('currency_code, SUM(total_price) as total, COUNT(*) as payment_count, SUM(price) as price_total, SUM(tax_value) as tax_total')
            ->where('status', 'paid')
            ->where('created_at', '>=', $from)
            ->where('created_at', '<', $to)
            ->groupBy('currency_code')
            ->orderBy('currency_code')
            ->get();

        foreach ($rows as $row) {
            $summary->put($row->currency_code, collect([
                'total' => (int) $row->total,
                'count' => (int) $row->payment_count,
                'price' => (int) $row->price_total,
                'taxes' => (int) $row->tax_total,
            ]));
        }

        return $summary;
    }

    private function calculateNodeUsagePercent(array $node): float
    {
        $memoryCapacity = (float) data_get($node, 'memory', 0) * (((float) data_get($node, 'memory_overallocate', 0) + 100) / 100);
        $diskCapacity = (float) data_get($node, 'disk', 0) * (((float) data_get($node, 'disk_overallocate', 0) + 100) / 100);
        $allocatedMemory = (float) data_get($node, 'allocated_resources.memory', 0);
        $allocatedDisk = (float) data_get($node, 'allocated_resources.disk', 0);

        $memoryUsage = $memoryCapacity > 0 ? ($allocatedMemory / $memoryCapacity) : 0;
        $diskUsage = $diskCapacity > 0 ? ($allocatedDisk / $diskCapacity) : 0;

        return round(max($memoryUsage, $diskUsage) * 100, 2);
    }
}
