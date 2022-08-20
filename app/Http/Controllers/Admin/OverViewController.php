<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Egg;
use App\Models\Location;
use App\Models\Nest;
use App\Models\Node;
use App\Models\Payment;
use App\Models\Server;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use App\Classes\Pterodactyl;
use App\Models\Product;
use Carbon\Carbon;

class OverViewController extends Controller
{
    public const TTL = 86400;

    public function index()
    {
        $counters = Cache::remember('counters', self::TTL, function () {
            $output = collect();
            //Set basic variables in the collection
            $output->put('users', User::query()->count());
            $output->put('credits', number_format(User::query()->where("role","!=","admin")->sum('credits'), 2, '.', ''));
            $output->put('payments', Payment::query()->count());
            $output->put('eggs', Egg::query()->count());
            $output->put('nests', Nest::query()->count());
            $output->put('locations', Location::query()->count());

            //Prepare for counting
            $output->put('servers', collect());
            $output['servers']->active = 0;
            $output['servers']->total = 0;
            $output->put('earnings', collect());
            $output['earnings']->active = 0;
            $output['earnings']->total = 0;
            $output->put('totalUsagePercent', 0);

            //Prepare subCollection 'payments'
            $output->put('payments', collect());
            //Get and save payments from last 2 months for later filtering and looping
            $payments = Payment::query()->where('created_at', '>=', Carbon::today()->startOfMonth()->subMonth())->where('status', 'paid')->get();
            //Prepare collections and set a few variables
            $output['payments']->put('thisMonth', collect());
            $output['payments']->put('lastMonth', collect());
            $output['payments']['thisMonth']->timeStart = Carbon::today()->startOfMonth()->toDateString();
            $output['payments']['thisMonth']->timeEnd = Carbon::today()->toDateString();
            $output['payments']['lastMonth']->timeStart = Carbon::today()->startOfMonth()->subMonth()->toDateString();
            $output['payments']['lastMonth']->timeEnd = Carbon::today()->endOfMonth()->subMonth()->toDateString();
            
            //Fill out variables for each currency separately
            foreach($payments->where('created_at', '>=', Carbon::today()->startOfMonth()) as $payment){
                $paymentCurrency = $payment->currency_code;
                if(!isset($output['payments']['thisMonth'][$paymentCurrency])){
                    $output['payments']['thisMonth']->put($paymentCurrency, collect());
                    $output['payments']['thisMonth'][$paymentCurrency]->total = 0;
                    $output['payments']['thisMonth'][$paymentCurrency]->count = 0;
                }
                $output['payments']['thisMonth'][$paymentCurrency]->total += $payment->total_price;
                $output['payments']['thisMonth'][$paymentCurrency]->count ++;
            }
            foreach($payments->where('created_at', '<', Carbon::today()->startOfMonth()) as $payment){
                $paymentCurrency = $payment->currency_code;
                if(!isset($output['payments']['lastMonth'][$paymentCurrency])){
                    $output['payments']['lastMonth']->put($paymentCurrency, collect());
                    $output['payments']['lastMonth'][$paymentCurrency]->total = 0;
                    $output['payments']['lastMonth'][$paymentCurrency]->count = 0;
                }
                $output['payments']['lastMonth'][$paymentCurrency]->total += $payment->total_price;
                $output['payments']['lastMonth'][$paymentCurrency]->count ++;
            }
            $output['payments']->total = Payment::query()->count();
            
            return $output;
        });

        $lastEgg = Egg::query()->latest('updated_at')->first();
        $syncLastUpdate = $lastEgg ? $lastEgg->updated_at->isoFormat('LLL') : __('unknown');
        
        $nodes = Cache::remember('nodes', self::TTL, function() use($counters){
            $output = collect();
            foreach($nodes = Node::query()->get() as $node){ //gets all node information and prepares the structure
                $nodeId = $node['id'];
                $output->put($nodeId, collect());
                $output[$nodeId]->name = $node['name'];
                $node = Pterodactyl::getNode($nodeId);
                $output[$nodeId]->usagePercent = round(max($node['allocated_resources']['memory']/($node['memory']*($node['memory_overallocate']+100)/100), $node['allocated_resources']['disk']/($node['disk']*($node['disk_overallocate']+100)/100))*100, 2);
                $counters['totalUsagePercent'] += $output[$nodeId]->usagePercent;

                $output[$nodeId]->totalServers = 0;
                $output[$nodeId]->activeServers = 0;
                $output[$nodeId]->totalEarnings = 0;
                $output[$nodeId]->activeEarnings = 0;
            }
            $counters['totalUsagePercent'] = round($counters['totalUsagePercent']/$nodes->count(), 2);

            foreach(Pterodactyl::getServers() as $server){ //gets all servers from Pterodactyl and calculates total of credit usage for each node separately + total
                $nodeId = $server['attributes']['node'];
                
                if($CPServer = Server::query()->where('pterodactyl_id', $server['attributes']['id'])->first()){
                    $prize = Product::query()->where('id', $CPServer->product_id)->first()->price;
                    if (!$CPServer->suspended){
                        $counters['earnings']->active += $prize;
                        $counters['servers']->active ++;
                        $output[$nodeId]->activeEarnings += $prize;
                        $output[$nodeId]->activeServers ++;
                    }
                    $counters['earnings']->total += $prize;
                    $counters['servers']->total ++;
                    $output[$nodeId]->totalEarnings += $prize;
                    $output[$nodeId]->totalServers ++;
                }
            }
            return $output;
        });
        //dd($counters);
        return view('admin.overview.index', [
            'counters'       => $counters,
            'nodes'          => $nodes,
            'syncLastUpdate' => $syncLastUpdate,
            'perPageLimit'   => ($counters['servers']->total != Server::query()->count())?true:false
        ]);
    }   

    /**
     * @description Sync locations,nodes,nests,eggs with the linked pterodactyl panel
     */
    public function syncPterodactyl()
    {
        Node::syncNodes();
        Egg::syncEggs();

        return redirect()->back()->with('success', __('Pterodactyl synced'));
    }
}
