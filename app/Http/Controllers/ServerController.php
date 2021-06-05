<?php

namespace App\Http\Controllers;

use App\Classes\Pterodactyl;
use App\Models\Configuration;
use App\Models\Egg;
use App\Models\Location;
use App\Models\Nest;
use App\Models\Node;
use App\Models\Product;
use App\Models\Server;
use App\Notifications\ServerCreationError;
use Exception;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;


class ServerController extends Controller
{


    /**
     * Display a listing of the resource.
     *
     * @return Factory|View
     */
    public function index()
    {
        return view('servers.index')->with([
            'servers' => Auth::user()->Servers
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Factory|View|RedirectResponse
     */
    public function create()
    {
        //limit
        if (Auth::user()->Servers->count() >= Auth::user()->server_limit) {
            return redirect()->route('servers.index')->with('error', "You've already reached your server limit!");
        }

        //minimum credits
        if (Auth::user()->credits <= Configuration::getValueByKey('MINIMUM_REQUIRED_CREDITS_TO_MAKE_SERVER' , 50)) {
            return redirect()->route('servers.index')->with('error', "You do not have the required amount of credits to create a new server!");
        }


        return view('servers.create')->with([
            'products'  => Product::where('disabled' , '=' , false)->orderBy('price', 'asc')->get(),
            'locations' => Location::whereHas('nodes' , function ($query) {
                $query->where('disabled' , '=' , false);
            })->get(),
            'nests'     => Nest::where('disabled' , '=' , false)->get(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     * @return RedirectResponse
     */
    public function store(Request $request)
    {
        $request->validate([
            "name"        => "required|max:191",
            "description" => "nullable|max:191",
            "node_id"     => "required|exists:nodes,id",
            "egg_id"      => "required|exists:eggs,id",
            "product_id"  => "required|exists:products,id",
        ]);

        //limit validation
        if (Auth::user()->servers()->count() >= Auth::user()->server_limit) {
            return redirect()->route('servers.index')->with('error', 'Server limit reached!');
        }

        //minimum credits
        if (Auth::user()->credits <= Configuration::getValueByKey('MINIMUM_REQUIRED_CREDITS_TO_MAKE_SERVER' , 50)) {
            return redirect()->route('servers.index')->with('error', "You do not have the required amount of credits to create a new server!");
        }


        //create server
        $egg = Egg::findOrFail($request->input('egg_id'));
        $server = Auth::user()->servers()->create($request->all());
        $node = Node::findOrFail($request->input('node_id'));

        //create server on pterodactyl
        $response = Pterodactyl::createServer($server , $egg , $node);

        if (is_null($response)) return $this->serverCreationFailed($server);
        if ($response->failed()) return $this->serverCreationFailed($server);

        //update server with pterodactyl_id
        $server->update([
            'pterodactyl_id' => $response->json()['attributes']['id'],
            'identifier'     => $response->json()['attributes']['identifier']
        ]);

        return redirect()->route('servers.index')->with('success', 'server created');
    }

    /**
     * Quick Fix
     * @param Server $server
     * @return RedirectResponse
     */
    private function serverCreationFailed(Server $server): RedirectResponse
    {
        $server->delete();

        Auth::user()->notify(new ServerCreationError($server));
        return redirect()->route('servers.index')->with('error', 'No allocations satisfying the requirements for automatic deployment were found.');
    }

    /**
     * Display the specified resource.
     *
     * @param Server $server
     * @return Response
     */
    public function show(Server $server)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param Server $server
     * @return Response
     */
    public function edit(Server $server)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param Server $server
     * @return Response
     */
    public function update(Request $request, Server $server)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param Server $server
     * @return RedirectResponse
     * @throws Exception
     */
    public function destroy(Server $server)
    {
        $server->delete();

        return redirect()->route('servers.index')->with('success', 'server removed');
    }
}
