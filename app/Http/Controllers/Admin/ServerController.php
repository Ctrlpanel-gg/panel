<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Server;
use App\Models\User;
use App\Settings\DiscordSettings;
use App\Settings\LocaleSettings;
use App\Settings\PterodactylSettings;
use App\Classes\PterodactylClient;
use App\Facades\Currency;
use App\Services\CreditService;
use Exception;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ServerController extends Controller
{

    const READ_PERMISSION = "admin.servers.read";
    const WRITE_PERMISSION = "admin.servers.write";
    const SUSPEND_PERMISSION = "admin.servers.suspend";
    const CHANGEOWNER_PERMISSION = "admin.servers.write.owner";
    const CHANGE_IDENTIFIER_PERMISSION = "admin.servers.write.identifier";
    const DELETE_PERMISSION = "admin.servers.delete";
    private const ANY_PERMISSION = [
        self::READ_PERMISSION,
        self::WRITE_PERMISSION,
        self::SUSPEND_PERMISSION,
        self::CHANGEOWNER_PERMISSION,
        self::CHANGE_IDENTIFIER_PERMISSION,
        self::DELETE_PERMISSION,
    ];
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
        $this->checkAnyPermission(self::ANY_PERMISSION);

        return view('admin.servers.index', [
            'locale_datatables' => $locale_settings->datatables
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  Server  $server
     * @return View
     */
    public function edit(Server $server)
    {
        $this->checkAnyPermission([
            self::WRITE_PERMISSION,
            self::CHANGEOWNER_PERMISSION,
            self::CHANGE_IDENTIFIER_PERMISSION,
        ]);

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
        $this->checkAnyPermission([
            self::WRITE_PERMISSION,
            self::CHANGEOWNER_PERMISSION,
            self::CHANGE_IDENTIFIER_PERMISSION,
        ]);

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
                    if($discord_settings->role_for_active_clients) {
                        // remove the role from the old owner
                        $oldOwner = User::findOrFail($server->user_id);
                        $discordUser = $oldOwner->discordUser;
                        if ($discordUser && $oldOwner->servers->count() <= 1) {
                            $discordUser->addOrRemoveRole('remove', $discord_settings->role_id_for_active_clients);
                        }

                        // add the role to the new owner
                        $discordUser = $user->discordUser;
                        if ($discordUser && $user->servers->count() >= 1) {
                            $discordUser->addOrRemoveRole('add', $discord_settings->role_id_for_active_clients);
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
     * @param  Request  $request
     * @param  DiscordSettings  $discord_settings
     * @return RedirectResponse|Response
     */
    public function destroy(Server $server, Request $request, DiscordSettings $discord_settings)
    {
        $this->checkPermission(self::DELETE_PERMISSION);
        try {
            // Remove role from discord
            try {
                if($discord_settings->role_for_active_clients) {
                    $user = User::findOrFail($server->user_id);
                    $discordUser = $user->discordUser;
                    if($discordUser && $user->servers->count() <= 1) {
                        $discordUser->addOrRemoveRole('remove', $discord_settings->role_id_for_active_clients);
                    }
                }
            } catch (Exception $e) {
                log::debug('Failed to update discord roles' . $e->getMessage());
            }

            if ($request->has('refund')) {
                $user = User::findOrFail($server->user_id);
                $credits = (int) round($server->product->price);
                app(CreditService::class)->refund($user, $credits);

                activity()
                    ->performedOn($server)
                    ->causedBy(Auth::user())
                    ->log("Server credits (" . Currency::formatForDisplay($credits) . ") refunded to user " . $user->name . " during deletion.");
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
        $this->checkPermission(self::WRITE_PERMISSION);
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
    public function toggleSuspended(Request $request, Server $server)
    {
        $this->checkPermission(self::SUSPEND_PERMISSION);
        $reason = $request->input('reason', null);

        try {
            $server->isSuspended() ? $server->unSuspend() : $server->suspend();
        } catch (Exception $exception) {
            return redirect()->back()->with('error', $exception->getMessage());
        }

        $logMessage = "The server with ID: " . $server->id . " was " .
            ($server->isSuspended() ? "suspended" : "unsuspended") .
            " by " . Auth::user()['name'];
        if ($reason) {
            $logMessage .= ". Reason: " . e($reason);
        }

        activity()
            ->performedOn($server)
            ->causedBy(Auth::user())
            ->log($logMessage);



        return redirect()->back()->with('success', __('Server has been updated!'));
    }

    public function syncServers()
    {
        $this->checkPermission(self::WRITE_PERMISSION);
        $CPServers = Server::all();

        $CPIDArray = [];
        $renameCount = 0;
        $recoveredCount = 0;
        $deleteCount = 0;

        // 1. Handle servers with missing pterodactyl_id (Try to recover via external_id)
        foreach ($CPServers->whereNull('pterodactyl_id') as $serverWithoutId) {
            try {
                $response = $this->pterodactyl->getServerByExternalId($serverWithoutId->id);
                if ($response->successful()) {
                    $attributes = $response->json()['attributes'] ?? null;
                    if ($attributes && isset($attributes['id'])) {
                        $serverWithoutId->update([
                            'pterodactyl_id' => $attributes['id'],
                            'identifier' => $attributes['identifier'] ?? $serverWithoutId->identifier,
                            'status' => Server::STATUS_ACTIVE,
                        ]);
                        $recoveredCount++;
                    }
                }
            } catch (Exception $e) {
                Log::error("Failed to sync server without ID {$serverWithoutId->id}: " . $e->getMessage());
            }
        }

        // Refresh list after recovery attempts
        $CPServers = Server::all();

        // 2. Map existing pterodactyl_id for presence check
        foreach ($CPServers as $CPServer) {
            if ($CPServer->pterodactyl_id) {
                $CPIDArray[$CPServer->pterodactyl_id] = false;
            }
        }

        // 3. Sync names and mark found servers
        foreach ($this->pterodactyl->getServers() as $server) {
            $pteroId = $server['attributes']['id'];
            if (isset($CPIDArray[$pteroId])) {
                $CPIDArray[$pteroId] = true;

                if (isset($server['attributes']['name'])) {
                    $savedServer = $CPServers->where('pterodactyl_id', $pteroId)->first();
                    if ($savedServer && $savedServer->name != $server['attributes']['name']) {
                        $savedServer->update(['name' => $server['attributes']['name']]);
                        $renameCount++;
                    }
                }
            }
        }

        // 4. Delete servers that don't exist on Pterodactyl anymore
        $orphanedServers = array_filter($CPIDArray, fn($found) => !$found);
        foreach ($orphanedServers as $key => $found) {
            try {
                // getServerAttributes with deleteOn404=true will delete the server if it's missing
                $this->pterodactyl->getServerAttributes($key, true);
                $deleteCount++;
            } catch (Exception $e) {
                Log::error("Failed to check orphaned server {$key}: " . $e->getMessage());
            }
        }

        $message = __('Servers synced successfully.');
        if ($renameCount > 0) $message .= ' ' . __('Renamed') . ': ' . $renameCount . '.';
        if ($recoveredCount > 0) $message .= ' ' . __('Recovered') . ': ' . $recoveredCount . '.';
        if ($deleteCount > 0) $message .= ' ' . __('Deleted') . ': ' . $deleteCount . '.';

        return redirect()->back()->with('success', $message);
    }

    /**
     * @return JsonResponse|mixed
     *
     * @throws Exception
     */
    public function dataTable(Request $request)
    {
        $this->checkAnyPermission(self::ANY_PERMISSION);

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
                if ($server->user) {
                    return '<a href="' . route('admin.users.show', $server->user->id) . '">' . e($server->user->name) . '</a>';
                }

                return __('Unknown user');
            })
            ->addColumn('resources', function (Server $server) {
                return $server->product ? e($server->product->name) : '';
            })
            ->addColumn('actions', function (Server $server) {
                $suspendColor = $server->isSuspended() ? 'btn-success' : 'btn-warning';
                $suspendIcon = $server->isSuspended() ? 'fa-play-circle' : 'fa-pause-circle';
                $suspendText = $server->isSuspended() ? __('Unsuspend') : __('Suspend');

                return '
                         <a data-content="' . __('Edit') . '" data-toggle="popover" data-trigger="hover" data-placement="top"  href="' . route('admin.servers.edit', $server->id) . '" class="btn btn-sm btn-info mr-1"><i class="fas fa-pen"></i></a>
                        <form class="d-inline" method="post" action="' . route('admin.servers.togglesuspend', $server->id) . '">
                            ' . csrf_field() . '
                        <button type="button"
                            class="btn btn-sm '.$suspendColor.' text-white mr-1 suspend-btn"
                            data-server-id="'. $server->id .'"
                            data-action="'.route("admin.servers.togglesuspend", $server->id) .'"
                            data-content="'.$suspendText .'"
                            data-toggle="popover"
                            data-trigger="hover"
                            data-placement="top">
                            <i class="far '.$suspendIcon .'"></i>
                        </button>
                       </form>

                       <button data-content="' . __('Delete') . '"
                               data-toggle="popover"
                               data-trigger="hover"
                               data-placement="top"
                               class="btn btn-sm btn-danger mr-1 delete-server-btn"
                               data-server-id="' . $server->id . '"
                               data-server-status="' . $server->status . '"
                               data-action="' . route('admin.servers.destroy', $server->id) . '">
                           <i class="fas fa-trash"></i>
                       </button>

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
            $url = e(rtrim($ptero_settings->panel_url, '/'));
            $pteroId = (int) $server->pterodactyl_id;

            return '<a class="text-info" target="_blank" href="' . $url . '/admin/servers/view/' . $pteroId . '">' . e($server->name) . '</a>';
            })
            ->rawColumns(['user', 'actions', 'status', 'name'])
            ->make();
    }
}
