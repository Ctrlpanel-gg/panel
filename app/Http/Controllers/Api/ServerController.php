<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Server;
use Exception;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Spatie\QueryBuilder\QueryBuilder;

class ServerController extends Controller
{
    public const ALLOWED_INCLUDES = ['product', 'user'];

    public const ALLOWED_FILTERS = ['name', 'suspended', 'identifier', 'pterodactyl_id', 'user_id', 'product_id'];

    public function index(Request $request)
    {
        $query = QueryBuilder::for(Server::class)
            ->allowedIncludes(self::ALLOWED_INCLUDES)
            ->allowedFilters(self::ALLOWED_FILTERS);

        return $query->paginate($request->input('per_page') ?? 50);
    }

    public function show(Server $server)
    {
        $query = QueryBuilder::for(Server::class)
            ->where('id', '=', $server->id)
            ->allowedIncludes(self::ALLOWED_INCLUDES);

        return $query->firstOrFail();
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, Server $server)
    {
        $request->validate([
            'reason' => 'sometimes|string|max:320',
        ]);

        $reason = $request->input('reason');
        
        $logMessage = "The server with ID: " . $server->id . " was deleted via API";
        
        if ($reason) {
            $logMessage .= ". Reason: " . e($reason);
        }

        activity()->performedOn($server)->log($logMessage);

        $server->delete();

        return $server;
    }

    public function suspend(Request $request, Server $server)
    {
        $request->validate([
            'reason' => 'sometimes|string|max:320',
        ]);

        $reason = $request->input('reason');

        try {
            $server->suspend();
        } catch (Exception $exception) {
            return response()->json(['message' => $exception->getMessage()], 500);
        }

        $logMessage = "The server with ID: " . $server->id . " was suspended via API";
        if ($reason) {
            $logMessage .= ". Reason: " . e($reason);
        }
        activity()->performedOn($server)->log($logMessage);

        return $server->load('product');
    }

    public function unSuspend(Request $request, Server $server)
    {
        $request->validate([
            'reason' => 'sometimes|string|max:320',
        ]);

        $reason = $request->input('reason');

        try {
            $server->unSuspend();
        } catch (Exception $exception) {
            return response()->json(['message' => $exception->getMessage()], 500);
        }

        $logMessage = "The server with ID: " . $server->id . " was unsuspended via API";
        if ($reason) {
            $logMessage .= ". Reason: " . e($reason);
        }
        activity()->performedOn($server)->log($logMessage);

        return $server->load('product');
    }
}
