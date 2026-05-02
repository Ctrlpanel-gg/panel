<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Exceptions\ApiErrorCode;
use App\Http\Controllers\Controller;
use App\Models\ApplicationApi;
use App\Models\User;
use App\Services\ApiResponseService;
use App\Settings\LocaleSettings;
use Exception;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\Rule;

class ApplicationApiController extends Controller
{
    const READ_PERMISSION = "admin.api.read";
    const WRITE_PERMISSION = "admin.api.write";
    /**
     * Display a listing of the resource.
     *
     * @return Application|Factory|View|Response
     */
    public function index(LocaleSettings $locale_settings)
    {
        $this->checkAnyPermission([self::READ_PERMISSION,self::WRITE_PERMISSION]);

        return view('admin.api.index', [
            'locale_datatables' => $locale_settings->datatables
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Application|Factory|View|Response
     */
    public function create()
    {
        $this->checkPermission(self::WRITE_PERMISSION);

        return view('admin.api.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  Request  $request
     * @return RedirectResponse
     */
    public function store(Request $request)
    {
        $this->checkPermission(self::WRITE_PERMISSION);

        $request->validate([
            'memo' => 'nullable|string|max:60',
            'expires_at' => 'nullable|date|after:now',
            'permissions' => 'nullable|array',
            'permissions.*' => ['string', Rule::in($this->getAvailablePermissions())],
        ]);

        $permissions = $request->input('permissions');

        if ($permissions && !$request->user()->hasAllPermissions($permissions)) {
            return back()->withErrors(['permissions' => 'You cannot assign permissions you do not have.']);
        }

        $token = ApplicationApi::create([
            'memo' => $request->input('memo'),
            'expires_at' => $request->input('expires_at'),
            'permissions' => $permissions,
            'created_by' => $request->user()->id,
        ]);

        return redirect()->route('admin.api.index')
            ->with('success', __('api key created!'))
            ->with('new_token', $token->token);
    }

    /**
     * Display the specified resource.
     *
     * @param  ApplicationApi  $applicationApi
     * @return Response
     */
    public function show(ApplicationApi $applicationApi)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  ApplicationApi  $applicationApi
     * @return Application|Factory|View|Response
     */
    public function edit(ApplicationApi $applicationApi)
    {
        $this->checkPermission(self::WRITE_PERMISSION);
        return view('admin.api.edit', [
            'applicationApi' => $applicationApi,
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  Request  $request
     * @param  ApplicationApi  $applicationApi
     * @return RedirectResponse
     */
    public function update(Request $request, ApplicationApi $applicationApi)
    {
        $this->checkPermission(self::WRITE_PERMISSION);

        $request->validate([
            'memo' => 'nullable|string|max:60',
            'is_active' => 'boolean',
            'expires_at' => 'nullable|date',
            'permissions' => 'nullable|array',
            'permissions.*' => ['string', Rule::in($this->getAvailablePermissions())],
        ]);

        $permissions = $request->input('permissions');
        if ($permissions !== null && ! auth()->user()->hasAllPermissions($permissions)) {
            abort(403);
        }

        $applicationApi->update($request->only(['memo', 'is_active', 'expires_at', 'permissions']));

        return redirect()->route('admin.api.index')->with('success', __('api key updated!'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  ApplicationApi  $applicationApi
     * @return RedirectResponse
     */
    public function destroy(ApplicationApi $applicationApi)
    {
        $this->checkPermission(self::WRITE_PERMISSION);

        $applicationApi->delete();

        return redirect()->back()->with('success', __('api key has been removed!'));
    }

    /**
     * @param  Request  $request
     * @return JsonResponse|mixed
     *
     * @throws Exception
     */
    public function regenerateToken(Request $request, ApplicationApi $applicationApi)
    {
        $this->checkPermission(self::WRITE_PERMISSION);

        if ($applicationApi->created_by && !$request->user()->hasAllPermissions($applicationApi->permissions ?? [])) {
            return back()->withErrors(['error' => 'You do not have all the permissions required by this token.']);
        }

        $applicationApi->token = (new \Hidehalo\Nanoid\Client())->generateId(48);
        $applicationApi->save();

        return redirect()->route('admin.api.index')
            ->with('success', __('api key regenerated!'))
            ->with('new_token', $applicationApi->token);
    }

    public function dataTable(Request $request)
    {
        $this->checkAnyPermission([self::READ_PERMISSION,self::WRITE_PERMISSION]);

        $query = ApplicationApi::query()->with('creator');

        return datatables($query)
            ->addColumn('actions', function (ApplicationApi $apiKey) {
                return '
                <a data-content="'.__('Edit').'" data-toggle="popover" data-trigger="hover" data-placement="top"  href="'.route('admin.api.edit', $apiKey->token).'" class="btn btn-sm btn-info mr-1"><i class="fas fa-pen"></i></a>
                <form class="d-inline" onsubmit="return submitResult();" method="post" action="'.route('admin.api.regenerate', $apiKey->token).'">
                            '.csrf_field().'
                            '.method_field('PATCH').'
                           <button data-content="'.__('Regenerate').'" data-toggle="popover" data-trigger="hover" data-placement="top" class="btn btn-sm btn-warning mr-1"><i class="fas fa-redo"></i></button>
                       </form>
                <form class="d-inline" onsubmit="return submitResult();" method="post" action="'.route('admin.api.destroy', $apiKey->token).'">
                            '.csrf_field().'
                            '.method_field('DELETE').'
                           <button data-content="'.__('Delete').'" data-toggle="popover" data-trigger="hover" data-placement="top" class="btn btn-sm btn-danger mr-1"><i class="fas fa-trash"></i></button>
                       </form>
                ';
            })
            ->editColumn('token', function (ApplicationApi $apiKey) {
                return "<code>" . e(substr($apiKey->token, 0, 8)) . '...' . e(substr($apiKey->token, -8)) . "</code>";
            })
            ->editColumn('last_used', function (ApplicationApi $apiKey) {
                return $apiKey->last_used ? $apiKey->last_used->diffForHumans() : '';
            })
            ->editColumn('is_active', function (ApplicationApi $apiKey) {
                return $apiKey->is_active
                    ? '<span class="badge badge-success">Active</span>'
                    : '<span class="badge badge-danger">Inactive</span>';
            })
            ->rawColumns(['actions', 'token', 'is_active'])
            ->make();
    }

    private function getAvailablePermissions(): array
    {
        return \Spatie\Permission\Models\Permission::pluck('name')->toArray();
    }
}
