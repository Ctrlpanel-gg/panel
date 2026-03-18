<?php

namespace App\Http\Controllers\Api;

use App\Constants\Roles;
use App\Http\Resources\RoleResource;
use App\Models\User;
use App\Models\Role;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Roles\CreateRoleRequest;
use App\Http\Requests\Api\Roles\UpdateRoleRequest;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Spatie\QueryBuilder\QueryBuilder;

class RoleController extends Controller
{
    const ALLOWED_INCLUDES = ['permissions', 'users'];
    const ALLOWED_FILTERS = ['name'];

    /**
     * Show a list of roles.
     *
     * @param Request $request
     * @return RoleResource
     */
    public function index(Request $request)
    {
        $roles = QueryBuilder::for(Role::class)
            ->allowedIncludes(self::ALLOWED_INCLUDES)
            ->allowedFilters(self::ALLOWED_FILTERS)
            ->paginate($request->input('per_page') ?? 50);

        return RoleResource::collection($roles);
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

        return RoleResource::make($role);
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

        return RoleResource::make($role);
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

        return RoleResource::make($role);
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
            return response()->json(['error' => 'This role cannot be deleted.'], 403);
        }

        $users = User::role($role)->get();

        foreach($users as $user){
            $user->syncRoles([Roles::USER_ROLE_ID]);
        }
        
        $role->delete();

        return response()->noContent();
    }
}
