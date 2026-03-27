<?php

namespace App\Http\Controllers\Admin;

use App\Constants\Roles;
use App\Http\Controllers\Controller;
use App\Models\User;
use Exception;
use Illuminate\Validation\Rule;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Permission;
use App\Models\Role;

class RoleController extends Controller
{

    const READ_PERMISSION = "admin.roles.read";
    const CREATE_PERMISSION = "admin.roles.create";
    const EDIT_PERMISSION = "admin.roles.edit";
    const DELETE_PERMISSION = "admin.roles.delete";
    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return mixed
     * @throws Exception
     */
    public function index(Request $request)
    {

        $allConstants = (new \ReflectionClass(__CLASS__))->getConstants();
        $this->checkAnyPermission($allConstants);

        //datatables
        if ($request->ajax()) {
            return $this->dataTable();
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
        $this->checkPermission(self::CREATE_PERMISSION);

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
        $this->checkPermission(self::CREATE_PERMISSION);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:191', Rule::unique('roles', 'name')],
            'color' => ['required', 'string', 'max:20'],
            'power' => ['required', 'integer', 'min:0', 'max:' . max(0, $this->currentUserPower() - 1)],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['integer', 'exists:permissions,id'],
        ]);

        $role = Role::create([
            'name' => $validated['name'],
            'color' => $validated['color'],
            'power' => $validated['power'],
            'guard_name' => 'web',
        ]);

        if (! empty($validated['permissions'])) {
            $collectedPermissions = collect($validated['permissions'])->map(fn ($val) => (int) $val);
            $role->givePermissionTo($collectedPermissions);
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
        $this->checkPermission(self::EDIT_PERMISSION);

        if ($this->currentUserPower() < $role->power) {
            return back()->with("error","You dont have enough Power to edit that Role");
        }

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
        $this->checkPermission(self::EDIT_PERMISSION);

        if ($this->currentUserPower() < $role->power) {
            return back()->with("error","You dont have enough Power to edit that Role");
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:191', Rule::unique('roles', 'name')->ignore($role->id)],
            'color' => ['required', 'string', 'max:20'],
            'power' => ['required', 'integer', 'min:0', 'max:' . max(0, $this->currentUserPower() - 1)],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['integer', 'exists:permissions,id'],
        ]);

        if (! empty($validated['permissions'])) {
            if (! $this->isAdminRole($role)) {
                $collectedPermissions = collect($validated['permissions'])->map(fn ($val) => (int) $val);
                $role->syncPermissions($collectedPermissions);
            }
        } elseif (! $this->isAdminRole($role)) {
            $role->syncPermissions([]);
        }

        $role->update([
            'name' => $validated['name'],
            'color' => $validated['color'],
            'power' => $validated['power'],
        ]);

        return redirect()
            ->route('admin.roles.index')
            ->with('success', __('Role saved'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @return RedirectResponse
     */
    public function destroy(Role $role)
    {
        $this->checkPermission(self::DELETE_PERMISSION);

        if (! $role->isDeletable()) {
            return back()->with("error","You cannot delete that role");
        }

        $defaultRole = Role::query()
            ->find(Roles::USER_ROLE_ID)
            ?? Role::query()->where('name', 'User')->first();

        if (! $defaultRole) {
            return back()->with('error', __('Default user role could not be found.'));
        }

        $users = User::role($role)->get();

        foreach ($users as $user) {
            $user->syncRoles([$defaultRole]);
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
        $allConstants = (new \ReflectionClass(__CLASS__))->getConstants();
        $this->checkAnyPermission($allConstants);

        $query = Role::query()->withCount(['users', 'permissions'])->get();

        return datatables($query)
            ->editColumn('id', function (Role $role) {
                return $role->id;
            })
            ->addColumn('actions', function (Role $role) {
                $actions = [];

                if (auth()->user()?->can(self::EDIT_PERMISSION) && $this->currentUserPower() >= $role->power) {
                    $actions[] = '
                        <a title="Edit" href="'.route("admin.roles.edit", $role).'" class="btn btn-sm btn-info"><i class="fa fas fa-edit"></i></a>
                    ';
                }

                if (auth()->user()?->can(self::DELETE_PERMISSION) && $role->isDeletable()) {
                    $actions[] = '
                        <form class="d-inline" method="post" action="'.route("admin.roles.destroy", $role).'">
                        ' . csrf_field() . '
                        ' . method_field("DELETE") . '
                            <button title="Delete" type="submit" class="btn btn-sm btn-danger confirm"><i class="fa fas fa-trash"></i></button>
                        </form>
                    ';
                }

                return implode('', $actions);
            })

            ->editColumn('name', function (Role $role) {
                $color = preg_match('/^[a-zA-Z0-9#\s\-(),]+$/', $role->color) ? $role->color : '#ccc';
                return "<span style='background-color: " . e($color) . "' class='badge'>" . e($role->name) . "</span>";
            })
            ->editColumn('users_count', function ($query) {
                return $query->users_count;
            })
            ->editColumn('permissions_count', function ($query){
                return $query->permissions_count;
            })
            ->editColumn('power', function (Role $role){
                return $role->power;
            })
            ->rawColumns(['actions', 'name'])
            ->make(true);
    }

    private function currentUserPower(): int
    {
        return (int) (Auth::user()?->roles()->max('power') ?? 0);
    }

    private function isAdminRole(Role $role): bool
    {
        return $role->id === Roles::ADMIN_ROLE_ID || $role->name === 'Admin';
    }
}
