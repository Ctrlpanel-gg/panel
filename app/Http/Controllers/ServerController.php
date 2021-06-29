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
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;


class ServerController extends Controller
{
    /** Display a listing of the resource. */
    public function index()
    {
        return view('servers.index')->with([
            'servers' => Auth::user()->Servers
        ]);
    }

    /** Show the form for creating a new resource. */
    public function create()
    {
        if (!is_null($this->validateConfigurationRules())) return $this->validateConfigurationRules();

        return view('servers.create')->with([
            'products' => Product::where('disabled', '=', false)->orderBy('price', 'asc')->get(),
            'locations' => Location::whereHas('nodes', function ($query) {
                $query->where('disabled', '=', false);
            })->get(),
            'nests' => Nest::where('disabled', '=', false)->get(),
        ]);
    }

    /** Store a newly created resource in storage. */
    public function store(Request $request)
    {
        $request->validate([
            "name" => "required|max:191",
            "description" => "nullable|max:191",
            "node_id" => "required|exists:nodes,id",
            "egg_id" => "required|exists:eggs,id",
            "product_id" => "required|exists:products,id",
        ]);

        if (!is_null($this->validateConfigurationRules())) return $this->validateConfigurationRules();

        //create server
        $egg = Egg::findOrFail($request->input('egg_id'));
        $server = Auth::user()->servers()->create($request->all());
        $node = Node::findOrFail($request->input('node_id'));

        //create server on pterodactyl
        $response = Pterodactyl::createServer($server, $egg, $node);

        if (is_null($response)) return $this->serverCreationFailed($server);
        if ($response->failed()) return $this->serverCreationFailed($server);

        //update server with pterodactyl_id
        $server->update([
            'pterodactyl_id' => $response->json()['attributes']['id'],
            'identifier' => $response->json()['attributes']['identifier']
        ]);

        return redirect()->route('servers.index')->with('success', 'server created');
    }

    /** Quick Fix */
    private function serverCreationFailed(Server $server)
    {
        $server->delete();

        Auth::user()->notify(new ServerCreationError($server));
        return redirect()->route('servers.index')->with('error', 'No allocations satisfying the requirements for automatic deployment were found.');
    }


    /**
     * @return null|RedirectResponse
     */
    private function validateConfigurationRules(){
        //limit validation
        if (Auth::user()->servers()->count() >= Auth::user()->server_limit) {
            return redirect()->route('servers.index')->with('error', 'Server limit reached!');
        }

        //minimum credits
        if (Auth::user()->credits <= Configuration::getValueByKey('MINIMUM_REQUIRED_CREDITS_TO_MAKE_SERVER', 50)) {
            return redirect()->route('servers.index')->with('error', "You do not have the required amount of credits to create a new server!");
        }

        //Required Verification for creating an server
        if (Configuration::getValueByKey('FORCE_EMAIL_VERIFICATION', 'false') === 'true' && !Auth::user()->hasVerifiedEmail()) {
            return redirect()->route('profile.index')->with('error', "You are required to verify your email address before you can create a server.");
        }

        //Required Verification for creating an server
        if (Configuration::getValueByKey('FORCE_DISCORD_VERIFICATION', 'false') === 'true' && !Auth::user()->discordUser) {
            return redirect()->route('profile.index')->with('error', "You are required to link your discord account before you can create a server.");
        }

        return null;
    }

    /** Remove the specified resource from storage. */
    public function destroy(Server $server)
    {
        try {
            $server->delete();
            return redirect()->route('servers.index')->with('success', 'server removed');
        } catch (Exception $e) {
            return redirect()->route('servers.index')->with('error', 'An exception has occurred while trying to remove a resource');
        }
    }
}
