<?php

namespace App\Http\Controllers\Api;

use App\Models\Voucher;
use App\Http\Resources\VoucherResource;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Vouchers\CreateVoucherRequest;
use App\Http\Requests\Api\Vouchers\UpdateVoucherRequest;
use Spatie\QueryBuilder\QueryBuilder;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;

/**
 * @group Voucher Management
 */

class VoucherController extends Controller
{
    const ALLOWED_INCLUDES = ['users'];
    const ALLOWED_FILTERS = ['code', 'memo', 'credits', 'uses'];

    /**
     * List all vouchers
     *
     * @response {
     *  "data": [
     *    {
     *      "id": 1,
     *      "code": "SUMMER2026",
     *      "memo": "Summer promotion",
     *      "credits": "50.00",
     *      "uses": 100,
     *      "expires_at": "2026-12-31 23:59:59",
     *      "created_at": "2026-04-26 12:00:00",
     *      "updated_at": "2026-04-26 12:00:00"
     *    }
     *  ],
     *  "meta": { "total": 1 }
     * }
     *
     * @param Request $request
     * @return VoucherResource
     */
    public function index(Request $request)
    {
        $vouchers = QueryBuilder::for(Voucher::class)
            ->allowedIncludes(self::ALLOWED_INCLUDES)
            ->allowedFilters(self::ALLOWED_FILTERS)
            ->paginate($request->input('per_page') ?? 50);

        return VoucherResource::collection($vouchers);
    }

    /**
     * Create voucher
     *
     * @bodyParam memo string Description for the voucher. Example: Summer 2026 Promotion
     * @bodyParam code string required 4-36 chars, alpha-dash format. Example: SUMMER2026
     * @bodyParam uses integer required Max uses. Example: 100
     * @bodyParam credits number required Credits amount. Min: 0.01, Max: 9223372036854775. Example: 50.00
     * @bodyParam expires_at string Expiration date (d-m-Y H:i:s or d-m-Y). Example: 31-12-2026 23:59:59
     *
     * @response {
     *  "data": {
     *      "id": 1,
     *      "code": "SUMMER2026",
     *      "memo": "Summer promotion",
     *      "credits": "50.00",
     *      "uses": 100,
     *      "expires_at": "2026-12-31 23:59:59",
     *      "created_at": "2026-04-26 12:00:00",
     *      "updated_at": "2026-04-26 12:00:00"
     *  }
     * }
     *
     * @param  CreateVoucherRequest  $request
     * @return VoucherResource
     */
    public function store(CreateVoucherRequest $request)
    {
        $data = $request->validated();

        $voucher = Voucher::create($data);

        return VoucherResource::make($voucher);
    }

    /**
     * Get voucher details
     *
     * @urlParam id integer required The ID of the voucher. Example: 1
     *
     * @response {
     *  "data": {
     *      "id": 1,
     *      "code": "SUMMER2026",
     *      "memo": "Summer promotion",
     *      "credits": "50.00",
     *      "uses": 100,
     *      "expires_at": "2026-12-31 23:59:59",
     *      "created_at": "2026-04-26 12:00:00",
     *      "updated_at": "2026-04-26 12:00:00"
     *  }
     * }
     *
     * @param Request $request
     * @param  int  $voucher
     * @return VoucherResource
     *
     * @throws ModelNotFoundException
     */
    public function show(Request $request, int $voucher)
    {
        $voucherQuery = QueryBuilder::for(Voucher::class)
            ->allowedIncludes(self::ALLOWED_INCLUDES)
            ->where('id', $voucher)
            ->firstOrFail();

        return VoucherResource::make($voucherQuery);
    }

    /**
     * Update voucher
     *
     * @urlParam id integer required The ID of the voucher. Example: 1
     * @bodyParam memo string A description for the voucher. Example: Summer 2026 Promotion
     * @bodyParam code string The unique code for the voucher. Example: SUMMER2026
     * @bodyParam uses integer required The number of times the voucher can be used. Example: 100
     * @bodyParam credits number required The amount of credits the voucher gives. Example: 50.00
     * @bodyParam expires_at string The expiration date of the voucher (d-m-Y H:i:s or d-m-Y). Example: 31-12-2026 23:59:59
     *
     * @response {
     *  "data": {
     *      "id": 1,
     *      "code": "SUMMER2026",
     *      "memo": "Summer 2026 promotion",
     *      "credits": "50.00",
     *      "uses": 100,
     *      "expires_at": "2026-12-31 23:59:59",
     *      "created_at": "2026-04-26 12:00:00",
     *      "updated_at": "2026-04-26 12:00:00"
     *  }
     * }
     *
     * @param  Request  $request
     * @param  Voucher  $voucher
     * @return VoucherResource
     *
     * @throws ModelNotFoundException
     */
    public function update(UpdateVoucherRequest $request, Voucher $voucher)
    {
        $data = $request->validated();

        $voucher->update($data);

        return VoucherResource::make($voucher->fresh());
    }

    /**
     * Delete voucher
     *
     * @response 204 {}
     *
     * @param  Request  $request
     * @param  Voucher  $voucher
     * @return \Illuminate\Http\Response
     *
     * @throws ModelNotFoundException
     */
    public function destroy(Request $request, Voucher $voucher)
    {
        $voucher->delete();

        return response()->noContent();
    }
}
