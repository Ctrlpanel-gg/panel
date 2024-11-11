<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Server;
use App\Models\User;
use App\Settings\DiscordSettings;
use App\Settings\LocaleSettings;
use App\Settings\PterodactylSettings;
use App\Classes\PterodactylClient;
use Exception;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

class ServerController extends Controller
{

    const READ_PERMISSION = "admin.servers.read";
    const WRITE_PERMISSION = "admin.servers.write";
    const SUSPEND_PERMISSION = "admin.servers.suspend";
    const CHANGEOWNER_PERMISSION = "admin.servers.write.owner";
    const CHANGE_IDENTIFIER_PERMISSION = "admin.servers.write.identifier";
    const DELETE_PERMISSION = "admin.servers.delete";
    private $pterodactyl;

    public function __construct(PterodactylSettings $ptero_settings)
    {
        $this->pterodactyl = new PterodactylClient($ptero_settings);
    }

    /**
     * Display a listing of the resource.
     *
     * @return Application|Factory|View|Response
     */
    public function index(LocaleSettings $locale_settings)
    {
        $allConstants = (new \ReflectionClass(__CLASS__))->getConstants();
        $this->checkAnyPermission($allConstants);

        return view('admin.servers.index', [
            'locale_datatables' => $locale_settings->datatables
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  Server  $server
     * @return Response
     */
    public function edit(Server $server)
    {
        $allConstants = (new \ReflectionClass(__CLASS__))->getConstants();
        $permissions = array_filter($allConstants, fn($key) => str_starts_with($key, 'admin.servers.write'));
        $this->checkAnyPermission($permissions);

        // get all users from the database
        $users = User::all();

        return view('admin.servers.edit')->with([
            'server' => $server,
            'users' => $users,
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  Request  $request
     * @param  Server  $server
     */
    public function update(Request $request, Server $server, DiscordSettings $discord_settings)
    {
        $request->validate([
            'identifier' => 'required|string',
            'user_id' => 'required|integer',
        ]);


        if ($request->get('user_id') != $server->user_id && $this->can(self::CHANGEOWNER_PERMISSION)) {
            // find the user
            $user = User::findOrFail($request->get('user_id'));

            // try to update the owner on pterodactyl
            try {
                $response = $this->pterodactyl->updateServerOwner($server, $user->pterodactyl_id);
                if ($response->getStatusCode() != 200) {
                    return redirect()->back()->with('error', 'Failed to update server owner on pterodactyl');
                }

                // Attempt to remove/add roles respectively
                try {
                    if($discord_settings->role_on_purchase) {
                        // remove the role from the old owner
                        $oldOwner = User::findOrFail($server->user_id);
                        $discordUser = $oldOwner->discordUser;
                        if ($discordUser && $oldOwner->servers->count() <= 1) {
                            $discordUser->addOrRemoveRole('remove', $discord_settings->role_id_on_purchase);
                        }

                        // add the role to the new owner
                        $discordUser = $user->discordUser;
                        if ($discordUser && $user->servers->count() >= 1) {
                            $discordUser->addOrRemoveRole('add', $discord_settings->role_id_on_purchase);
                        }
                    }
                } catch (Exception $e) {
                    log::debug('Failed to update discord roles' . $e->getMessage());
                }

                // update the owner on the database
                $server->user_id = $user->id;

            } catch (Exception $e) {
                return redirect()->back()->with('error', 'Internal Server Error');
            }
        }

        // update the identifier
        if ($this->can(self::CHANGE_IDENTIFIER_PERMISSION)) {

            $server->identifier = $request->get('identifier');
        }
        $server->save();

        return redirect()->route('admin.servers.index')->with('success', 'Server updated!');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  Server  $server
     * @return RedirectResponse|Response
     */
    public function destroy(Server $server, DiscordSettings $discord_settings)
    {
        $this->checkPermission(self::DELETE_PERMISSION);
        try {
            // Remove role from discord
            try {
                if($discord_settings->role_on_purchase) {
                    $user = User::findOrFail($server->user_id);
                    $discordUser = $user->discordUser;
                    if($discordUser && $user->servers->count() <= 1) {
                        $discordUser->addOrRemoveRole('remove', $discord_settings->role_id_on_purchase);
                    }
                }
            } catch (Exception $e) {
                log::debug('Failed to update discord roles' . $e->getMessage());
            }

            // Attempt to remove the server from pterodactyl
            $server->delete();

            return redirect()->route('admin.servers.index')->with('success', __('Server removed'));
        } catch (Exception $e) {
            return redirect()->route('admin.servers.index')->with('error', __('An exception has occurred while trying to remove a resource "') . $e->getMessage() . '"');
        }
    }

    /**
     * Cancel the Server billing cycle.
     *
     * @param Server $server
     * @return RedirectResponse|Response
     */
    public function cancel(Server $server)
    {
        try {
            $server->update([
                'canceled' => now(),
            ]);
            return redirect()->route('servers.index')->with('success', __('Server canceled'));
        } catch (Exception $e) {
            return redirect()->route('servers.index')->with('error', __('An exception has occurred while trying to cancel the server"') . $e->getMessage() . '"');
        }
    }

    /**
     * @param Server $server
     * @return RedirectResponse
     */
    public function toggleSuspended(Server $server)
    {
        $this->checkPermission(self::SUSPEND_PERMISSION);

        try {
            $server->isSuspended() ? $server->unSuspend() : $server->suspend();
        } catch (Exception $exception) {
            return redirect()->back()->with('error', $exception->getMessage());
        }

        return redirect()->back()->with('success', __('Server has been updated!'));
    }

    public function syncServers()
    {
        $CPServers = Server::get();

        $CPIDArray = [];
        $renameCount = 0;
        foreach ($CPServers as $CPServer) { //go thru all CP servers and make array with IDs as keys. All values are false.
            if ($CPServer->pterodactyl_id) {
                $CPIDArray[$CPServer->pterodactyl_id] = false;
            }
        }

        foreach ($this->pterodactyl->getServers() as $server) { //go thru all ptero servers, if server exists, change value to true in array.
            if (isset($CPIDArray[$server['attributes']['id']])) {
                $CPIDArray[$server['attributes']['id']] = true;

                if (isset($server['attributes']['name'])) { //failsafe
                    //Check if a server got renamed
                    $savedServer = Server::query()->where('pterodactyl_id', $server['attributes']['id'])->first();
                    if ($savedServer->name != $server['attributes']['name']) {
                        $savedServer->name = $server['attributes']['name'];
                        $savedServer->save();
                        $renameCount++;
                    }
                }
            }
        }
        $filteredArray = array_filter($CPIDArray, function ($v, $k) {
            return $v == false;
        }, ARRAY_FILTER_USE_BOTH); //Array of servers, that dont exist on ptero (value == false)
        $deleteCount = 0;
        foreach ($filteredArray as $key => $CPID) { //delete servers that dont exist on ptero anymore
            if (!$this->pterodactyl->getServerAttributes($key, true)) {
                $deleteCount++;
            }
        }

        return redirect()->back()->with('success', __('Servers synced successfully' . (($renameCount) ? (',\n' . __('renamed') . ' ' . $renameCount . ' ' . __('servers')) : '') . ((count($filteredArray)) ? (',\n' . __('deleted') . ' ' . $deleteCount . '/' . count($filteredArray) . ' ' . __('old servers')) : ''))) . '.';
    }

    /**
     * @return JsonResponse|mixed
     *
     * @throws Exception
     */
    public function dataTable(Request $request)
    {
        $query = Server::with(['user', 'product']);


        if ($request->has('product')) {
            $query->where('product_id', '=', $request->input('product'));
        }
        if ($request->has('user')) {
            $query->where('user_id', '=', $request->input('user'));
        }
        $query->select('servers.*');

        Log::info($request->input('order'));


        return datatables($query)
            ->addColumn('user', function (Server $server) {
                return '<a href="' . route('admin.users.show', $server->user->id) . '">' . $server->user->name . '</a>';
            })
            ->addColumn('resources', function (Server $server) {
                return $server->product->name;
            })
            ->addColumn('actions', function (Server $server) {
                $suspendColor = $server->isSuspended() ? 'btn-success' : 'btn-warning';
                $suspendIcon = $server->isSuspended() ? 'fa-play-circle' : 'fa-pause-circle';
                $suspendText = $server->isSuspended() ? __('Unsuspend') : __('Suspend');

                return '
                         <a data-content="' . __('Edit') . '" data-toggle="popover" data-trigger="hover" data-placement="top"  href="' . route('admin.servers.edit', $server->id) . '" class="btn btn-sm btn-info mr-1"><i class="fas fa-pen"></i></a>
                        <form class="d-inline" method="post" action="' . route('admin.servers.togglesuspend', $server->id) . '">
                            ' . csrf_field() . '
                           <button data-content="' . $suspendText . '" data-toggle="popover" data-trigger="hover" data-placement="top" class="btn btn-sm ' . $suspendColor . ' text-white mr-1"><i class="far ' . $suspendIcon . '"></i></button>
                       </form>

                       <form class="d-inline" onsubmit="return submitResult();" method="post" action="' . route('admin.servers.destroy', $server->id) . '">
                            ' . csrf_field() . '
                            ' . method_field('DELETE') . '
                           <button data-content="' . __('Delete') . '" data-toggle="popover" data-trigger="hover" data-placement="top" class="btn btn-sm btn-danger mr-1"><i class="fas fa-trash"></i></button>
                       </form>

                ';
            })
            ->addColumn('status', function (Server $server) {
                $labelColor = $server->suspended ? 'text-danger' : 'text-success';

                return '<i class="fas ' . $labelColor . ' fa-circle mr-2"></i>';
            })
            ->editColumn('created_at', function (Server $server) {
                return $server->created_at ? $server->created_at->diffForHumans() : '';
            })
            ->editColumn('suspended', function (Server $server) {
                return $server->suspended ? $server->suspended->diffForHumans() : '';
            })
            ->editColumn('name', function (Server $server, PterodactylSettings $ptero_settings) {
                return '<a class="text-info" target="_blank" href="' . $ptero_settings->panel_url . '/admin/servers/view/' . $server->pterodactyl_id . '">' . strip_tags($server->name) . '</a>';
            })
            ->rawColumns(['user', 'actions', 'status', 'name'])
            ->make();
    }
}
