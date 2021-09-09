<?php

namespace App\Http\Controllers\Admin;

use App\Classes\Pterodactyl;
use App\Classes\PterodactylWrapper;
use App\Http\Controllers\Controller;
use App\Models\Server;
use Exception;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class ServerController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return Application|Factory|View|Response
     */
    public function index()
    {
        return view('admin.servers.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     * @return Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param Server $server
     * @return Response
     */
    public function show(Server $server)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param Server $server
     * @return Response
     */
    public function edit(Server $server)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param Server $server
     * @return Response
     */
    public function update(Request $request, Server $server)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param Server $server
     * @return RedirectResponse|Response
     */
    public function destroy(Server $server)
    {
        try {
            $server->delete();
            return redirect()->route('admin.servers.index')->with('success', 'server removed');
        } catch (Exception $e) {
            return redirect()->route('admin.servers.index')->with('error', 'An exception has occurred while trying to remove a resource "' . $e->getMessage() . '"');
        }
    }

    /**
     * @param Server $server
     * @return RedirectResponse
     */
    public function toggleSuspended(Server $server){
        try {
            $server->isSuspended() ?  $server->unSuspend() :  $server->suspend();
        } catch (Exception $exception) {
            return redirect()->back()->with('error', $exception->getMessage());
        }

        return redirect()->back()->with('success', 'server has been updated!');
    }

    /**
     * @return JsonResponse|mixed
     * @throws Exception
     */
    public function dataTable(Request $request)
    {
        $query = Server::with(['user', 'product']);
        if ($request->has('product')) $query->where('product_id', '=', $request->input('product'));
        if ($request->has('user')) $query->where('user_id', '=', $request->input('user'));
        $query->select('servers.*');


        return datatables($query)
            ->addColumn('user', function (Server $server) {
                return '<a href="' . route('admin.users.show', $server->user->id) . '">' . $server->user->name . '</a>';
            })
            ->addColumn('resources', function (Server $server) {
                return $server->product->description;
            })
            ->addColumn('actions', function (Server $server) {
                $suspendColor = $server->isSuspended() ? "btn-success" : "btn-warning";
                $suspendIcon = $server->isSuspended() ? "fa-play-circle" : "fa-pause-circle";
                $suspendText = $server->isSuspended() ? "Unsuspend" : "Suspend";

                return '
                        <form class="d-inline" method="post" action="' . route('admin.servers.togglesuspend', $server->id) . '">
                            ' . csrf_field() . '
                           <button data-content="'.$suspendText.'" data-toggle="popover" data-trigger="hover" data-placement="top" class="btn btn-sm '.$suspendColor.' text-white mr-1"><i class="far '.$suspendIcon.'"></i></button>
                       </form>

                       <form class="d-inline" onsubmit="return submitResult();" method="post" action="' . route('admin.servers.destroy', $server->id) . '">
                            ' . csrf_field() . '
                            ' . method_field("DELETE") . '
                           <button data-content="Delete" data-toggle="popover" data-trigger="hover" data-placement="top" class="btn btn-sm btn-danger mr-1"><i class="fas fa-trash"></i></button>
                       </form>

                ';
            })
            ->addColumn('status', function (Server $server) {
                $labelColor = $server->isSuspended() ? 'text-danger' : 'text-success';
                return '<i class="fas ' . $labelColor . ' fa-circle mr-2"></i>';
            })
            ->editColumn('created_at', function (Server $server) {
                return $server->created_at ? $server->created_at->diffForHumans() : '';
            })
            ->editColumn('suspended', function (Server $server) {
                return $server->suspended ? $server->suspended->diffForHumans() : '';
            })
            ->editColumn('name', function (Server $server) {
                return '<a class="text-info" target="_blank" href="' . env('PTERODACTYL_URL', 'http://localhost') . '/admin/servers/view/' . $server->pterodactyl_id . '">' . $server->name . '</a>';
            })
            ->rawColumns(['user', 'actions', 'status', 'name'])
            ->make();
    }
}
