<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\CurrencyHelper;
use App\Http\Controllers\Controller;
use App\Models\Coupon;
use App\Settings\LocaleSettings;
use App\Traits\Coupon as CouponTrait;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

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

        $validated = $request->validate($rules);

        if (array_key_exists('range_codes', $rules)) {
            $data = $validated;
            $data['max_uses_per_user'] = $this->normalizeMaxUsesPerUser($request);
            DB::transaction(function () use ($data, $random_codes_amount) {
                $coupons = Coupon::generateRandomCoupon($random_codes_amount);

                foreach ($coupons as $coupon) {
                    Coupon::create([
                        'code' => $coupon,
                        'type' => $data['type'],
                        'value' => $data['value'],
                        'max_uses' => $data['max_uses'],
                        'max_uses_per_user' => $data['max_uses_per_user'],
                        'expires_at' => $data['expires_at'] ?? null,
                    ]);
                }
            });
        } else {
            $data = $validated;
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
        abort(404);
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
            'expired_at' => $coupon->expires_at ? Carbon::createFromTimestamp((int) $coupon->expires_at) : null
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

        $coupon_code = $request->input('code');
        $random_codes_amount = $request->input('range_codes');
        $rules = $this->requestRules($request, $coupon);

        // If for some reason you pass both fields at once.
        if ($coupon_code && $random_codes_amount) {
            return redirect()->back()->with('error', __('Only one of the two code inputs must be provided.'))->withInput($request->all());
        }

        if (!$coupon_code && !$random_codes_amount) {
            return redirect()->back()->with('error', __('At least one of the two code inputs must be provided.'))->withInput($request->all());
        }

        $validated = $request->validate($rules);
        $data = $validated;
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
        $coupon->delete();

        return redirect()->back()->with('success', __('coupon has been removed!'));
    }

    private function requestRules(Request $request, ?Coupon $coupon = null)
    {
        $coupon_code = $request->input('code');
        $random_codes_amount = $request->input('range_codes');
        $valueRules = $request->input('type') === 'amount'
            ? ['required', 'numeric', 'min:0', 'max:99999999']
            : ['required', 'numeric', 'between:0,100'];

        $rules = [
            "type" => "required|string|in:percentage,amount",
               // Set to -1 for unlimited uses globally, or a positive integer within DB range.
               "max_uses" => [
               'required',
               'integer',
               function ($attribute, $value, $fail) {
                   if ((int)$value === -1) {
                   return;
                   }
                   if ((int)$value <= 0 || (int)$value > 2147483647) {
                   $fail(__('Max uses must be -1 for unlimited or a positive integer up to :max.', ['max' => 2147483647]));
                   }
               }
               ],
               // Set to -1 for unlimited per user. Empty falls back to coupon settings.
               "max_uses_per_user" => [
               'nullable',
               'integer',
               function ($attribute, $value, $fail) {
                   if ((int)$value === -1 || is_null($value)) {
                   return;
                   }
                   if ((int)$value <= 0 || (int)$value > 2147483647) {
                   $fail(__('Max uses per user must be -1 for unlimited, or a positive integer up to :max.', ['max' => 2147483647]));
                   }
               }
               ],
            "value" => $valueRules,
            "expires_at" => "nullable|date|after:" . Carbon::now()->format(Coupon::formatDate())
        ];

        if ($coupon_code) {
            $codeRule = Rule::unique('coupons', 'code');
            if ($coupon) {
                $codeRule = $codeRule->ignore($coupon->id);
            }

            $rules['code'] = ['required', 'string', 'min:4', $codeRule];
        } elseif ($random_codes_amount) {
            $rules['range_codes'] = 'required|integer|min:1|max:100';
        }

        return $rules;
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
        $this->checkAnyPermission([self::READ_PERMISSION, self::WRITE_PERMISSION]);

        $query = Coupon::selectRaw('
            coupons.*,
            CASE
                   WHEN coupons.max_uses != -1 AND coupons.uses >= coupons.max_uses THEN "USES_LIMIT_REACHED"
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
                 $maxUses = $coupon->max_uses == -1 ? '∞' : $coupon->max_uses;

                 return "{$coupon->uses} / {$maxUses}";
            })
            ->editColumn('value', function (Coupon $coupon, CurrencyHelper $currencyHelper) {
                if ($coupon->type === 'percentage') {
                    return $coupon->value . "%";
                }

                return $currencyHelper->formatForDisplay($coupon->value);
            })
            ->editColumn('expires_at', function (Coupon $coupon) {
                if (!$coupon->expires_at) {
                    return __('Never');
                }

                return Carbon::createFromTimestamp((int) $coupon->expires_at);
            })
            ->editColumn('created_at', function(Coupon $coupon) {
                return $coupon->created_at ? $coupon->created_at->diffForHumans() : '';
            })
            ->editColumn('code', function (Coupon $coupon) {
                return "<code>{$coupon->code}</code>";
            })
            ->orderColumn('status', 'derived_status $1')
            ->rawColumns(['actions', 'code', 'status'])
            ->make();
    }
}
