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

class OverViewController extends Controller
{
    public const TTL = 86400;

    public function index()
    {
        $userCount = Cache::remember('user:count', self::TTL, function () {
            return User::query()->count();
        });

        $creditCount = Cache::remember('credit:count', self::TTL, function () {
            return User::query()->sum('credits');
        });

        $paymentCount = Cache::remember('payment:count', self::TTL, function () {
            return Payment::query()->count();
        });

        $serverCount = Cache::remember('server:count', self::TTL, function () {
            return Server::query()->count();
        });

        $lastEgg = Egg::query()->latest('updated_at')->first();
        $syncLastUpdate = $lastEgg ? $lastEgg->updated_at->isoFormat('LLL') : __('unknown');

        return view('admin.overview.index', [
            'serverCount'  => $serverCount,
            'userCount'    => $userCount,
            'paymentCount' => $paymentCount,
            'creditCount'  => number_format($creditCount, 2, '.', ''),

            'locationCount'  => Location::query()->count(),
            'nodeCount'      => Node::query()->count(),
            'nestCount'      => Nest::query()->count(),
            'eggCount'       => Egg::query()->count(),
            'syncLastUpdate' => $syncLastUpdate
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
