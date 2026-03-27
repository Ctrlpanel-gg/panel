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
        $query = Activity::query()
            ->with('causer')
            ->orderBy('created_at', 'desc');

        if ($request->filled('search')) {
            $searchTerm = trim((string) $request->input('search'));
            $query->where(function ($builder) use ($searchTerm) {
                $like = '%' . $searchTerm . '%';

                $builder
                    ->where('description', 'like', $like)
                    ->orWhere('properties', 'like', $like)
                    ->orWhereHas('causer', function ($causerQuery) use ($like) {
                        $causerQuery->where('name', 'like', $like);
                    });
            });
        }

        $logs = $query->paginate(20)->withQueryString();

        return view('admin.activitylogs.index')->with([
            'logs' => $logs,
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
        abort(403, __('User does not have the right permissions.'));
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
        abort(403, __('User does not have the right permissions.'));
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
