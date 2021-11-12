<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Egg;
use App\Models\Nest;
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
 * Class NestsController
 * @package App\Http\Controllers\Admin
 */
class NestsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return Application|Factory|View
     */
    public function index()
    {
        return view('admin.nests.index');
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
     * @param Nest $nest
     * @return Response
     */
    public function show(Nest $nest)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param Nest $nest
     * @return Response
     */
    public function edit(Nest $nest)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param Nest $nest
     * @return RedirectResponse
     */
    public function update(Request $request, Nest $nest)
    {
        $disabled = !!is_null($request->input('disabled'));
        $nest->update(['disabled' => $disabled]);

        return redirect()->back()->with('success', 'Nest updated');

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param Nest $nest
     * @return Response
     */
    public function destroy(Nest $nest)
    {
        //
    }

    /**
     *
     * @throws Exception
     */
    public function sync(){
        Egg::query()->delete();
        Nest::query()->delete();
        Nest::syncNests();
        Egg::syncEggs();


        return redirect()->back()->with('success', 'Nests and Eggs have been synced');
    }

    /**
     * @param Request $request
     * @return JsonResponse|mixed
     * @throws Exception
     */
    public function dataTable(Request $request)
    {
        $query = Nest::with(['eggs']);
        $query->select('nests.*');

        return datatables($query)
            ->addColumn('eggs', function (Nest $nest) {
                return $nest->eggs()->count();
            })
            ->addColumn('actions', function (Nest $nest) {
                $checked = $nest->disabled == false ? "checked" : "";
                return '
                                <form class="d-inline" onsubmit="return submitResult();" method="post" action="' . route('admin.nests.update', $nest->id) . '">
                            ' . csrf_field() . '
                            ' . method_field("PATCH") . '
                            <div class="custom-control custom-switch">
                            <input '.$checked.' name="disabled" onchange="this.form.submit()" type="checkbox" class="custom-control-input" id="switch'.$nest->id.'">
                            <label class="custom-control-label" for="switch'.$nest->id.'"></label>
                          </div>
                       </form>
                ';
            })
            ->editColumn('created_at' , function (Nest $nest) {
                return $nest->created_at ? $nest->created_at->diffForHumans() : '';
            })
            ->rawColumns(['actions'])
            ->make();
    }
}
