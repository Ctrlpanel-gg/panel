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
     * Show a list of vouchers.
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
     * Store a new voucher in the system.
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
     * @param  Request  $request
     * @return VoucherResource
     */
    public function store(CreateVoucherRequest $request)
    {
        $data = $request->validated();
        
        $voucher = Voucher::create($data);

        return VoucherResource::make($voucher);
    }

    /**
     * Show the specified voucher.
     *
     * @urlParam voucher integer required The ID of the voucher. Example: 1
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
     * @queryParam include string Comma-separated list of related resources to include. Example: users
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
     * Update the specified voucher in the system.
     *
     * @urlParam voucher integer required The ID of the voucher. Example: 1
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
     * Remove the specified voucher from the system.
     *
     * @urlParam voucher integer required The ID of the voucher. Example: 1
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
