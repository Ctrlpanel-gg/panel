<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\UsefulLink;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class UsefulLinkController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return Application|Factory|View|Response
     */
    public function index()
    {
        return view('admin.usefullinks.index');
    }

    /**
     * Show the form for creating a new resource.
     *
    public function create()
    {
        return view('admin.usefullinks.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     * @return RedirectResponse
     */
    public function store(Request $request)
    {
        $request->validate([
           'icon' => 'required|string',
           'title' => 'required|string|max:60',
           'link' => 'required|string|max:191',
           'description' => 'required|string|max:2000',
        ]);

        UsefulLink::create($request->all());
        return redirect()->route('admin.usefullinks.index')->with('success', 'link has been created!');
    }

    /**
     * Display the specified resource.
     *
     * @param UsefulLink $usefullink
     * @return Response
     */
    public function show(UsefulLink $usefullink)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param UsefulLink $usefullink
     * @return Application|Factory|View
     */
    public function edit(UsefulLink $usefullink)
    {
        return view('admin.usefullinks.edit' , [
            'link' => $usefullink
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param UsefulLink $usefullink
     * @return RedirectResponse
     */
    public function update(Request $request, UsefulLink $usefullink)
    {
        $request->validate([
            'icon' => 'required|string',
            'title' => 'required|string|max:60',
            'link' => 'required|string|max:191',
            'description' => 'required|string|max:2000',
        ]);

        $usefullink->update($request->all());
        return redirect()->route('admin.usefullinks.index')->with('success', 'link has been updated!');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param UsefulLink $usefullink
     * @return Response
     */
    public function destroy(UsefulLink $usefullink)
    {
        $usefullink->delete();
        return redirect()->back()->with('success', 'product has been removed!');
    }

    public function dataTable()
    {
        $query = UsefulLink::query();

        return datatables($query)
            ->addColumn('actions', function (UsefulLink $link) {
                return '
                            <a data-content="Edit" data-toggle="popover" data-trigger="hover" data-placement="top" href="' . route('admin.usefullinks.edit', $link->id) . '" class="btn btn-sm btn-info mr-1"><i class="fas fa-pen"></i></a>

                           <form class="d-inline" onsubmit="return submitResult();" method="post" action="' . route('admin.usefullinks.destroy', $link->id) . '">
                            ' . csrf_field() . '
                            ' . method_field("DELETE") . '
                           <button data-content="Delete" data-toggle="popover" data-trigger="hover" data-placement="top" class="btn btn-sm btn-danger mr-1"><i class="fas fa-trash"></i></button>
                       </form>
                ';
            })
            ->editColumn('created_at', function (UsefulLink $link) {
                return $link->created_at ? $link->created_at->diffForHumans() : '';
            })
            ->editColumn('icon', function (UsefulLink $link) {
                return "<i class='{$link->icon}'></i>";
            })
            ->rawColumns(['actions' , 'icon'])
            ->make();
    }
}
