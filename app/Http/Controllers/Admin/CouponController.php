<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Coupon;
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
    public function index()
    {
        $this->checkPermission(self::READ_PERMISSION);

        return view('admin.coupons.index');
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
        $coupon_code = $request->input('coupon_code');
        $coupon_type = $request->input('coupon_type');
        $coupon_value = $request->input('coupon_value');
        $coupon_max_uses = $request->input('coupon_uses');
        $coupon_datepicker = $request->input('datepicker');
        $random_codes_amount = $request->input('range_codes');
        $rules = [
            "coupon_type" => "required|string|in:percentage,amount",
            "coupon_uses" => "required|integer|digits_between:1,100",
            "coupon_value" => "required|numeric|between:0,100",
            "datepicker" => "required|date|after:" . Carbon::now()->format(Coupon::formatDate())
        ];

        // If for some reason you pass both fields at once.
        if ($coupon_code && $random_codes_amount) {
            return redirect()->back()->with('error', __('Only one of the two code inputs must be provided.'))->withInput($request->all());
        }

        if (!$coupon_code && !$random_codes_amount) {
            return redirect()->back()->with('error', __('At least one of the two code inputs must be provided.'))->withInput($request->all());
        }

        if ($coupon_code) {
            $rules['coupon_code'] = 'required|string|min:4';
        } elseif ($random_codes_amount) {
            $rules['range_codes'] = 'required|integer|digits_between:1,100';
        }

        $request->validate($rules);

        if (array_key_exists('range_codes', $rules)) {
            $data = [];
            $coupons = Coupon::generateRandomCoupon($random_codes_amount);

            // Scroll through all the randomly generated coupons.
            foreach ($coupons as $coupon) {
                $data[] = [
                    'code' => $coupon,
                    'type' => $coupon_type,
                    'value' => $coupon_value,
                    'max_uses' => $coupon_max_uses,
                    'expires_at' => $coupon_datepicker,
                    'created_at' => Carbon::now(), // Does not fill in by itself when using the 'insert' method.
                    'updated_at' => Carbon::now()
                ];
            }
            Coupon::insert($data);
        } else {
            Coupon::create([
                'code' => $coupon_code,
                'type' => $coupon_type,
                'value' => $coupon_value,
                'max_uses' => $coupon_max_uses,
                'expires_at' => $coupon_datepicker,
            ]);
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
        //
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
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Coupon  $coupon
     * @return \Illuminate\Http\Response
     */
    public function destroy(Coupon $coupon)
    {
        //
    }

    public function redeem(Request $request)
    {
        return $this->validateCoupon($request);
    }
}
