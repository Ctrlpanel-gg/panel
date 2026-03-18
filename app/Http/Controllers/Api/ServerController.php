<?php

namespace App\Http\Controllers\Api;

use App\Classes\PterodactylClient;
use App\Events\ServerCreatedEvent;
use App\Events\ServerDeletedEvent;
use App\Models\Product;
use App\Models\User;
use App\Models\Server;
use App\Http\Resources\ServerResource;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Servers\CreateServerRequest;
use App\Http\Requests\Api\Servers\DeleteServerRequest;
use App\Http\Requests\Api\Servers\SuspendServerRequest;
use App\Http\Requests\Api\Servers\UnsuspendServerRequest;
use App\Http\Requests\Api\Servers\UpdateServerBuildRequest;
use App\Http\Requests\Api\Servers\UpdateServerRequest;
use App\Services\ServerCreationService;
use App\Services\ServerUpgradeService;
use App\Settings\PterodactylSettings;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\QueryBuilder\AllowedFilter;
use Exception;

class ServerController extends Controller
{
    protected PterodactylSettings $pterodactylSettings;
    protected PterodactylClient $pterodactylClient;

    public function __construct(
        protected ServerCreationService $serverCreationService,
        protected ServerUpgradeService $serverUpgradeService
    )
    {
        $this->pterodactylSettings = app(PterodactylSettings::class);
        $this->pterodactylClient = app(PterodactylClient::class, [$this->pterodactylSettings]);
    }

    public const ALLOWED_INCLUDES = ['product', 'user'];
    public const ALLOWED_FILTERS = ['name', 'suspended', 'identifier', 'pterodactyl_id', 'user_id', 'product_id'];

    /**
     * Show a list of servers.
     *
     * @param  Request  $request
     * @return ServerResource
     */
    public function index(Request $request)
    {
        $servers = QueryBuilder::for(Server::class)
            ->allowedIncludes(self::ALLOWED_INCLUDES)
            ->allowedFilters([
                AllowedFilter::exact('suspended')->nullable(),
                ...self::ALLOWED_FILTERS
            ])
            ->paginate($request->input('per_page') ?? 50);

        return ServerResource::collection($servers);
    }

    /**
     * Show the specified server.
     *
     * @param  Request  $request
     * @param  string  $serverId
     * @return ServerResource
     * 
     * @throws ModelNotFoundException
     */
    public function show(Request $request, string $serverId)
    {
        $server = QueryBuilder::for(Server::class)
            ->allowedIncludes(self::ALLOWED_INCLUDES)
            ->where('id', $serverId)
            ->firstOrFail();

        return ServerResource::make($server);
    }

    /**
     * Store a new server in the system.
     *
     * @param  Request  $request
     * @return ServerResource
     * 
     * @throws ValidationException
     */
    public function store(CreateServerRequest $request)
    {
        $data = $request->validated();

        $user = User::findOrFail($data['user_id']);
        $product = Product::with('eggs')->findOrFail($data['product_id']);

        try {
            $server = $this->serverCreationService->handle($user, $product, $data);

            $user->decrement("credits", $product->price);

            event(new ServerCreatedEvent($user, $server));

            return ServerResource::make($server->fresh());
        } catch (Exception $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], 401);
        }
    }

    /**
     * Update the specified server in the system.
     *
     * @param UpdateServerRequest $request
     * @param Server $server
     * @return ServerResource
     * 
     * @throws ModelNotFoundException
     * @throws Exception
     */
    public function update(UpdateServerRequest $request, Server $server)
    {
        $data = $request->validated();

        $server->fill($data);

        try {
            if ($server->isDirty(['name', 'description', 'user_id'])) {
                $pteroData = array_merge($request->only(['name', 'description']), ['user' => $data['user_id']]);

                $response = $this->pterodactylClient->updateServerDetails($server, $pteroData);

                if (!$response->successful()) {
                    $response->throw();
                }
            }

            $server->save();

            return ServerResource::make($server->refresh());
        } catch (Exception $e) {
            logger()->error('Failed to update server in Pterodactyl.', [
                'error' => $e->getMessage(),
                'server_id' => $server->id
            ]);

            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    /**
     * Update the server build.
     *
     * @param  UpdateServerBuildRequest  $request
     * @param  Server  $server
     * @return ServerResource|JsonResponse
     * 
     * @throws ModelNotFoundException
     */
    public function updateBuild(UpdateServerBuildRequest $request, Server $server)
    {
        $data = $request->validated();

        $user = User::findOrFail($data['user_id']);
        $product = Product::findOrFail($data['product_id']);

        try {
            $server = $this->serverUpgradeService->handle($user, $product, $server);

            return ServerResource::make($server->fresh());
        } catch (Exception $e) {
            return response()->json(['message' => $e->getMessage()], $e->getCode() ?: 500);
        }
    }

    /**
     * Remove the specified server from the system.
     *
     * @param  DeleteServerRequest  $request
     * @param  Server  $server
     * @return \Illuminate\Http\Response
     * 
     * @throws ModelNotFoundException
     */
    public function destroy(DeleteServerRequest $request, Server $server)
    {
        $data = $request->validated();

        try {
            $logMessage = sprintf("The server with ID: %d was deleted via API", $server->id);

            if (!empty($data['reason'])) {
                $logMessage .= " | Reason: " . e($data['reason']);
            }

            activity()->performedOn($server)->log($logMessage);

            event(new ServerDeletedEvent($server));

            $server->delete();
        } catch (Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }

        return response()->noContent();
    }

    /**
     * Suspend server.
     *
     * @param  SuspendServerRequest  $request
     * @param  Server  $server
     * @return ServerResource|JsonResponse
     * 
     * @throws ModelNotFoundException
     */
    public function suspend(SuspendServerRequest $request, Server $server)
    {
        $data = $request->validated();

        try {
            $logMessage = sprintf("The server with ID: %d was suspended via API", $server->id);

            if (!empty($data['reason'])) {
                $logMessage .= " | Reason: " . e($data['reason']);
            }

            activity()->performedOn($server)->log($logMessage);

            $server->suspend();
        } catch (Exception $exception) {
            return response()->json(['message' => $exception->getMessage()], 500);
        }

        return ServerResource::make($server);
    }

    /**
     * Unsuspend server.
     *
     * @param  UnsuspendServerRequest  $request
     * @param  Server  $server
     * @return ServerResource|JsonResponse
     * 
     * @throws ModelNotFoundException
     */
    public function unSuspend(UnsuspendServerRequest $request, Server $server)
    {
        $data = $request->validated();

        try {
            $logMessage = sprintf("The server with ID: %d was unsuspended via API", $server->id);

            if (!empty($data['reason'])) {
                $logMessage .= " | Reason: " . e($data['reason']);
            }

            activity()->performedOn($server)->log($logMessage);

            $server->unSuspend();
        } catch (Exception $exception) {
            return response()->json(['message' => $exception->getMessage()], 500);
        }

        return ServerResource::make($server);
    }
}
