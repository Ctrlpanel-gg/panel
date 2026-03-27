<?php

namespace App\Http\Controllers\Api;

use App\Classes\PterodactylClient;
use App\Events\ServerCreatedEvent;
use App\Events\ServerDeletedEvent;
use App\Http\Controllers\Api\Concerns\InteractsWithScopedApiTokens;
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
use Illuminate\Support\Facades\DB;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\QueryBuilder\AllowedFilter;
use Exception;

class ServerController extends Controller
{
    use InteractsWithScopedApiTokens;

    protected ?PterodactylClient $pterodactylClient = null;

    public function __construct(
        protected ServerCreationService $serverCreationService,
        protected ServerUpgradeService $serverUpgradeService
    )
    {
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
        $servers = $this->restrictServersToTokenOwner(
            $request,
            QueryBuilder::for(Server::class)
        )
            ->allowedIncludes(self::ALLOWED_INCLUDES)
            ->allowedFilters([
                AllowedFilter::exact('suspended')->nullable(),
                ...self::ALLOWED_FILTERS
            ])
            ->paginate($this->perPage($request));

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
    public function show(Request $request, Server $server)
    {
        $this->ensureCanAccessServer($request, $server);

        $server = $this->restrictServersToTokenOwner(
            $request,
            QueryBuilder::for(Server::class)
        )
            ->allowedIncludes(self::ALLOWED_INCLUDES)
            ->whereKey($server->id)
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

        if ($this->ownerScopedUserId($request) !== null && $this->ownerScopedUserId($request) !== (int) $data['user_id']) {
            abort(403, 'This API token is restricted to its owner.');
        }

        $user = User::findOrFail($data['user_id']);
        $product = Product::with('eggs')->findOrFail($data['product_id']);

        try {
            $server = $this->serverCreationService->handle($user, $product, $data);

            event(new ServerCreatedEvent($user, $server));

            return ServerResource::make($server->fresh());
        } catch (Exception $e) {
            logger()->warning('Failed to create server via API.', [
                'user_id' => $user->id,
                'product_id' => $product->id,
                'error' => $e->getMessage(),
            ]);

            return $this->serverErrorResponse($e, 'Failed to create the server.');
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
        $this->ensureCanAccessServer($request, $server);

        $data = $request->validated();

        if ($this->ownerScopedUserId($request) !== null && isset($data['user_id']) && $this->ownerScopedUserId($request) !== (int) $data['user_id']) {
            abort(403, 'This API token is restricted to its owner.');
        }

        try {
            $server = DB::transaction(function () use ($server, $data) {
                $lockedServer = Server::query()->whereKey($server->id)->lockForUpdate()->firstOrFail();
                $lockedServer->fill($data);

                if ($lockedServer->isDirty(['name', 'description', 'user_id'])) {
                    $ownerChanged = $lockedServer->isDirty('user_id');
                    $remoteUserId = $lockedServer->user_id;

                    if ($ownerChanged) {
                        $owner = User::findOrFail($lockedServer->user_id);

                        if (! $owner->pterodactyl_id) {
                            throw new Exception('Selected user is not synced with Pterodactyl.', 422);
                        }

                        $remoteUserId = $owner->pterodactyl_id;
                    }

                    $pteroData = [
                        'name' => $lockedServer->name,
                        'description' => $lockedServer->description,
                        'user' => $remoteUserId,
                    ];

                    $lockedServer->save();

                    $response = $this->pterodactylClient()->updateServerDetails($lockedServer, $pteroData);

                    if (! $response->successful()) {
                        $response->throw();
                    }
                } elseif ($lockedServer->isDirty()) {
                    $lockedServer->save();
                }

                return $lockedServer;
            });

            return ServerResource::make($server->refresh());
        } catch (Exception $e) {
            logger()->error('Failed to update server in Pterodactyl.', [
                'error' => $e->getMessage(),
                'server_id' => $server->id
            ]);

            return $this->serverErrorResponse($e, 'Failed to update the server.');
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
        $this->ensureCanAccessServer($request, $server);

        $data = $request->validated();

        if ($this->ownerScopedUserId($request) !== null && $this->ownerScopedUserId($request) !== (int) $data['user_id']) {
            abort(403, 'This API token is restricted to its owner.');
        }

        $user = User::findOrFail($data['user_id']);
        $product = Product::findOrFail($data['product_id']);

        try {
            $server = $this->serverUpgradeService->handle($user, $product, $server);

            return ServerResource::make($server->fresh());
        } catch (Exception $e) {
            logger()->warning('Failed to update server build via API.', [
                'server_id' => $server->id,
                'error' => $e->getMessage(),
            ]);

            return $this->serverErrorResponse($e, 'Failed to update the server build.');
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
        $this->ensureCanAccessServer($request, $server);

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
            logger()->warning('Failed to delete server via API.', [
                'server_id' => $server->id,
                'error' => $e->getMessage(),
            ]);

            return $this->serverErrorResponse($e, 'Failed to delete the server.');
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
        $this->ensureCanAccessServer($request, $server);

        $data = $request->validated();

        try {
            $logMessage = sprintf("The server with ID: %d was suspended via API", $server->id);

            if (!empty($data['reason'])) {
                $logMessage .= " | Reason: " . e($data['reason']);
            }

            activity()->performedOn($server)->log($logMessage);

            $server->suspend();
        } catch (Exception $exception) {
            logger()->warning('Failed to suspend server via API.', [
                'server_id' => $server->id,
                'error' => $exception->getMessage(),
            ]);

            return $this->serverErrorResponse($exception, 'Failed to suspend the server.');
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
        $this->ensureCanAccessServer($request, $server);

        $data = $request->validated();

        try {
            $logMessage = sprintf("The server with ID: %d was unsuspended via API", $server->id);

            if (!empty($data['reason'])) {
                $logMessage .= " | Reason: " . e($data['reason']);
            }

            activity()->performedOn($server)->log($logMessage);

            $server->unSuspend();
        } catch (Exception $exception) {
            logger()->warning('Failed to unsuspend server via API.', [
                'server_id' => $server->id,
                'error' => $exception->getMessage(),
            ]);

            return $this->serverErrorResponse($exception, 'Failed to unsuspend the server.');
        }

        return ServerResource::make($server);
    }

    private function pterodactylClient(): PterodactylClient
    {
        if ($this->pterodactylClient === null) {
            $this->pterodactylClient = app(PterodactylClient::class, [app(PterodactylSettings::class)]);
        }

        return $this->pterodactylClient;
    }

    private function serverErrorResponse(Exception $exception, string $defaultMessage): JsonResponse
    {
        $message = $this->publicServerErrorMessage($exception->getMessage(), $defaultMessage);
        $status = $message === $defaultMessage && $this->validClientErrorStatus($exception->getCode())
            ? $exception->getCode()
            : ($message === $defaultMessage ? 500 : 422);

        return response()->json(['message' => $message], $status);
    }

    private function publicServerErrorMessage(string $message, string $defaultMessage): string
    {
        $safeMessages = [
            'Server limit reached for this product.',
            'Server limit reached for this user and product combination.',
            'User must verify their email before creating a server.',
            'User must link their Discord account before creating a server.',
            'Server creation is currently disabled.',
            'No available nodes for this product in the selected location.',
            'No free allocation available on the selected node.',
            'Insufficient resources on the node to upgrade the server.',
            'Insufficient credits to upgrade the server.',
            'Selected egg is not available for this product.',
            'Selected product is not available on the current node.',
            'Selected product is not compatible with the current egg.',
            'Selected user is not synced with Pterodactyl.',
        ];

        if (in_array($message, $safeMessages, true) || str_starts_with($message, 'User do not have the required amount of ')) {
            return $message;
        }

        return $defaultMessage;
    }

    private function validClientErrorStatus(mixed $status): bool
    {
        return is_int($status) && $status >= 400 && $status < 500;
    }
}
