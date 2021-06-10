<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\UsefulLink;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class UsefulLinkController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        return view('admin.usefullinks.index');
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
     * @param UsefulLink $usefulLink
     * @return Response
     */
    public function show(UsefulLink $usefulLink)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param UsefulLink $usefulLink
     * @return Response
     */
    public function edit(UsefulLink $usefulLink)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param UsefulLink $usefulLink
     * @return Response
     */
    public function update(Request $request, UsefulLink $usefulLink)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param UsefulLink $usefulLink
     * @return Response
     */
    public function destroy(UsefulLink $usefulLink)
    {
        dd($usefulLink);
    }

    
}
