<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\CurrencyHelper;
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
        $this->checkAnyPermission([self::WRITE_PERMISSION, self::READ_PERMISSION]);

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
    public function store(Request $request, CurrencyHelper $currencyHelper)
    {
        $this->checkPermission(self::WRITE_PERMISSION);

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

            $value = $request->input('value');
            if ($request->input('type') === 'amount') {
                $value = $currencyHelper->prepareForDatabase($value);
            }
            $min_product_price = $currencyHelper->prepareForDatabase($request->input('min_product_price'));

            // Scroll through all the randomly generated coupons.
            foreach ($coupons as $coupon) {
                $data[] = [
                    'code' => $coupon,
                    'type' => $request->input('type'),
                    'value' => $value,
                    'min_product_price' => $min_product_price,
                    'max_uses' => $request->input('max_uses'),
                    'max_uses_per_user' => $this->normalizeMaxUsesPerUser($request),
                    'expires_at' => $request->input('expires_at'),
                    'created_at' => Carbon::now(), // Does not fill in by itself when using the 'insert' method.
                    'updated_at' => Carbon::now()
                ];
            }
            Coupon::insert($data);
        } else {
            $data = $request->except('_token');
            $data['max_uses_per_user'] = $this->normalizeMaxUsesPerUser($request);
            Coupon::create($data);
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
        $this->checkPermission(self::WRITE_PERMISSION);

        $rules = $this->requestRules($request);

        $request->validate($rules);
        $data = $request->except('_token');
        $data['max_uses_per_user'] = $this->normalizeMaxUsesPerUser($request);
        $coupon->update($data);

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

        if ($coupon->pendingUses() > 0) {
            return redirect()->back()->with('error', __('This coupon cannot be deleted because there are pending payments using it.'));
        }

        \DB::transaction(function () use ($coupon) {
            $coupon->delete();
        });

        return redirect()->back()->with('success', __('coupon has been removed!'));
    }

    private function requestRules(Request $request)
    {
        return [
            "code" => "required|string|min:4",
            "type" => "required|string|in:percentage,amount",
            // Set to -1 for unlimited uses globally, or a positive integer within DB range.
            "max_uses" => [
                'required',
                'integer',
                function ($attribute, $value, $fail) {
                    if ((int) $value === -1) {
                        return;
                    }
                    if ((int) $value <= 0 || (int) $value > 2147483647) {
                        $fail(__('Max uses must be -1 for unlimited or a positive integer up to :max.', ['max' => 2147483647]));
                    }
                }
            ],
            // Set to -1 for unlimited per user. Empty falls back to coupon settings.
            "max_uses_per_user" => [
                'nullable',
                'integer',
                function ($attribute, $value, $fail) {
                    if ((int) $value === -1 || is_null($value)) {
                        return;
                    }
                    if ((int) $value <= 0 || (int) $value > 2147483647) {
                        $fail(__('Max uses per user must be -1 for unlimited, or a positive integer up to :max.', ['max' => 2147483647]));
                    }
                }
            ],
            "value" => $request->input('type') === 'percentage' ? 'required|numeric|between:1,100' : 'required|numeric|min:0.01|max:9007199254740991',
            "min_product_price" => "required|numeric|min:0|max:9007199254740991",
            "expires_at" => "nullable|date|after:now"
        ];
    }

    /**
     * Normalize optional per-user max uses input.
     * Empty input means fallback to global coupon settings.
     */
    private function normalizeMaxUsesPerUser(Request $request): ?int
    {
        $value = $request->input('max_uses_per_user');

        if ($value === null || $value === '') {
            return null;
        }

        return (int) $value;
    }

    public function redeem(Request $request)
    {
        return $this->validateCoupon($request->user(), $request->input('couponCode'), $request->input('productId'));
    }

    public function dataTable()
    {
        $this->checkAnyPermission([self::WRITE_PERMISSION, self::READ_PERMISSION]);

        $query = Coupon::query();

        return datatables($query)
            ->addColumn('actions', function (Coupon $coupon) {
                return '
                    <a data-content="' . __('Edit') . '" data-toggle="popover" data-trigger="hover" data-placement="top" href="' . route('admin.coupons.edit', $coupon->id) . '" class="mr-1 btn btn-sm btn-info"><i class="fas fa-pen"></i></a>

                    <form class="d-inline" onsubmit="return submitResult();" method="post" action="' . route('admin.coupons.destroy', $coupon->id) . '">
                        ' . csrf_field() . '
                        ' . method_field('DELETE') . '
                        <button data-content="' . __('Delete') . '" data-toggle="popover" data-trigger="hover" data-placement="top" class="mr-1 btn btn-sm btn-danger"><i class="fas fa-trash"></i></button>
                    </form>
                ';
            })
            ->addColumn('status', function (Coupon $coupon) {
                $derivedStatus = $coupon->getStatus();
                $color = 'success';

                if ($derivedStatus === 'USES_LIMIT_REACHED' || $derivedStatus === 'EXPIRED') {
                    $color = 'danger';
                } elseif ($derivedStatus === 'PENDING_LIMIT_REACHED') {
                    $color = 'warning';
                }

                $status = str_replace('_', ' ', $derivedStatus);

                return '<span class="badge badge-' . $color . '">' . __($status) . '</span>';
            })
            ->editColumn('uses', function (Coupon $coupon) {
                $maxUses = $coupon->max_uses == -1 ? '∞' : $coupon->max_uses;
                $pending = $coupon->pendingUses();
                $pendingText = $pending > 0 ? " (+{$pending})" : "";

                return "{$coupon->uses}{$pendingText} / {$maxUses}";
            })
            ->editColumn('value', function (Coupon $coupon, CurrencyHelper $currencyHelper) {
                if ($coupon->type === 'percentage') {
                    return $coupon->value . "%";
                }

                return $currencyHelper->formatForDisplay($coupon->value);
            })
            ->editColumn('min_product_price', function (Coupon $coupon, CurrencyHelper $currencyHelper) {
                return $currencyHelper->formatForDisplay($coupon->min_product_price);
            })
            ->editColumn('expires_at', function (Coupon $coupon) {
                if (!$coupon->expires_at) {
                    return __('Never');
                }

                return Carbon::createFromTimestamp($coupon->expires_at);
            })
            ->editColumn('created_at', function (Coupon $coupon) {
                return Carbon::createFromTimeString($coupon->created_at);
            })
            ->editColumn('code', function (Coupon $coupon) {
                return "<code>{$coupon->code}</code>";
            })
            ->rawColumns(['actions', 'code', 'status'])
            ->make();
    }
}
