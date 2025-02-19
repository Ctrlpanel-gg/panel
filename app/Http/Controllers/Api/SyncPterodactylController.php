<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Pterodactyl\Egg;
use App\Models\Pterodactyl\Node;
use App\Models\Pterodactyl\Nest;
use App\Models\Pterodactyl\Location;
use Illuminate\Http\Request;

class SyncPterodactylController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return response()->json($this->getSyncData());
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request)
    {
        Node::syncNodes();
        Egg::syncEggs();

        return response()->json($this->getSyncData());
    }

    private function getSyncData()
    {
        $eggs = Egg::count();
        $nodes = Node::count();
        $nests = Nest::count();
        $locations = Location::count();

        $lastEgg = Egg::query()->latest('updated_at')->first();

        $lastSync = $lastEgg ? $lastEgg->updated_at : null;

        return [
            'eggs' => $eggs,
            'nodes' => $nodes,
            'nests' => $nests,
            'locations' => $locations,
            'last_sync' => $lastSync
        ];
    }
}
