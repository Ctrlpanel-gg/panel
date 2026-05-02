<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Classes\PterodactylClient;
use App\Events\ServerDeletedEvent;
use App\Exceptions\ApiErrorCode;
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
use App\Services\ApiResponseService;
use App\Services\ServerCreationService;
use App\Services\ServerUpgradeService;
use App\Settings\PterodactylSettings;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\AllowedSort;
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
    public const ALLOWED_SORTS = ['id', 'name', 'created_at', 'updated_at', 'suspended'];

    /**
     * Show a list of servers.
     *
     * @param  Request  $request
     * @return ServerResource
     */
    public function index(Request $request)
    {
        $perPage = min((int) $request->input('per_page', 50), 100);

        $servers = QueryBuilder::for(Server::class)
            ->allowedIncludes(self::ALLOWED_INCLUDES)
            ->allowedFilters([
                AllowedFilter::exact('suspended')->nullable(),
                ...self::ALLOWED_FILTERS
            ])
            ->allowedSorts(self::ALLOWED_SORTS)
            ->paginate($perPage);

        return ApiResponseService::success(
            ServerResource::collection($servers)->toArray($request),
            [
                'current_page' => $servers->currentPage(),
                'total' => $servers->total(),
                'last_page' => $servers->lastPage(),
                'per_page' => $servers->perPage(),
                'from' => $servers->firstItem(),
                'to' => $servers->lastItem(),
            ]
        );
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

        return ApiResponseService::success(ServerResource::make($server)->toArray($request));
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

            return ApiResponseService::created(ServerResource::make($server->fresh())->toArray($request));
        } catch (Exception $e) {
            return ApiResponseService::error(
                ApiErrorCode::PTERODACTYL_ERROR,
                $e->getMessage(),
                422
            );
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

            return ApiResponseService::success(ServerResource::make($server->refresh())->toArray($request));
        } catch (Exception $e) {
            logger()->error('Failed to update server in Pterodactyl.', [
                'error' => $e->getMessage(),
                'server_id' => $server->id
            ]);

            return ApiResponseService::error(
                ApiErrorCode::PTERODACTYL_ERROR,
                $e->getMessage(),
                422
            );
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

            return ApiResponseService::success(ServerResource::make($server->fresh())->toArray($request));
        } catch (Exception $e) {
            return ApiResponseService::error(
                ApiErrorCode::PTERODACTYL_ERROR,
                $e->getMessage(),
                $e->getCode() ?: 422
            );
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
            return ApiResponseService::error(
                ApiErrorCode::INTERNAL_ERROR,
                $e->getMessage(),
                500
            );
        }

        return ApiResponseService::noContent();
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
            return ApiResponseService::error(
                ApiErrorCode::INTERNAL_ERROR,
                $exception->getMessage(),
                500
            );
        }

        return ApiResponseService::success(ServerResource::make($server->fresh())->toArray($request));
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
            return ApiResponseService::error(
                ApiErrorCode::INTERNAL_ERROR,
                $exception->getMessage(),
                500
            );
        }

        return ApiResponseService::success(ServerResource::make($server->fresh())->toArray($request));
    }
}
