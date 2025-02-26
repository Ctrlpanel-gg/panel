<?php

namespace App\Http\Controllers\Admin;

use App\Events\UserUpdateCreditsEvent;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Notifications\DynamicNotification;
use App\Settings\LocaleSettings;
use App\Settings\PterodactylSettings;
use App\Classes\PterodactylClient;
use App\Settings\GeneralSettings;
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
use App\Models\Role;

class UserController extends Controller
{
    const READ_PERMISSION = "admin.users.read";
    const WRITE_PERMISSION = "admin.users.write";
    const SUSPEND_PERMISSION = "admin.users.suspend";
    const CHANGE_EMAIL_PERMISSION = "admin.users.write.email";
    const CHANGE_CREDITS_PERMISSION = "admin.users.write.credits";
    const CHANGE_USERNAME_PERMISSION = "admin.users.write.username";
    const CHANGE_PASSWORD_PERMISSION = "admin.users.write.password";
    const CHANGE_ROLE_PERMISSION ="admin.users.write.role";
    const CHANGE_REFERRAL_PERMISSION ="admin.users.write.referral";
    const CHANGE_PTERO_PERMISSION = "admin.users.write.pterodactyl";

    const CHANGE_SERVERLIMIT_PERMISSION = "admin.users.write.serverlimit";
    const DELETE_PERMISSION = "admin.users.delete";
    const NOTIFY_PERMISSION = "admin.users.notify";
    const LOGIN_PERMISSION = "admin.users.login_as";


    private $pterodactyl;

    public function __construct(PterodactylSettings $ptero_settings)
    {
        $this->pterodactyl = new PterodactylClient($ptero_settings);
    }

    /**
     * Display a listing of the resource.
     *
     * @param  Request  $request
     * @return Application|Factory|View|Response
     */
    public function index(LocaleSettings $locale_settings, GeneralSettings $general_settings)
    {
        $allConstants = (new \ReflectionClass(__CLASS__))->getConstants();
        $this->checkAnyPermission($allConstants);

        //$this->checkPermission(self::READ_PERMISSION);

        return view('admin.users.index', [
            'locale_datatables' => $locale_settings->datatables,
            'credits_display_name' => $general_settings->credits_display_name
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param  User  $user
     * @return Application|Factory|View|Response
     */
    public function show(User $user, LocaleSettings $locale_settings, GeneralSettings $general_settings)
    {
        $this->checkPermission(self::READ_PERMISSION);

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
            'locale_datatables' => $locale_settings->datatables,
            'credits_display_name' => $general_settings->credits_display_name
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
    public function edit(User $user, GeneralSettings $general_settings)
    {
        $allConstants = (new \ReflectionClass(__CLASS__))->getConstants();
        $permissions = array_filter($allConstants, fn($key) => str_starts_with($key, 'admin.users.write'));
        $this->checkAnyPermission($permissions);

        $roles = Role::all();
        return view('admin.users.edit')->with([
            'user' => $user,
            'credits_display_name' => $general_settings->credits_display_name,
            'roles' => $roles
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
        $data = $request->validate([
            'name' => 'required|string|min:4|max:30',
            'pterodactyl_id' => "required|numeric|unique:users,pterodactyl_id,{$user->id}",
            'email' => 'required|string|email',
            'credits' => 'required|numeric|min:0|max:99999999',
            'server_limit' => 'required|numeric|min:0|max:1000000',
            'referral_code' => "required|string|min:2|max:32|unique:users,referral_code,{$user->id}",
        ]);

        //update roles
        if ($request->roles && $this->can(self::CHANGE_ROLE_PERMISSION)) {
            $collectedRoles = collect($request->roles)->map(fn($val)=>(int)$val);
            $user->syncRoles($collectedRoles);
        }

        if (isset($this->pterodactyl->getUser($request->input('pterodactyl_id'))['errors'])) {
            throw ValidationException::withMessages([
                'pterodactyl_id' => [__("User does not exists on pterodactyl's panel")],
            ]);
        }

        $dataArray = [];

        if ($this->canAny([self::CHANGE_USERNAME_PERMISSION, self::WRITE_PERMISSION]) && $request->filled('name')) {
            $dataArray['name'] = $request->input('name');
        }

        if ($this->canAny([self::CHANGE_CREDITS_PERMISSION, self::WRITE_PERMISSION]) && $request->filled('credits')) {
            $dataArray['credits'] = $request->input('credits');
        }

        if ($this->canAny([self::CHANGE_PTERO_PERMISSION, self::WRITE_PERMISSION]) && $request->filled('pterodactyl_id')) {
            $dataArray['pterodactyl_id'] = $request->input('pterodactyl_id');
        }

        if ($this->canAny([self::CHANGE_REFERRAL_PERMISSION, self::WRITE_PERMISSION]) && $request->filled('referral_code')) {
            $dataArray['referral_code'] = $request->input('referral_code');
        }

        if ($this->canAny([self::CHANGE_EMAIL_PERMISSION, self::WRITE_PERMISSION]) && $request->filled('email')) {
            $dataArray['email'] = $request->input('email');
        }

        if ($this->canAny([self::CHANGE_SERVERLIMIT_PERMISSION, self::WRITE_PERMISSION]) && $request->filled('server_limit')) {
            $dataArray['server_limit'] = $request->input('server_limit');
        }


// Update password separately with validation, if permission is granted
        if (!is_null($request->input('new_password')) && $this->canAny([self::CHANGE_PASSWORD_PERMISSION, self::WRITE_PERMISSION])) {
            $request->validate([
                'new_password' => 'required|string|min:8',
                'new_password_confirmation' => 'required|same:new_password',
            ]);

            $dataArray['password'] = Hash::make($request->input('new_password'));
        }

// Only update with the collected data
        if (!empty($dataArray)) {
            $user->update($dataArray);
        }

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
        $this->checkPermission(self::DELETE_PERMISSION);

        if ($user->hasRole(1) && User::role(1)->count() === 1) {
            return redirect()->back()->with('error', __('You can not delete the last admin!'));
        }

        $user->delete();

        return redirect()->back()->with('success', __('user has been removed!'));
    }

    /**
     * Verifys the users email
     *
     * @param  User  $user
     * @return RedirectResponse
     */
    public function verifyEmail(User $user)
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
        $this->checkPermission(self::LOGIN_PERMISSION);

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
    public function notifications()
    {
        $this->checkPermission(self::NOTIFY_PERMISSION);

        $roles = Role::all();

        return view('admin.users.notifications')->with(["roles" => $roles]);
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
        $this->checkPermission(self::NOTIFY_PERMISSION);

//TODO: reimplement the required validation on all,users and roles . didnt work -- required_without:users,roles
        $data = $request->validate([
            'via' => 'required|min:1|array',
            'via.*' => 'required|string|in:mail,database',
            'all' => 'boolean',
            'users' => 'min:1|array',
            'roles' => 'min:1|array',
            'roles.*' => 'required_without:all,users|exists:roles,id',
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
                ->markdown('mail.custom', ['content' => $data['content']]);
        }
        $all = $data['all'] ?? false;
        $roles = $data['roles'] ?? false;
        if(!$roles){
            $users = $all ? User::where('suspended', false)->get() : User::whereIn('id', $data['users'])->get();
        } else{
            $users = User::role($data["roles"])->get();
        }


        try {
            Notification::send($users, new DynamicNotification($data['via'], $database, $mail));
        } catch (Exception $e) {
            return redirect()->route('admin.users.notifications.index')->with('error', __('The attempt to send the email failed with the error: ' . $e->getMessage()));
        }

        return redirect()->route('admin.users.notifications.index')->with('success', __('Notification sent!'));
    }

    /**
     * @param  User  $user
     * @return RedirectResponse
     */
    public function toggleSuspended(User $user)
    {
        $this->checkPermission(self::SUSPEND_PERMISSION);

        if (Auth::user()->id === $user->id) {
            return redirect()->back()->with('error', __('You can not suspend yourself!'));
        }

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
        $query = User::with('discordUser')
            ->withCount('servers')
            ->leftJoin('model_has_roles', 'users.id', '=', 'model_has_roles.model_id')
            ->leftJoin('roles', 'model_has_roles.role_id', '=', 'roles.id')
            ->selectRaw('users.*, roles.name as role_name, (SELECT COUNT(*) FROM user_referrals WHERE user_referrals.referral_id = users.id) as referrals_count')
            ->where('model_has_roles.model_type', User::class);

        return datatables($query)
            ->addColumn('avatar', function (User $user) {
                return '<img width="28px" height="28px" class="ml-1 rounded-circle" src="' . $user->getAvatar() . '">';
            })
            ->addColumn('credits', function (User $user) {
                return '<i class="mr-2 fas fa-coins"></i> ' . $user->credits();
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
                <a data-content="' . __('Login as User') . '" data-toggle="popover" data-trigger="hover" data-placement="top" href="' . route('admin.users.loginas', $user->id) . '" class="mr-1 btn btn-sm btn-primary"><i class="fas fa-sign-in-alt"></i></a>
                <a data-content="' . __('Verify') . '" data-toggle="popover" data-trigger="hover" data-placement="top" href="' . route('admin.users.verifyEmail', $user->id) . '" class="mr-1 btn btn-sm btn-secondary"><i class="fas fa-envelope"></i></a>
                <a data-content="' . __('Show') . '" data-toggle="popover" data-trigger="hover" data-placement="top"  href="' . route('admin.users.show', $user->id) . '" class="mr-1 text-white btn btn-sm btn-warning"><i class="fas fa-eye"></i></a>
                <a data-content="' . __('Edit') . '" data-toggle="popover" data-trigger="hover" data-placement="top"  href="' . route('admin.users.edit', $user->id) . '" class="mr-1 btn btn-sm btn-info"><i class="fas fa-pen"></i></a>
                <form class="d-inline" method="post" action="' . route('admin.users.togglesuspend', $user->id) . '">
                             ' . csrf_field() . '
                            <button data-content="' . $suspendText . '" data-toggle="popover" data-trigger="hover" data-placement="top" class="btn btn-sm ' . $suspendColor . ' text-white mr-1"><i class="far ' . $suspendIcon . '"></i></button>
                          </form>
                <form class="d-inline" onsubmit="return submitResult();" method="post" action="' . route('admin.users.destroy', $user->id) . '">
                             ' . csrf_field() . '
                             ' . method_field('DELETE') . '
                            <button data-content="' . __('Delete') . '" data-toggle="popover" data-trigger="hover" data-placement="top" class="mr-1 btn btn-sm btn-danger"><i class="fas fa-trash"></i></button>
                        </form>
                ';
            })
            ->editColumn('role', function (User $user) {
                $html = '';

                foreach ($user->roles as $role) {
                    $html .= "<span style='background-color: $role->color' class='badge'>$role->name</span>";
                }

                return $html;
            })
            ->editColumn('last_seen', function (User $user) {
                return $user->last_seen ? $user->last_seen->diffForHumans() : __('Never');
            })
            ->editColumn('name', function (User $user, PterodactylSettings $ptero_settings) {
                return '<a class="text-info" target="_blank" href="' . $ptero_settings->panel_url . '/admin/users/view/' . $user->pterodactyl_id . '">' . strip_tags($user->name) . '</a>';
            })
            ->orderColumn('role', 'role_name $1')
            ->rawColumns(['avatar', 'name', 'credits', 'role', 'usage',  'actions'])
            ->make();
    }
}
