<?php

namespace App\Http\Controllers\Admin;

use App\Events\UserUpdateCreditsEvent;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Voucher;
use App\Settings\GeneralSettings;
use App\Settings\LocaleSettings;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\ValidationException;

class VoucherController extends Controller
{
    const READ_PERMISSION = "admin.voucher.read";
    const WRITE_PERMISSION = "admin.voucher.write";
    /**
     * Display a listing of the resource.
     *
     * @return Application|Factory|View
     */
    public function index(LocaleSettings $locale_settings, GeneralSettings $general_settings)
    {
        $this->checkAnyPermission([self::READ_PERMISSION, self::WRITE_PERMISSION]);

        return view('admin.vouchers.index', [
            'locale_datatables' => $locale_settings->datatables,
            'credits_display_name' => $general_settings->credits_display_name
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Application|Factory|View
     */
    public function create(GeneralSettings $general_settings)
    {
        $this->checkPermission(self::WRITE_PERMISSION);
        return view('admin.vouchers.create', [
            'credits_display_name' => $general_settings->credits_display_name
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  Request  $request
     * @return RedirectResponse
     */
    public function store(Request $request)
    {
        $request->validate([
            'memo' => 'nullable|string|max:191',
            'code' => 'required|string|alpha_dash|max:36|min:4|unique:vouchers',
            'uses' => 'required|numeric|max:2147483647|min:1',
            'credits' => 'required|numeric|between:0,99999999',
            'expires_at' => 'nullable|multiple_date_format:d-m-Y H:i:s,d-m-Y|after:now|before:10 years',
        ]);

        Voucher::create($request->except('_token'));

        return redirect()->route('admin.vouchers.index')->with('success', __('voucher has been created!'));
    }

    /**
     * Display the specified resource.
     *
     * @param  Voucher  $voucher
     * @return Response
     */
    public function show(Voucher $voucher)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  Voucher  $voucher
     * @return Application|Factory|View
     */
    public function edit(Voucher $voucher, GeneralSettings $general_settings)
    {
        $this->checkPermission(self::WRITE_PERMISSION);
        return view('admin.vouchers.edit', [
            'voucher' => $voucher,
            'credits_display_name' => $general_settings->credits_display_name
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  Request  $request
     * @param  Voucher  $voucher
     * @return RedirectResponse
     */
    public function update(Request $request, Voucher $voucher)
    {
        $request->validate([
            'memo' => 'nullable|string|max:191',
            'code' => "required|string|alpha_dash|max:36|min:4|unique:vouchers,code,{$voucher->id}",
            'uses' => 'required|numeric|max:2147483647|min:1',
            'credits' => 'required|numeric|between:0,99999999',
            'expires_at' => 'nullable|multiple_date_format:d-m-Y H:i:s,d-m-Y|after:now|before:10 years',
        ]);

        $voucher->update($request->except('_token'));

        return redirect()->route('admin.vouchers.index')->with('success', __('voucher has been updated!'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  Voucher  $voucher
     * @return RedirectResponse
     */
    public function destroy(Voucher $voucher)
    {
        $this->checkPermission(self::WRITE_PERMISSION);
        $voucher->delete();

        return redirect()->back()->with('success', __('voucher has been removed!'));
    }

    public function users(Voucher $voucher, LocaleSettings $locale_settings, GeneralSettings $general_settings)
    {
        $this->checkPermission(self::READ_PERMISSION);

        return view('admin.vouchers.users', [
            'voucher' => $voucher,
            'locale_datatables' => $locale_settings->datatables,
            'credits_display_name' => $general_settings->credits_display_name
        ]);
    }

    /**
     * @param  Request  $request
     * @return JsonResponse
     *
     * @throws ValidationException
     */
    public function redeem(Request $request, GeneralSettings $general_settings)
    {
        //general validations
        $request->validate([
            'code' => 'required|exists:vouchers,code',
        ]);

        //get voucher by code
        $voucher = Voucher::where('code', '=', $request->input('code'))->firstOrFail();

        //extra validations
        if ($voucher->getStatus() == 'USES_LIMIT_REACHED') {
            throw ValidationException::withMessages([
                'code' => __('This voucher has reached the maximum amount of uses'),
            ]);
        }

        if ($voucher->getStatus() == 'EXPIRED') {
            throw ValidationException::withMessages([
                'code' => __('This voucher has expired'),
            ]);
        }

        if (! $request->user()->vouchers()->where('id', '=', $voucher->id)->get()->isEmpty()) {
            throw ValidationException::withMessages([
                'code' => __('You already redeemed this voucher code'),
            ]);
        }

        if ($request->user()->credits + $voucher->credits >= 99999999) {
            throw ValidationException::withMessages([
                'code' => "You can't redeem this voucher because you would exceed the  limit of " . $general_settings->credits_display_name,
            ]);
        }

        //redeem voucher
        $voucher->redeem($request->user());

        event(new UserUpdateCreditsEvent($request->user()));

        return response()->json([
            'success' => "{$voucher->credits} ". $general_settings->credits_display_name .' '.__('have been added to your balance!'),
        ]);
    }

    public function usersDataTable(Voucher $voucher)
    {
        $users = $voucher->users();

        return datatables($users)
            ->editColumn('name', function (User $user) {
                return '<a class="text-info" target="_blank" href="'.route('admin.users.show', $user->id).'">'.$user->name.'</a>';
            })
            ->addColumn('credits', function (User $user) {
                return '<i class="mr-2 fas fa-coins"></i> '.$user->credits();
            })
            ->addColumn('last_seen', function (User $user) {
                return $user->last_seen ? $user->last_seen->diffForHumans() : '';
            })
            ->rawColumns(['name', 'credits', 'last_seen'])
            ->make();
    }

    public function dataTable()
    {
        $query = Voucher::selectRaw('
            vouchers.*,
            CASE
                WHEN (SELECT COUNT(*) FROM user_voucher WHERE user_voucher.voucher_id = vouchers.id) >= vouchers.uses THEN "USES_LIMIT_REACHED"
                WHEN vouchers.expires_at IS NOT NULL AND vouchers.expires_at < NOW() THEN "EXPIRED"
                ELSE "VALID"
            END as derived_status
        ');

        return datatables($query)
            ->addColumn('actions', function (Voucher $voucher) {
                return '
                            <a data-content="'.__('Users').'" data-toggle="popover" data-trigger="hover" data-placement="top" href="'.route('admin.vouchers.users', $voucher->id).'" class="mr-1 btn btn-sm btn-primary"><i class="fas fa-users"></i></a>
                            <a data-content="'.__('Edit').'" data-toggle="popover" data-trigger="hover" data-placement="top" href="'.route('admin.vouchers.edit', $voucher->id).'" class="mr-1 btn btn-sm btn-info"><i class="fas fa-pen"></i></a>

                           <form class="d-inline" onsubmit="return submitResult();" method="post" action="'.route('admin.vouchers.destroy', $voucher->id).'">
                            '.csrf_field().'
                            '.method_field('DELETE').'
                           <button data-content="'.__('Delete').'" data-toggle="popover" data-trigger="hover" data-placement="top" class="mr-1 btn btn-sm btn-danger"><i class="fas fa-trash"></i></button>
                       </form>
                ';
            })
            ->addColumn('status', function (Voucher $voucher) {
                $color = ($voucher->derived_status == 'VALID') ? 'success' : 'danger';
                $status = str_replace('_', ' ', $voucher->derived_status);

                return '<span class="badge badge-'.$color.'">'.$status.'</span>';
            })
            ->editColumn('uses', function (Voucher $voucher) {
                return "{$voucher->used} / {$voucher->uses}";
            })
            ->editColumn('credits', function (Voucher $voucher) {
                return number_format($voucher->credits, 2, '.', '');
            })
            ->editColumn('expires_at', function (Voucher $voucher) {
                if (! $voucher->expires_at) {
                    return __("Never");
                }

                return $voucher->expires_at ? $voucher->expires_at->diffForHumans() : __("Never");
            })
            ->editColumn('code', function (Voucher $voucher) {
                return "<code>{$voucher->code}</code>";
            })
            ->orderColumn('status', 'derived_status $1')
            ->rawColumns(['actions', 'code', 'status'])
            ->make();
    }
}
