<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Constants\Roles;
use App\Exceptions\ApiErrorCode;
use App\Http\Resources\RoleResource;
use App\Models\User;
use App\Models\Role;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Roles\CreateRoleRequest;
use App\Http\Requests\Api\Roles\UpdateRoleRequest;
use App\Services\ApiResponseService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\QueryBuilder\AllowedSort;

class RoleController extends Controller
{
    const ALLOWED_INCLUDES = ['permissions', 'users'];
    const ALLOWED_FILTERS = ['name'];
    const ALLOWED_SORTS = ['id', 'name', 'power', 'created_at', 'updated_at'];

    /**
     * Show a list of roles.
     *
     * @param Request $request
     * @return RoleResource
     */
    public function index(Request $request)
    {
        $perPage = min((int) $request->input('per_page', 50), 100);

        $roles = QueryBuilder::for(Role::class)
            ->allowedIncludes(self::ALLOWED_INCLUDES)
            ->allowedFilters(self::ALLOWED_FILTERS)
            ->allowedSorts(self::ALLOWED_SORTS)
            ->paginate($perPage);

        return ApiResponseService::success(
            RoleResource::collection($roles)->toArray($request),
            [
                'current_page' => $roles->currentPage(),
                'total' => $roles->total(),
                'last_page' => $roles->lastPage(),
                'per_page' => $roles->perPage(),
                'from' => $roles->firstItem(),
                'to' => $roles->lastItem(),
            ]
        );
    }

    /**
     * Store a new role in the system.
     *
     * @param  Request  $request
     * @return RoleResource
     */
    public function store(CreateRoleRequest $request)
    {
        $data = $request->validated();

        $role = Role::create($data);

        if (isset($data['permissions'])) {
            $role->syncPermissions($data['permissions']);
        }

        $role->load('permissions');

        return ApiResponseService::created(RoleResource::make($role)->toArray($request));
    }

    /**
     * Show the specified role.
     * 
     * @queryParam include string Comma-separated list of related resources to include. Example: permissions,users
     *
     * @param  Request  $request
     * @param  int  $roleId
     * @return RoleResource
     * 
     * @throws ModelNotFoundException
     */
    public function show(Request $request, int $roleId)
    {
        $role = QueryBuilder::for(Role::class)
            ->allowedIncludes(self::ALLOWED_INCLUDES)
            ->where('id', $roleId)
            ->firstOrFail();

        return ApiResponseService::success(RoleResource::make($role)->toArray($request));
    }

    /**
     * Update the specified role in the system.
     *
     * @param  Request  $request
     * @param  Role  $role
     * @return RoleResource
     * 
     * @throws ModelNotFoundException
     */
    public function update(UpdateRoleRequest $request, Role $role)
    {
        $data = $request->validated();

        if (isset($data['permissions'])) {
            $role->syncPermissions($data['permissions']);

            unset($data['permissions']);
        }

        $role->load('permissions')->update($data);

        return ApiResponseService::success(RoleResource::make($role->fresh())->toArray($request));
    }

    /**
     * Remove the specified role from the system.
     *
     * @param  Request  $request
     * @param  Role  $role
     * @return \Illuminate\Http\Response|\Illuminate\Http\JsonResponse
     * 
     * @throws ModelNotFoundException
     */
    public function destroy(Request $request, Role $role)
    {
        if (!$role->isDeletable()) {
            return ApiResponseService::error(
                ApiErrorCode::INSUFFICIENT_PERMISSIONS,
                'This role cannot be deleted.',
                403
            );
        }

        $users = User::role($role)->get();

        foreach($users as $user){
            $user->syncRoles([Roles::USER_ROLE_ID]);
        }
        
        $role->delete();

        return ApiResponseService::noContent();
    }
}
