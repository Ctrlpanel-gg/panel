<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Configuration;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class ConfigurationController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return Application|Factory|View|Response
     */
    public function index()
    {
        return view('admin.configurations.index');
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
     * @param Configuration $configuration
     * @return Response
     */
    public function show(Configuration $configuration)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param Configuration $configuration
     * @return Response
     */
    public function edit(Configuration $configuration)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param Configuration $configuration
     * @return Response
     */
    public function update(Request $request, Configuration $configuration)
    {
        //
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updatevalue(Request $request)
    {
        $configuration = Configuration::findOrFail($request->input('key'));

        $request->validate([
            'key'   => 'required|string|max:191',
            'value' => 'required|string|max:191',
        ]);

        $configuration->update($request->all());

        return redirect()->route('admin.configurations.index')->with('success', __('configuration has been updated!'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param Configuration $configuration
     * @return Response
     */
    public function destroy(Configuration $configuration)
    {
        //
    }

    public function datatable()
    {
        $query = Configuration::query();

        return datatables($query)
            ->addColumn('actions', function (Configuration $configuration) {
                return '<button data-content="Edit" data-toggle="popover" data-trigger="hover" data-placement="top" onclick="configuration.parse(\'' . $configuration->key . '\',\'' . $configuration->value . '\',\'' . $configuration->type . '\')" data-content="Edit" data-trigger="hover" data-toggle="tooltip" class="btn btn-sm btn-info mr-1"><i class="fas fa-pen"></i></button> ';
            })
            ->editColumn('created_at', function (Configuration $configuration) {
                return $configuration->created_at ? $configuration->created_at->diffForHumans() : '';
            })
            ->rawColumns(['actions'])
            ->make();
    }
}
