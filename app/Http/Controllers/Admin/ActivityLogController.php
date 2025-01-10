<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Spatie\Activitylog\Models\Activity;
use Illuminate\Pagination\LengthAwarePaginator;

class ActivityLogController extends Controller
{
    const VIEW_PERMISSION = "admin.logs.read";
    /**
     * Display a listing of the resource.
     *
     * @return Application|Factory|View|Response
     */
    public function index(Request $request)
    {
        $this->checkPermission(self::VIEW_PERMISSION);


        $cronLogs = Storage::disk('logs')->exists('cron.log') ? Storage::disk('logs')->get('cron.log') : null;

        if ($request->input('search')) {
            $searchTerm = $request->input('search');

            // Pre-fetch logs and decode JSON properties
            $logs = Activity::all()->filter(function ($log) use ($searchTerm) {
                $properties = json_decode($log->properties, true);

                // Check if search term exists in attributes or old values
                $attributesMatch = isset($properties['attributes']) &&
                    collect($properties['attributes'])->contains(fn($value) => str_contains(strtolower($value), strtolower($searchTerm)));

                $oldMatch = isset($properties['old']) &&
                    collect($properties['old'])->contains(fn($value) => str_contains(strtolower($value), strtolower($searchTerm)));

                return str_contains(strtolower($log->description), strtolower($searchTerm)) ||
                    str_contains(strtolower(optional($log->causer)->name), strtolower($searchTerm)) ||
                    $attributesMatch || $oldMatch;
            });

            // Paginate manually
            $perPage = 20;
            $currentPage = LengthAwarePaginator::resolveCurrentPage();
            $currentItems = $logs->slice(($currentPage - 1) * $perPage, $perPage);
            $query = new LengthAwarePaginator(
                $currentItems,
                $logs->count(),
                $perPage,
                $currentPage,
                ['path' => LengthAwarePaginator::resolveCurrentPath()]
            );
        } else {
            $query = Activity::orderBy('created_at', 'desc')->paginate(20);
        }

        return view('admin.activitylogs.index')->with([
            'logs' => $query,
            'cronlogs' => $cronLogs,
        ]);


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
     * @param  Request  $request
     * @return Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  Request  $request
     * @param  int  $id
     * @return Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function destroy($id)
    {
        //
    }
}
