<?php

namespace App\Http\Controllers;

use App\Models\PartnerDiscount;
use App\Models\User;
use Illuminate\Http\Request;

class PartnerController extends Controller
{
    public function index()
    {
        return view('admin.partners.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Application|Factory|View
     */
    public function create()
    {
        return view('admin.partners.create', [
            'partners'  =>PartnerDiscount::get(),
            'users'     => User::orderBy('name')->get()
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     * @return RedirectResponse
     */
    public function store(Request $request)
    {
        $request->validate([
            'user_id'                   => 'required|integer|min:0',
            'partner_discount'          => 'required|integer|max:100|min:0',
            'registered_user_discount'  => 'required|integer|max:100|min:0'
        ]);

        PartnerDiscount::create($request->all());

        return redirect()->route('admin.partners.index')->with('success', __('partner has been created!'));
    }

    /**
     * Display the specified resource.
     *
     * @param Voucher $voucher
     * @return Response
     */
    public function show(Voucher $voucher)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param Voucher $voucher
     * @return Application|Factory|View
     */
    public function edit(PartnerDiscount $partner)
    {
        return view('admin.partners.edit', [
            'partners'  =>PartnerDiscount::get(),
            'partner'   => $partner,
            'users'     => User::orderBy('name')->get()
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param Voucher $voucher
     * @return RedirectResponse
     */
    public function update(Request $request, PartnerDiscount $partner)
    {
        //dd($request);
        $request->validate([
            'user_id'                   => 'required|integer|min:0',
            'partner_discount'          => 'required|integer|max:100|min:0',
            'registered_user_discount'  => 'required|integer|max:100|min:0'
        ]);

        $partner->update($request->all());

        return redirect()->route('admin.partners.index')->with('success', __('partner has been updated!'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param Voucher $voucher
     * @return RedirectResponse
     */
    public function destroy(PartnerDiscount $partner)
    {
        $partner->delete();
        return redirect()->back()->with('success', __('partner has been removed!'));
    }

    public function users(Voucher $voucher)
    {
        return view('admin.vouchers.users', [
            'voucher' => $voucher
        ]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     * @throws ValidationException
     */
    public function redeem(Request $request)
    {
        #general validations
        $request->validate([
            'code' => 'required|exists:vouchers,code'
        ]);

        #get voucher by code
        $voucher = Voucher::where('code', '=', $request->input('code'))->firstOrFail();

        #extra validations
        if ($voucher->getStatus() == 'USES_LIMIT_REACHED') throw ValidationException::withMessages([
            'code' => __('This voucher has reached the maximum amount of uses')
        ]);

        if ($voucher->getStatus() == 'EXPIRED') throw ValidationException::withMessages([
            'code' => __('This voucher has expired')
        ]);

        if (!$request->user()->vouchers()->where('id', '=', $voucher->id)->get()->isEmpty()) throw ValidationException::withMessages([
            'code' => __('You already redeemed this voucher code')
        ]);

        if ($request->user()->credits + $voucher->credits >= 99999999) throw ValidationException::withMessages([
            'code' => "You can't redeem this voucher because you would exceed the  limit of " . CREDITS_DISPLAY_NAME
        ]);

        #redeem voucher
        $voucher->redeem($request->user());

        event(new UserUpdateCreditsEvent($request->user()));

        return response()->json([
            'success' => "{$voucher->credits} " . CREDITS_DISPLAY_NAME ." ". __("have been added to your balance!")
        ]);
    }

    public function usersDataTable(Voucher $voucher)
    {
        $users = $voucher->users();

        return datatables($users)
            ->editColumn('name', function (User $user) {
                return '<a class="text-info" target="_blank" href="' . route('admin.users.show', $user->id) . '">' . $user->name . '</a>';
            })
            ->addColumn('credits', function (User $user) {
                return '<i class="fas fa-coins mr-2"></i> ' . $user->credits();
            })
            ->addColumn('last_seen', function (User $user) {
                return $user->last_seen ? $user->last_seen->diffForHumans() : '';
            })
            ->rawColumns(['name', 'credits', 'last_seen'])
            ->make();
    }
    public function dataTable()
    {
        $query = PartnerDiscount::query();

        return datatables($query)
            ->addColumn('actions', function (PartnerDiscount $partner) {
                return '
                            <a data-content="'.__("Edit").'" data-toggle="popover" data-trigger="hover" data-placement="top" href="' . route('admin.partners.edit', $partner->id) . '" class="btn btn-sm btn-info mr-1"><i class="fas fa-pen"></i></a>
                           <form class="d-inline" onsubmit="return submitResult();" method="post" action="' . route('admin.partners.destroy', $partner->id) . '">
                            ' . csrf_field() . '
                            ' . method_field("DELETE") . '
                           <button data-content="'.__("Delete").'" data-toggle="popover" data-trigger="hover" data-placement="top" class="btn btn-sm btn-danger mr-1"><i class="fas fa-trash"></i></button>
                       </form>
                ';
            })
            ->addColumn('user', function (PartnerDiscount $partner) {
                return ($user=User::where('id', $partner->user_id)->first())?'<a href="'.route('admin.users.show', $partner->user_id).'">'.$user->name.'</a>':__('Unknown user');
            })
            ->editColumn('created_at', function (PartnerDiscount $partner) {
                return $partner->created_at ? $partner->created_at->diffForHumans() : '';
            })
            ->editColumn('partner_discount', function (PartnerDiscount $partner) {
                return $partner->partner_discount ? $partner->partner_discount . "%" : "0%";
            })
            ->editColumn('registered_user_discount', function (PartnerDiscount $partner) {
                return $partner->registered_user_discount ? $partner->registered_user_discount . "%" : "0%";
            })
            ->rawColumns(['user', 'actions'])
            ->make();
    }
}