<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Server;
use Exception;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class ServerController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return LengthAwarePaginator
     */
    public function index(Request $request)
    {
        return Server::with('product')->paginate($request->query('per_page') ?? 50);
    }


    /**
     * Display the specified resource.
     *
     * @param Server $server
     * @return Server
     */
    public function show(Server $server)
    {
        return $server->load('product');
    }


    /**
     * Remove the specified resource from storage.
     *
     * @param Server $server
     * @return Server
     */
    public function destroy(Server $server)
    {
        $server->delete();
        return $server;
    }


    /**
     * suspend server
     * @param Server $server
     * @return Server|JsonResponse
     */
    public function suspend(Server $server) {
        try {
            $server->suspend();
        } catch (Exception $exception) {
            return response()->json(['message' => $exception->getMessage()] , 500);
        }

        return $server->load('product');
    }


    /**
     * unsuspend server
     * @param Server $server
     * @return Server|JsonResponse
     */
    public function unSuspend(Server $server) {
        try {
            $server->unSuspend();
        } catch (Exception $exception) {
            return response()->json(['message' => $exception->getMessage()] , 500);
        }

        return $server->load('product');
    }
}
