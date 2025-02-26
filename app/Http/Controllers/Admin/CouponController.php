<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Coupon;
use App\Settings\LocaleSettings;
use App\Traits\Coupon as CouponTrait;
use Illuminate\Http\Request;
use Carbon\Carbon;

class CouponController extends Controller
{
    const READ_PERMISSION = "admin.coupons.read";
    const WRITE_PERMISSION = "admin.coupons.write";

    use CouponTrait;

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(LocaleSettings $localeSettings)
    {
        $this->checkAnyPermission([self::WRITE_PERMISSION,self::READ_PERMISSION]);

        return view('admin.coupons.index', [
            'locale_datatables' => $localeSettings->datatables
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $this->checkPermission(self::WRITE_PERMISSION);

        return view('admin.coupons.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $coupon_code = $request->input('code');
        $random_codes_amount = $request->input('range_codes');
        $rules = $this->requestRules($request);

         // If for some reason you pass both fields at once.
         if ($coupon_code && $random_codes_amount) {
            return redirect()->back()->with('error', __('Only one of the two code inputs must be provided.'))->withInput($request->all());
        }

        if (!$coupon_code && !$random_codes_amount) {
            return redirect()->back()->with('error', __('At least one of the two code inputs must be provided.'))->withInput($request->all());
        }

        $request->validate($rules);

        if (array_key_exists('range_codes', $rules)) {
            $data = [];
            $coupons = Coupon::generateRandomCoupon($random_codes_amount);

            // Scroll through all the randomly generated coupons.
            foreach ($coupons as $coupon) {
                $data[] = [
                    'code' => $coupon,
                    'type' => $request->input('type'),
                    'value' => $request->input('value'),
                    'max_uses' => $request->input('max_uses'),
                    'expires_at' => $request->input('expires_at'),
                    'created_at' => Carbon::now(), // Does not fill in by itself when using the 'insert' method.
                    'updated_at' => Carbon::now()
                ];
            }
            Coupon::insert($data);
        } else {
            Coupon::create($request->except('_token'));
        }

        return redirect()->route('admin.coupons.index')->with('success', __("The coupon's was registered successfully."));
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Coupon  $coupon
     * @return \Illuminate\Http\Response
     */
    public function show(Coupon $coupon)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Coupon  $coupon
     * @return \Illuminate\Http\Response
     */
    public function edit(Coupon $coupon)
    {
        $this->checkPermission(self::WRITE_PERMISSION);

        return view('admin.coupons.edit', [
            'coupon' => $coupon,
            'expired_at' => $coupon->expires_at ? Carbon::createFromTimestamp($coupon->expires_at) : null
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Coupon  $coupon
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Coupon $coupon)
    {
        $coupon_code = $request->input('code');
        $random_codes_amount = $request->input('range_codes');
        $rules = $this->requestRules($request);

        // If for some reason you pass both fields at once.
        if ($coupon_code && $random_codes_amount) {
            return redirect()->back()->with('error', __('Only one of the two code inputs must be provided.'))->withInput($request->all());
        }

        if (!$coupon_code && !$random_codes_amount) {
            return redirect()->back()->with('error', __('At least one of the two code inputs must be provided.'))->withInput($request->all());
        }

        $request->validate($rules);
        $coupon->update($request->except('_token'));

        return redirect()->route('admin.coupons.index')->with('success', __('coupon has been updated!'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Coupon  $coupon
     * @return \Illuminate\Http\Response
     */
    public function destroy(Coupon $coupon)
    {
        $this->checkPermission(self::WRITE_PERMISSION);
        $coupon->delete();

        return redirect()->back()->with('success', __('coupon has been removed!'));
    }

    private function requestRules(Request $request)
    {
        $coupon_code = $request->input('code');
        $random_codes_amount = $request->input('range_codes');
        $rules = [
            "type" => "required|string|in:percentage,amount",
            "max_uses" => "required|integer|digits_between:1,100",
            "value" => "required|numeric|between:0,100",
            "expires_at" => "nullable|date|after:" . Carbon::now()->format(Coupon::formatDate())
        ];

        if ($coupon_code) {
            $rules['code'] = "required|string|min:4";
        } elseif ($random_codes_amount) {
            $rules['range_codes'] = 'required|integer|digits_between:1,100';
        }

        return $rules;
    }

    public function redeem(Request $request)
    {
        return $this->validateCoupon($request->user(), $request->input('couponCode'), $request->input('productId'));
    }

    public function dataTable()
    {
        $query = Coupon::selectRaw('
            coupons.*,
            CASE
                WHEN coupons.uses >= coupons.max_uses THEN "USES_LIMIT_REACHED"
                WHEN coupons.expires_at IS NOT NULL AND coupons.expires_at < NOW() THEN "EXPIRED"
                ELSE "VALID"
            END as derived_status
        ');

        return datatables($query)
            ->addColumn('actions', function(Coupon $coupon) {
                return '
                    <a data-content="'.__('Edit').'" data-toggle="popover" data-trigger="hover" data-placement="top" href="'.route('admin.coupons.edit', $coupon->id).'" class="mr-1 btn btn-sm btn-info"><i class="fas fa-pen"></i></a>

                    <form class="d-inline" onsubmit="return submitResult();" method="post" action="'.route('admin.coupons.destroy', $coupon->id).'">
                        '.csrf_field().'
                        '.method_field('DELETE').'
                        <button data-content="'.__('Delete').'" data-toggle="popover" data-trigger="hover" data-placement="top" class="mr-1 btn btn-sm btn-danger"><i class="fas fa-trash"></i></button>
                    </form>
                ';
            })
            ->addColumn('status', function (Coupon $coupon) {
                $color = ($coupon->derived_status == 'VALID') ? 'success' : 'danger';
                $status = str_replace('_', ' ', $coupon->derived_status);

                return '<span class="badge badge-'.$color.'">'.$status.'</span>';
            })
            ->editColumn('uses', function (Coupon $coupon) {
                return "{$coupon->uses} / {$coupon->max_uses}";
            })
            ->editColumn('value', function (Coupon $coupon) {
                if ($coupon->type === 'percentage') {
                    return $coupon->value . "%";
                }

                return number_format($coupon->value, 2, '.', '');
            })
            ->editColumn('expires_at', function (Coupon $coupon) {
                if (!$coupon->expires_at) {
                    return __('Never');
                }

                return Carbon::createFromTimestamp($coupon->expires_at);
            })
            ->editColumn('created_at', function(Coupon $coupon) {
                return Carbon::createFromTimeString($coupon->created_at);
            })
            ->editColumn('code', function (Coupon $coupon) {
                return "<code>{$coupon->code}</code>";
            })
            ->orderColumn('status', 'derived_status $1')
            ->rawColumns(['actions', 'code', 'status'])
            ->make();
    }
}
