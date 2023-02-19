<?php

namespace App\Http\Controllers;

use App\Models\Pterodactyl\Egg;
use App\Models\Pterodactyl\Location;
use App\Models\Pterodactyl\Nest;
use App\Models\Pterodactyl\Node;
use App\Models\Product;
use App\Models\Server;
use App\Notifications\ServerCreationError;
use App\Settings\UserSettings;
use App\Settings\ServerSettings;
use App\Settings\PterodactylSettings;
use App\Classes\PterodactylClient;
use App\Settings\GeneralSettings;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Client\Response;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request as FacadesRequest;

class ServerController extends Controller
{
    private $pterodactyl;

    public function __construct(PterodactylSettings $ptero_settings)
    {
        $this->pterodactyl = new PterodactylClient($ptero_settings);
    }

    /** Display a listing of the resource. */
    public function index(GeneralSettings $general_settings, PterodactylSettings $ptero_settings)
    {
        $servers = Auth::user()->servers;

        //Get and set server infos each server
        foreach ($servers as $server) {

            //Get server infos from ptero
            $serverAttributes = $this->pterodactyl->getServerAttributes($server->pterodactyl_id, true);
            if (! $serverAttributes) {
                continue;
            }
            $serverRelationships = $serverAttributes['relationships'];
            $serverLocationAttributes = $serverRelationships['location']['attributes'];

            //Set server infos
            $server->location = $serverLocationAttributes['long'] ?
                $serverLocationAttributes['long'] :
                $serverLocationAttributes['short'];

            $server->egg = $serverRelationships['egg']['attributes']['name'];
            $server->nest = $serverRelationships['nest']['attributes']['name'];

            $server->node = $serverRelationships['node']['attributes']['name'];

            //Check if a server got renamed on Pterodactyl
            $savedServer = Server::query()->where('id', $server->id)->first();
            if ($savedServer->name != $serverAttributes['name']) {
                $savedServer->name = $serverAttributes['name'];
                $server->name = $serverAttributes['name'];
                $savedServer->save();
            }
            //get productname by product_id for server
            $product = Product::find($server->product_id);

            $server->product = $product;
        }

        return view('servers.index')->with([
            'servers' => $servers,
            'credits_display_name' => $general_settings->credits_display_name,
            'pterodactyl_url' => $ptero_settings->panel_url,
            'phpmyadmin_url' => $general_settings->phpmyadmin_url
        ]);
    }

    /** Show the form for creating a new resource. */
    public function create(UserSettings $user_settings, ServerSettings $server_settings)
    {
        $validate_configuration = $this->validateConfigurationRules($user_settings, $server_settings);

        if (!is_null($validate_configuration)) {
            return $validate_configuration;
        }

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
            'nodeCount' => $nodeCount,
            'nests' => $nests,
            'locations' => $locations,
            'eggs' => $eggs,
            'user' => Auth::user(),
            'server_creation_enabled' => $server_settings->creation_enabled,
            'min_credits_to_make_server' => $user_settings->min_credits_to_make_server
        ]);
    }

    /**
     * @return null|RedirectResponse
     */
    private function validateConfigurationRules(UserSettings $user_settings, ServerSettings $server_settings)
    {
        //limit validation
        if (Auth::user()->servers()->count() >= Auth::user()->server_limit) {
            return redirect()->route('servers.index')->with('error', __('Server limit reached!'));
        }

        // minimum credits && Check for Allocation
        if (FacadesRequest::has('product')) {
            $product = Product::findOrFail(FacadesRequest::input('product'));

            // Get node resource allocation info
            $node = $product->nodes()->findOrFail(FacadesRequest::input('node'));
            $nodeName = $node->name;

            // Check if node has enough memory and disk space
            $checkResponse = $this->pterodactyl->checkNodeResources($node, $product->memory, $product->disk);
            if ($checkResponse == false) {
                return redirect()->route('servers.index')->with('error', __("The node '".$nodeName."' doesn't have the required memory or disk left to allocate this product."));
            }

            // Min. Credits
            if (Auth::user()->credits < ($product->minimum_credits == -1
                ? $user_settings->min_credits_to_make_server
                : $product->minimum_credits)) {
                return redirect()->route('servers.index')->with('error', 'You do not have the required amount of '.CREDITS_DISPLAY_NAME.' to use this product!');
            }
        }

        //Required Verification for creating an server
        if ($user_settings->force_email_verification && !Auth::user()->hasVerifiedEmail()) {
            return redirect()->route('profile.index')->with('error', __('You are required to verify your email address before you can create a server.'));
        }

        //Required Verification for creating an server
        if (!$server_settings->creation_enabled && Auth::user()->role != 'admin') {
            return redirect()->route('servers.index')->with('error', __('The system administrator has blocked the creation of new servers.'));
        }

        //Required Verification for creating an server
        if ($user_settings->force_discord_verification && !Auth::user()->discordUser) {
            return redirect()->route('profile.index')->with('error', __('You are required to link your discord account before you can create a server.'));
        }

        return null;
    }

    /** Store a newly created resource in storage. */
    public function store(Request $request, UserSettings $user_settings, ServerSettings $server_settings)
    {
        /** @var Node $node */
        /** @var Egg $egg */
        /** @var Product $product */
        $validate_configuration = $this->validateConfigurationRules($user_settings, $server_settings);

        if (!is_null($validate_configuration)) {
            return $validate_configuration;
        }

        $request->validate([
            'name' => 'required|max:191',
            'node' => 'required|exists:nodes,id',
            'egg' => 'required|exists:eggs,id',
            'product' => 'required|exists:products,id',
        ]);

        //get required resources
        $product = Product::query()->findOrFail($request->input('product'));
        $egg = $product->eggs()->findOrFail($request->input('egg'));
        $node = $product->nodes()->findOrFail($request->input('node'));

        $server = $request->user()->servers()->create([
            'name' => $request->input('name'),
            'product_id' => $request->input('product'),
        ]);

        //get free allocation ID
        $allocationId = $this->pterodactyl->getFreeAllocationId($node);
        if (! $allocationId) {
            return $this->noAllocationsError($server);
        }

        //create server on pterodactyl
        $response = $this->pterodactyl->createServer($server, $egg, $allocationId);
        if ($response->failed()) {
            return $this->serverCreationFailed($response, $server);
        }

        $serverAttributes = $response->json()['attributes'];
        //update server with pterodactyl_id
        $server->update([
            'pterodactyl_id' => $serverAttributes['id'],
            'identifier' => $serverAttributes['identifier'],
        ]);

        if ($server_settings->charge_first_hour) {
            if ($request->user()->credits >= $server->product->getHourlyPrice()) {
                $request->user()->decrement('credits', $server->product->getHourlyPrice());
            }
        }

        return redirect()->route('servers.index')->with('success', __('Server created'));
    }

    /**
     * return redirect with error
     *
     * @param  Server  $server
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
     *
     * @param  Response  $response
     * @param  Server  $server
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
            return redirect()->route('servers.index')->with('error', __('An exception has occurred while trying to remove a resource "').$e->getMessage().'"');
        }
    }

    /** Show Server Settings */
    public function show(Server $server, ServerSettings $server_settings)
    {
        if ($server->user_id != Auth::user()->id) {
            return back()->with('error', __('This is not your Server!'));
        }
        $serverAttributes = $this->pterodactyl->getServerAttributes($server->pterodactyl_id);
        $serverRelationships = $serverAttributes['relationships'];
        $serverLocationAttributes = $serverRelationships['location']['attributes'];

        //Get current product
        $currentProduct = Product::where('id', $server->product_id)->first();

        //Set server infos
        $server->location = $serverLocationAttributes['long'] ?
            $serverLocationAttributes['long'] :
            $serverLocationAttributes['short'];

        $server->node = $serverRelationships['node']['attributes']['name'];
        $server->name = $serverAttributes['name'];
        $server->egg = $serverRelationships['egg']['attributes']['name'];

        $pteroNode = $this->pterodactyl->getNode($serverRelationships['node']['attributes']['id']);

        $products = Product::orderBy('created_at')
        ->whereHas('nodes', function (Builder $builder) use ($serverRelationships) { //Only show products for that node
            $builder->where('id', '=', $serverRelationships['node']['attributes']['id']);
        })
        ->get();

        // Set the each product eggs array to just contain the eggs name
        foreach ($products as $product) {
            $product->eggs = $product->eggs->pluck('name')->toArray();
            if ($product->memory - $currentProduct->memory > ($pteroNode['memory'] * ($pteroNode['memory_overallocate'] + 100) / 100) - $pteroNode['allocated_resources']['memory'] || $product->disk - $currentProduct->disk > ($pteroNode['disk'] * ($pteroNode['disk_overallocate'] + 100) / 100) - $pteroNode['allocated_resources']['disk']) {
                $product->doesNotFit = true;
            }
        }

        return view('servers.settings')->with([
            'server' => $server,
            'products' => $products,
            'server_enable_upgrade' => $server_settings->enable_upgrade
        ]);
    }

    public function upgrade(Server $server, Request $request)
    {
        if ($server->user_id != Auth::user()->id) {
            return redirect()->route('servers.index');
        }
        if (! isset($request->product_upgrade)) {
            return redirect()->route('servers.show', ['server' => $server->id])->with('error', __('this product is the only one'));
        }
        $user = Auth::user();
        $oldProduct = Product::where('id', $server->product->id)->first();
        $newProduct = Product::where('id', $request->product_upgrade)->first();
        $serverAttributes = $this->pterodactyl->getServerAttributes($server->pterodactyl_id);
        $serverRelationships = $serverAttributes['relationships'];

        // Get node resource allocation info
        $nodeId = $serverRelationships['node']['attributes']['id'];
        $node = Node::where('id', $nodeId)->firstOrFail();
        $nodeName = $node->name;

        // Check if node has enough memory and disk space
        $requireMemory = $newProduct->memory - $oldProduct->memory;
        $requiredisk = $newProduct->disk - $oldProduct->disk;
        $checkResponse = $this->pterodactyl->checkNodeResources($node, $requireMemory, $requiredisk);
        if ($checkResponse == false) {
            return redirect()->route('servers.index')->with('error', __("The node '".$nodeName."' doesn't have the required memory or disk left to upgrade the server."));
        }

        $priceupgrade = $newProduct->getHourlyPrice();

        if ($priceupgrade < $oldProduct->getHourlyPrice()) {
            $priceupgrade = 0;
        }
        if ($user->credits >= $priceupgrade && $user->credits >= $newProduct->minimum_credits) {
            $server->product_id = $request->product_upgrade;
            $server->update();
            $server->allocation = $serverAttributes['allocation'];
            $response = $this->pterodactyl->updateServer($server, $newProduct);
            if ($response->failed()) {
                return $this->serverCreationFailed($response, $server);
            }
            //update user balance
            $user->decrement('credits', $priceupgrade);
            //restart the server
            $response = $this->pterodactyl->powerAction($server, 'restart');
            if ($response->failed()) {
                return redirect()->route('servers.index')->with('error', $response->json()['errors'][0]['detail']);
            }

            return redirect()->route('servers.show', ['server' => $server->id])->with('success', __('Server Successfully Upgraded'));
        } else {
            return redirect()->route('servers.show', ['server' => $server->id])->with('error', __('Not Enough Balance for Upgrade'));
        }
    }
}
