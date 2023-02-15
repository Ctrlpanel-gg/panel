<?php

namespace App\Http\Controllers\Admin;

use App\Classes\Pterodactyl;
use App\Events\UserUpdateCreditsEvent;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Notifications\DynamicNotification;
use Exception;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\HtmlString;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Spatie\QueryBuilder\QueryBuilder;

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
     * @param  Request  $request
     * @return Application|Factory|View|Response
     */
    public function index(Request $request)
    {
        return view('admin.users.index');
    }

    /**
     * Display the specified resource.
     *
     * @param  User  $user
     * @return Application|Factory|View|Response
     */
    public function show(User $user)
    {
        //QUERY ALL REFERRALS A USER HAS
        //i am not proud of this at all.
        $allReferals = [];
        $referrals = DB::table('user_referrals')->where('referral_id', '=', $user->id)->get();
        foreach ($referrals as $referral) {
            array_push($allReferals, $allReferals['id'] = User::query()->findOrFail($referral->registered_user_id));
        }
        array_pop($allReferals);

        return view('admin.users.show')->with([
            'user' => $user,
            'referrals' => $allReferals,
        ]);
    }

    /**
     * Get a JSON response of users.
     *
     * @return \Illuminate\Support\Collection|\App\models\User
     */
    public function json(Request $request)
    {
        $users = QueryBuilder::for(User::query())
            ->allowedFilters(['id', 'name', 'pterodactyl_id', 'email'])
            ->paginate(25);

        if ($request->query('user_id')) {
            $user = User::query()->findOrFail($request->input('user_id'));
            $user->avatarUrl = $user->getAvatar();

            return $user;
        }

        return $users->map(function ($item) {
            $item->avatarUrl = $item->getAvatar();

            return $item;
        });
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  User  $user
     * @return Application|Factory|View|Response
     */
    public function edit(User $user)
    {
        return view('admin.users.edit')->with([
            'user' => $user,
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  Request  $request
     * @param  User  $user
     * @return RedirectResponse
     *
     * @throws Exception
     */
    public function update(Request $request, User $user)
    {
        $request->validate([
            'name' => 'required|string|min:4|max:30',
            'pterodactyl_id' => "required|numeric|unique:users,pterodactyl_id,{$user->id}",
            'email' => 'required|string|email',
            'credits' => 'required|numeric|min:0|max:99999999',
            'server_limit' => 'required|numeric|min:0|max:1000000',
            'role' => Rule::in(['admin', 'moderator', 'client', 'member']),
            'referral_code' => "required|string|min:2|max:32|unique:users,referral_code,{$user->id}",
        ]);

        if (isset($this->pterodactyl->getUser($request->input('pterodactyl_id'))['errors'])) {
            throw ValidationException::withMessages([
                'pterodactyl_id' => [__("User does not exists on pterodactyl's panel")],
            ]);
        }

        if (!is_null($request->input('new_password'))) {
            $request->validate([
                'new_password' => 'required|string|min:8',
                'new_password_confirmation' => 'required|same:new_password',
            ]);

            $user->update([
                'password' => Hash::make($request->input('new_password')),
            ]);
        }

        $user->update($request->all());
        event(new UserUpdateCreditsEvent($user));

        return redirect()->route('admin.users.index')->with('success', 'User updated!');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  User  $user
     * @return RedirectResponse
     */
    public function destroy(User $user)
    {
        $user->delete();

        return redirect()->back()->with('success', __('user has been removed!'));
    }

    /**
     * Verifys the users email
     *
     * @param  User  $user
     * @return RedirectResponse
     */
    public function verifyEmail(Request $request, User $user)
    {
        $user->verifyEmail();

        return redirect()->back()->with('success', __('Email has been verified!'));
    }

    /**
     * @param  Request  $request
     * @param  User  $user
     * @return RedirectResponse
     */
    public function loginAs(Request $request, User $user)
    {
        $request->session()->put('previousUser', Auth::user()->id);
        Auth::login($user);

        return redirect()->route('home');
    }

    /**
     * @param  Request  $request
     * @return RedirectResponse
     */
    public function logBackIn(Request $request)
    {
        Auth::loginUsingId($request->session()->get('previousUser'), true);
        $request->session()->remove('previousUser');

        return redirect()->route('admin.users.index');
    }

    /**
     * Show the form for seding notifications to the specified resource.
     *
     * @param  User  $user
     * @return Application|Factory|View|Response
     */
    public function notifications(User $user)
    {
        return view('admin.users.notifications');
    }

    /**
     * Notify the specified resource.
     *
     * @param  Request  $request
     * @param  User  $user
     * @return RedirectResponse
     *
     * @throws Exception
     */
    public function notify(Request $request)
    {
        $data = $request->validate([
            'via' => 'required|min:1|array',
            'via.*' => 'required|string|in:mail,database',
            'all' => 'required_without:users|boolean',
            'users' => 'required_without:all|min:1|array',
            'users.*' => 'exists:users,id',
            'title' => 'required|string|min:1',
            'content' => 'required|string|min:1',
        ]);

        $mail = null;
        $database = null;
        if (in_array('database', $data['via'])) {
            $database = [
                'title' => $data['title'],
                'content' => $data['content'],
            ];
        }
        if (in_array('mail', $data['via'])) {
            $mail = (new MailMessage)
                ->subject($data['title'])
                ->line(new HtmlString($data['content']));
        }
        $all = $data['all'] ?? false;
        $users = $all ? User::all() : User::whereIn('id', $data['users'])->get();
        Notification::send($users, new DynamicNotification($data['via'], $database, $mail));

        return redirect()->route('admin.users.notifications')->with('success', __('Notification sent!'));
    }

    /**
     * @param  User  $user
     * @return RedirectResponse
     */
    public function toggleSuspended(User $user)
    {
        try {
            !$user->isSuspended() ? $user->suspend() : $user->unSuspend();
        } catch (Exception $exception) {
            return redirect()->back()->with('error', $exception->getMessage());
        }

        return redirect()->back()->with('success', __('User has been updated!'));
    }

    /**
     * @throws Exception
     */
    public function dataTable(Request $request)
    {
        $query =  User::with('discordUser')->withCount('servers');
        // manually count referrals in user_referrals table
        $query->selectRaw('users.*, (SELECT COUNT(*) FROM user_referrals WHERE user_referrals.referral_id = users.id) as referrals_count');


        return datatables($query)
            ->addColumn('avatar', function (User $user) {
                return '<img width="28px" height="28px" class="rounded-circle ml-1" src="' . $user->getAvatar() . '">';
            })
            ->addColumn('credits', function (User $user) {
                return '<i class="fas fa-coins mr-2"></i> ' . $user->credits();
            })
            ->addColumn('verified', function (User $user) {
                return $user->getVerifiedStatus();
            })
            ->addColumn('discordId', function (User $user) {
                return $user->discordUser ? $user->discordUser->id : '';
            })
            ->addColumn('actions', function (User $user) {
                $suspendColor = $user->isSuspended() ? 'btn-success' : 'btn-warning';
                $suspendIcon = $user->isSuspended() ? 'fa-play-circle' : 'fa-pause-circle';
                $suspendText = $user->isSuspended() ? __('Unsuspend') : __('Suspend');

                return '
                <a data-content="' . __('Login as User') . '" data-toggle="popover" data-trigger="hover" data-placement="top" href="' . route('admin.users.loginas', $user->id) . '" class="btn btn-sm btn-primary mr-1"><i class="fas fa-sign-in-alt"></i></a>
                <a data-content="' . __('Verify') . '" data-toggle="popover" data-trigger="hover" data-placement="top" href="' . route('admin.users.verifyEmail', $user->id) . '" class="btn btn-sm btn-secondary mr-1"><i class="fas fa-envelope"></i></a>
                <a data-content="' . __('Show') . '" data-toggle="popover" data-trigger="hover" data-placement="top"  href="' . route('admin.users.show', $user->id) . '" class="btn btn-sm text-white btn-warning mr-1"><i class="fas fa-eye"></i></a>
                <a data-content="' . __('Edit') . '" data-toggle="popover" data-trigger="hover" data-placement="top"  href="' . route('admin.users.edit', $user->id) . '" class="btn btn-sm btn-info mr-1"><i class="fas fa-pen"></i></a>
                <form class="d-inline" method="post" action="' . route('admin.users.togglesuspend', $user->id) . '">
                             ' . csrf_field() . '
                            <button data-content="' . $suspendText . '" data-toggle="popover" data-trigger="hover" data-placement="top" class="btn btn-sm ' . $suspendColor . ' text-white mr-1"><i class="far ' . $suspendIcon . '"></i></button>
                          </form>
                <form class="d-inline" onsubmit="return submitResult();" method="post" action="' . route('admin.users.destroy', $user->id) . '">
                             ' . csrf_field() . '
                             ' . method_field('DELETE') . '
                            <button data-content="' . __('Delete') . '" data-toggle="popover" data-trigger="hover" data-placement="top" class="btn btn-sm btn-danger mr-1"><i class="fas fa-trash"></i></button>
                        </form>
                ';
            })
            ->editColumn('role', function (User $user) {
                switch ($user->role) {
                    case 'admin':
                        $badgeColor = 'badge-danger';
                        break;
                    case 'moderator':
                        $badgeColor = 'badge-info';
                        break;
                    case 'client':
                        $badgeColor = 'badge-success';
                        break;
                    default:
                        $badgeColor = 'badge-secondary';
                        break;
                }

                return '<span class="badge ' . $badgeColor . '">' . $user->role . '</span>';
            })
            ->editColumn('last_seen', function (User $user) {
                return $user->last_seen ? $user->last_seen->diffForHumans() : __('Never');
            })
            ->editColumn('name', function (User $user) {
                return '<a class="text-info" target="_blank" href="' . config('SETTINGS::SYSTEM:PTERODACTYL:URL') . '/admin/users/view/' . $user->pterodactyl_id . '">' . strip_tags($user->name) . '</a>';
            })
            ->rawColumns(['avatar', 'name', 'credits', 'role', 'usage',  'actions'])
            ->make();
    }
}
