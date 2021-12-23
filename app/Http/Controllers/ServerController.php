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
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Client\Response;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request as FacadesRequest;

class ServerController extends Controller
{
    /** Display a listing of the resource. */
    public function index()
    {
        $servers = Auth::user()->servers;

        //Get and set server infos each server
        foreach ($servers as $server) {

            //Get server infos from ptero
            $serverAttributes = Pterodactyl::getServerAttributes($server->pterodactyl_id);

            $serverRelationships = $serverAttributes['relationships'];
            $serverLocationAttributes = $serverRelationships['location']['attributes'];

            //Set server infos
            $server->location = $serverLocationAttributes['long'] ?
                $serverLocationAttributes['long'] :
                $serverLocationAttributes['short'];

            $server->egg = $serverRelationships['egg']['attributes']['name'];
            $server->nest = $serverRelationships['nest']['attributes']['name'];

            $server->node = $serverRelationships['node']['attributes']['name'];

            //get productname by product_id for server
            $product = Product::find($server->product_id);

            $server->product = $product;
        }

        return view('servers.index')->with([
            'servers' => $servers
        ]);
    }

    /** Show the form for creating a new resource. */
    public function create()
    {
        if (!is_null($this->validateConfigurationRules())) return $this->validateConfigurationRules();

        $productCount = Product::query()->where('disabled', '=', false)->count();
        $locations = Location::all();

        $nodeCount = Node::query()
            ->whereHas('products', function (Builder $builder) {
                $builder->where('disabled', '=', false);
            })->count();

        $eggs = Egg::query()
            ->whereHas('products', function (Builder $builder) {
                $builder->where('disabled', '=', false);
            })->get();

        $nests = Nest::query()
            ->whereHas('eggs', function (Builder $builder) {
                $builder->whereHas('products', function (Builder $builder) {
                    $builder->where('disabled', '=', false);
                });
            })->get();

        return view('servers.create')->with([
            'productCount' => $productCount,
            'nodeCount'    => $nodeCount,
            'nests'        => $nests,
            'locations'    => $locations,
            'eggs'         => $eggs,
            'user'         => Auth::user(),
        ]);
    }

    /**
     * @return null|RedirectResponse
     */
    private function validateConfigurationRules()
    {
        //limit validation
        if (Auth::user()->servers()->count() >= Auth::user()->server_limit) {
            return redirect()->route('servers.index')->with('error', __('Server limit reached!'));
        }

        // minimum credits
        if (FacadesRequest::has("product")) {
            $product = Product::findOrFail(FacadesRequest::input("product"));
            if (
                Auth::user()->credits <
                ($product->minimum_credits == -1
                    ? Configuration::getValueByKey('MINIMUM_REQUIRED_CREDITS_TO_MAKE_SERVER', 50)
                    : $product->minimum_credits)
            ) {
                return redirect()->route('servers.index')->with('error', "You do not have the required amount of " . CREDITS_DISPLAY_NAME . " to use this product!");
            }
        }

        //Required Verification for creating an server
        if (Configuration::getValueByKey('FORCE_EMAIL_VERIFICATION', 'false') === 'true' && !Auth::user()->hasVerifiedEmail()) {
            return redirect()->route('profile.index')->with('error', __("You are required to verify your email address before you can create a server."));
        }

        //Required Verification for creating an server
        if (Configuration::getValueByKey('FORCE_DISCORD_VERIFICATION', 'false') === 'true' && !Auth::user()->discordUser) {
            return redirect()->route('profile.index')->with('error', __("You are required to link your discord account before you can create a server."));
        }

        return null;
    }

    /** Store a newly created resource in storage. */
    public function store(Request $request)
    {
        /** @var Node $node */
        /** @var Egg $egg */
        /** @var Product $product */

        if (!is_null($this->validateConfigurationRules())) return $this->validateConfigurationRules();

        $request->validate([
            "name"    => "required|max:191",
            "node"    => "required|exists:nodes,id",
            "egg"     => "required|exists:eggs,id",
            "product" => "required|exists:products,id"
        ]);

        //get required resources
        $product = Product::query()->findOrFail($request->input('product'));
        $egg = $product->eggs()->findOrFail($request->input('egg'));
        $node = $product->nodes()->findOrFail($request->input('node'));

        $server = $request->user()->servers()->create([
            'name'       => $request->input('name'),
            'product_id' => $request->input('product'),
        ]);

        //get free allocation ID
        $allocationId = Pterodactyl::getFreeAllocationId($node);
        if (!$allocationId) return $this->noAllocationsError($server);

        //create server on pterodactyl
        $response = Pterodactyl::createServer($server, $egg, $allocationId);
        if ($response->failed()) return $this->serverCreationFailed($response, $server);

        $serverAttributes = $response->json()['attributes'];
        //update server with pterodactyl_id
        $server->update([
            'pterodactyl_id' => $serverAttributes['id'],
            'identifier'     => $serverAttributes['identifier']
        ]);

        if (Configuration::getValueByKey('SERVER_CREATE_CHARGE_FIRST_HOUR', 'true') == 'true') {
            if ($request->user()->credits >= $server->product->getHourlyPrice()) {
                $request->user()->decrement('credits', $server->product->getHourlyPrice());
            }
        }

        return redirect()->route('servers.index')->with('success', __('Server created'));
    }

    /**
     * return redirect with error
     * @param Server $server
     * @return RedirectResponse
     */
    private function noAllocationsError(Server $server)
    {
        $server->delete();

        Auth::user()->notify(new ServerCreationError($server));
        return redirect()->route('servers.index')->with('error', __('No allocations satisfying the requirements for automatic deployment on this node were found.'));
    }

    /**
     * return redirect with error
     * @param Response $response
     * @param Server $server
     * @return RedirectResponse
     */
    private function serverCreationFailed(Response $response, Server $server)
    {
        $server->delete();

        return redirect()->route('servers.index')->with('error', json_encode($response->json()));
    }

    /** Remove the specified resource from storage. */
    public function destroy(Server $server)
    {
        try {
            $server->delete();
            return redirect()->route('servers.index')->with('success', __('Server removed'));
        } catch (Exception $e) {
            return redirect()->route('servers.index')->with('error', __('An exception has occurred while trying to remove a resource "') . $e->getMessage() . '"');
        }
    }
}
