<?php

namespace App\Http\Controllers\Admin;

use App\Classes\Pterodactyl;
use App\Http\Controllers\Controller;
use App\Models\User;
use Exception;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class UserController extends Controller
{
    private Pterodactyl $pterodactyl;

    public function __construct(Pterodactyl $pterodactyl)
    {
        $this->pterodactyl = $pterodactyl;
    }

    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return Application|Factory|View|Response
     */
    public function index(Request $request)
    {
        return view('admin.users.index');
    }

    /**
     * Display the specified resource.
     *
     * @param User $user
     * @return Application|Factory|View|Response
     */
    public function show(User $user)
    {
        return view('admin.users.show')->with([
            'user' => $user
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param User $user
     * @return Application|Factory|View|Response
     */
    public function edit(User $user)
    {
        return view('admin.users.edit')->with([
            'user' => $user
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param User $user
     * @return RedirectResponse
     * @throws Exception
     */
    public function update(Request $request, User $user)
    {

        $request->validate([
            "name" => "required|string|min:4|max:30",
            "pterodactyl_id" => "required|numeric|unique:users,pterodactyl_id,{$user->id}",
            "email" => "required|string|email",
            "credits" => "required|numeric|min:0|max:1000000",
            "server_limit" => "required|numeric|min:0|max:1000000",
            "role" => Rule::in(['admin', 'mod', 'client', 'member']),
        ]);

        if (empty($this->pterodactyl->getUser($request->input('pterodactyl_id')))) {
            throw ValidationException::withMessages([
                'pterodactyl_id' => ["User does not exists on pterodactyl's panel"]
            ]);
        }


        if (!is_null($request->input('new_password'))) {
            $request->validate([
                'new_password' => 'required|string|min:8',
                'new_password_confirmation' => 'required|same:new_password'
            ]);

            $user->update([
                'password' => Hash::make($request->input('new_password')),
            ]);
        }

        $user->update($request->all());

        return redirect()->route('admin.users.index')->with('success', 'User updated!');

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param User $user
     * @return RedirectResponse
     */
    public function destroy(User $user)
    {
        $user->delete();
        return redirect()->back()->with('success', 'user has been removed!');
    }

    /**
     * @param Request $request
     * @param User $user
     * @return RedirectResponse
     */
    public function loginAs(Request $request, User $user)
    {
        $request->session()->put('previousUser', Auth::user()->id);
        Auth::login($user);
        return redirect()->route('home');
    }

    /**
     * @param Request $request
     * @return RedirectResponse
     */
    public function logBackIn(Request $request)
    {
        Auth::loginUsingId($request->session()->get('previousUser'), true);
        $request->session()->remove('previousUser');
        return redirect()->route('admin.users.index');
    }

    /**
     * @param User $user
     * @return RedirectResponse
     */
    public function reSendVerificationEmail(User $user)
    {
        if ($user->hasVerifiedEmail())
            return redirect()->back()->with('error', 'User has already verified their email');

        $user->sendEmailVerificationNotification();
        return redirect()->back()->with('success', 'User has been emailed again!');
    }

    /**
     *
     * @throws Exception
     */
    public function dataTable()
    {
        $query = User::with(['discordUser', 'servers'])->select('users.*');

        return datatables($query)
            ->addColumn('avatar', function (User $user) {
                return '<img width="28px" height="28px" class="rounded-circle ml-1" src="' . $user->getAvatar() . '">';
            })
            ->addColumn('credits', function (User $user) {
                return '<i class="fas fa-coins mr-2"></i> ' . $user->credits();
            })
            ->addColumn('usage', function (User $user) {
                return '<i class="fas fa-coins mr-2"></i> ' . $user->creditUsage();
            })
            ->addColumn('verified', function (User $user) {
                return $user->getVerifiedStatus();
            })
            ->addColumn('servers', function (User $user) {
                return $user->servers->count();
            })
            ->addColumn('discordId', function (User $user) {
                return $user->discordUser ? $user->discordUser->id : '';
            })
            ->addColumn('last_seen', function (User $user) {
                return $user->last_seen ? $user->last_seen->diffForHumans() : '';
            })
            ->addColumn('actions', function (User $user) {
                return '
                <a data-content="Resend verification" data-toggle="popover" data-trigger="hover" data-placement="top" href="' . route('admin.users.reSendVerificationEmail', $user->id) . '" class="btn btn-sm text-white btn-light mr-1"><i class="far fa-envelope"></i></a>
                <a data-content="Login as user" data-toggle="popover" data-trigger="hover" data-placement="top" href="' . route('admin.users.loginas', $user->id) . '" class="btn btn-sm btn-primary mr-1"><i class="fas fa-sign-in-alt"></i></a>
                <a data-content="Show" data-toggle="popover" data-trigger="hover" data-placement="top"  href="' . route('admin.users.show', $user->id) . '" class="btn btn-sm text-white btn-warning mr-1"><i class="fas fa-eye"></i></a>
                <a data-content="Edit" data-toggle="popover" data-trigger="hover" data-placement="top"  href="' . route('admin.users.edit', $user->id) . '" class="btn btn-sm btn-info mr-1"><i class="fas fa-pen"></i></a>
                <form class="d-inline" onsubmit="return submitResult();" method="post" action="' . route('admin.users.destroy', $user->id) . '">
                            ' . csrf_field() . '
                            ' . method_field("DELETE") . '
                           <button data-content="Delete" data-toggle="popover" data-trigger="hover" data-placement="top" class="btn btn-sm btn-danger mr-1"><i class="fas fa-trash"></i></button>
                       </form>
                ';
            })
            ->editColumn('role', function (User $user) {
                switch ($user->role) {
                    case 'admin' :
                        $badgeColor = 'badge-danger';
                        break;
                    case 'mod' :
                        $badgeColor = 'badge-info';
                        break;
                    case 'client' :
                        $badgeColor = 'badge-success';
                        break;
                    default :
                        $badgeColor = 'badge-secondary';
                        break;
                }

                return '<span class="badge ' . $badgeColor . '">' . $user->role . '</span>';
            })
            ->editColumn('name', function (User $user) {
                return '<a class="text-info" target="_blank" href="' . env('PTERODACTYL_URL', 'http://localhost') . '/admin/users/view/' . $user->pterodactyl_id . '">' . $user->name . '</a>';
            })
            ->orderColumn('last_seen', function ($query, $order) {
                $query->orderBy('last_seen', $order);
            })
            ->rawColumns(['avatar', 'name', 'credits', 'role', 'usage', 'actions', 'last_seen'])
            ->make(true);
    }
}
