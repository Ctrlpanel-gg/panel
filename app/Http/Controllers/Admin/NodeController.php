<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Location;
use App\Models\Node;
use Exception;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

/**
 * @deprecated
 * Class NodeController
 * @package App\Http\Controllers\Admin
 */
class NodeController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return Application|Factory|View
     */
    public function index()
    {
        return view('admin.nodes.index');
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
     * @param Node $node
     * @return Response
     */
    public function show(Node $node)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param Node $node
     * @return Response
     */
    public function edit(Node $node)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param Node $node
     * @return RedirectResponse
     */
    public function update(Request $request, Node $node)
    {
        $disabled = !!is_null($request->input('disabled'));
        $node->update(['disabled' => $disabled]);

        return redirect()->back()->with('success', 'Node updated');

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param Node $node
     * @return Response
     */
    public function destroy(Node $node)
    {
        //
    }

    /**
     *
     * @throws Exception
     */
    public function sync(){
        Node::query()->delete();
        Location::query()->delete();
        Node::syncNodes();

        return redirect()->back()->with('success', 'Locations and Nodes have been synced');
    }

    /**
     * @param Request $request
     * @return JsonResponse|mixed
     * @throws Exception
     */
    public function dataTable(Request $request)
    {
        $query = Node::with(['location']);
        $query->select('nodes.*');

        return datatables($query)
            ->addColumn('location', function (Node $node) {
                return $node->location->name;
            })
            ->addColumn('actions', function (Node $node) {
                $checked = $node->disabled == false ? "checked" : "";
                return '
                                <form class="d-inline" onsubmit="return submitResult();" method="post" action="' . route('admin.nodes.update', $node->id) . '">
                            ' . csrf_field() . '
                            ' . method_field("PATCH") . '
                            <div class="custom-control custom-switch">
                            <input '.$checked.' name="disabled" onchange="this.form.submit()" type="checkbox" class="custom-control-input" id="switch'.$node->id.'">
                            <label class="custom-control-label" for="switch'.$node->id.'"></label>
                          </div>
                       </form>
                ';
            })
            ->editColumn('created_at' , function (Node $node) {
                return $node->created_at ? $node->created_at->diffForHumans() : '';
            })
            ->rawColumns(['actions'])
            ->make();
    }
}
