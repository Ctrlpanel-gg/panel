<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\Rule;
use App\Models\Role;
use Spatie\QueryBuilder\QueryBuilder;

class RoleController extends Controller
{
    const ALLOWED_INCLUDES = ['permissions', 'users'];

    const ALLOWED_FILTERS = ['name'];

    /**
     * Display a listing of the resource.
     *
     * @return LengthAwarePaginator
     */
    public function index(Request $request)
    {
        $query = QueryBuilder::for(Role::class)
            ->allowedIncludes(self::ALLOWED_INCLUDES)
            ->allowedFilters(self::ALLOWED_FILTERS);

        return $query->paginate($request->input('per_page') ?? 50);
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
        $request->validate([
            'name' => 'required|string|max:191',
            'color' => [
                'required',
                'regex:/^#([a-f0-9]{6}|[a-f0-9]{3})$/i'
            ],
            'power' => 'required',
        ]);

        $role = Role::create([
            'name' => $request->name,
            'color' => $request->color,
            'power' => $request->power,
        ]);

        if ($request->permissions) {
            $permissions = explode(",",$request->permissions);
            $collectedPermissions = collect($permissions)->map(fn($val)=>(int)$val);
            foreach($collectedPermissions as $permission){
                $role->givePermissionTo($permission);
            }
        }

        return $role;
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return Role|Collection|Model
     */
    public function show(int $id)
    {
        $query = QueryBuilder::for(Role::class)
            ->where('id', '=', $id)
            ->allowedIncludes(self::ALLOWED_INCLUDES);

        return $query->firstOrFail();
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
    public function update(Request $request, int $id)
    {
        $role = Role::findOrFail($id);

        $request->validate([
            'name' => 'sometimes|string|max:191',
            'color' => [
                'sometimes',
                'regex:/^#([a-f0-9]{6}|[a-f0-9]{3})$/i'
            ],
            'power' => 'sometimes',
        ]);

        if ($request->permissions) {
            $permissions = explode(",",$request->permissions);
            $collectedPermissions = collect($permissions)->map(fn($val)=>(int)$val);
            $role->syncPermissions($collectedPermissions);
        }


        $role->update($request->except('permissions'));

        return $role;
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function destroy(int $id)
    {
        $role = Role::findOrFail($id);

        if($role->id == 1 || $role->id == 3|| $role->id == 4){ //cannot delete admin and User role
            return response()->json([
                'error' => 'Not allowed to delete Admin, Client or Member'], 400);
        }

        $users = User::role($role)->get();

        foreach($users as $user){
            $user->syncRoles([4]);
        }
        $role->delete();

        return $role;
    }
}
