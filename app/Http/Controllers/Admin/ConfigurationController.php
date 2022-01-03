<?php

namespace App\Http\Controllers\Admin;

use App\Models\Configuration;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class ConfigurationController
{
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

        return redirect()->route('admin.settings.index')->with('success', __('configuration has been updated!'));
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
                return '<button data-content="' . __("Edit") . '" data-toggle="popover" data-trigger="hover" data-placement="top" onclick="configuration.parse(\'' . $configuration->key . '\',\'' . $configuration->value . '\',\'' . $configuration->type . '\')" data-content="Edit" data-trigger="hover" data-toggle="tooltip" class="btn btn-sm btn-info mr-1"><i class="fas fa-pen"></i></button> ';
            })
            ->editColumn('created_at', function (Configuration $configuration) {
                return $configuration->created_at ? $configuration->created_at->diffForHumans() : '';
            })
            ->rawColumns(['actions'])
            ->make();
    }
}
