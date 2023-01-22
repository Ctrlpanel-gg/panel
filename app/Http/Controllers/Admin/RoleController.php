<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Exception;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleController extends Controller
{
    const READ_PERMISSIONS = 'admin.roles.read';
    const WRITE_PERMISSIONS = 'admin.roles.write';

    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return mixed
     * @throws Exception
     */
    public function index(Request $request)
    {
        $this->checkPermission(self::READ_PERMISSIONS);

        //datatables
        if ($request->ajax()) {
            return $this->dataTableQuery();
        }

        $html = $this->dataTable();
        return view('admin.roles.index', compact('html'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Application|Factory|View
     */
    public function create()
    {
        $this->checkPermission(self::WRITE_PERMISSIONS);

        $permissions = Permission::all();

        return view('admin.roles.edit', compact('permissions'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return RedirectResponse
     */
    public function store(Request $request): RedirectResponse
    {
        $role = Role::create([
            'name' => $request->name,
            'color' => $request->color
        ]);

        if ($request->permissions) {
            $role->givePermissionTo($request->permissions);
        }

        return redirect()
            ->route('admin.roles.index')
            ->with('success', __('Role saved'));
    }

    /**
     * Display the specified resource.
     */
    public function show()
    {
        abort(404);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param Role $role
     * @return Application|Factory|View
     */
    public function edit(Role $role)
    {
        $this->checkPermission(self::WRITE_PERMISSIONS);

        $permissions = Permission::all();

        return view('admin.roles.edit', compact('role', 'permissions'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Role $role
     * @return RedirectResponse
     */
    public function update(Request $request, Role $role)
    {
        if ($request->permissions) {
            if($role->id != 1){ //disable admin permissions change
                $role->syncPermissions($request->permissions);
            }
        }

        $role->update([
            'name' => $request->name,
            'color' => $request->color
        ]);


        if($role->id == 1){
            return redirect()->route('admin.roles.index')->with('success', __('Role updated. Permissions of this Role cannot be changed'));
        }else{
            return redirect()
                ->route('admin.roles.index')
                ->with('success', __('Role saved'));
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @return RedirectResponse
     */
    public function destroy(Role $role)
    {
        $this->checkPermission(self::WRITE_PERMISSIONS);

        if($role->id == 1 || $role->id == 4){ //cannot delete admin and User role
            return back()->with("error","You cannot delete that role");
        }

        $users = User::role($role)->get();

        foreach($users as $user){
            $user->syncRoles(['User']);
        }

        $role->delete();

        return redirect()
            ->route('admin.roles.index')
            ->with('success', __('Role removed'));
    }

    /**
     * @return mixed
     * @throws Exception
     */
    public function dataTable()
    {
        $query = Role::query()->withCount(['users', 'permissions']);


        return datatables($query)
            ->addColumn('actions', function (Role $role) {
                return '
                            <a title="Edit" href="'.route("admin.roles.edit", $role).'" class="btn btn-sm btn-info"><i
                                    class="fa fas fa-edit"></i></a>
                            <form class="d-inline" method="post" action="'.route("admin.roles.destroy", $role).'">
                            ' . csrf_field() . '
                            ' . method_field("DELETE") . '
                                <button title="Delete" type="submit" class="btn btn-sm btn-danger confirm"><i
                                        class="fa fas fa-trash"></i></button>
                            </form>
                ';
            })

            ->editColumn('name', function (Role $role) {
                return "<span style=\"color: $role->color\">$role->name</span>";
            })
            ->editColumn('usercount', function ($query) {
                return $query->users_count;
            })
            ->editColumn('permissionscount', function ($query){
                return $query->permissions_count;
            })
            ->rawColumns(['actions', 'name'])
            ->make(true);
    }
}
