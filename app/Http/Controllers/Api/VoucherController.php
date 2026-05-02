<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Exceptions\ApiErrorCode;
use App\Models\Voucher;
use App\Http\Resources\VoucherResource;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Vouchers\CreateVoucherRequest;
use App\Http\Requests\Api\Vouchers\UpdateVoucherRequest;
use App\Services\ApiResponseService;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\QueryBuilder\AllowedSort;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class VoucherController extends Controller
{
    const ALLOWED_INCLUDES = ['users'];
    const ALLOWED_FILTERS = ['code', 'memo', 'credits', 'uses'];
    const ALLOWED_SORTS = ['id', 'code', 'credits', 'uses', 'created_at', 'updated_at'];

    /**
     * Show a list of vouchers.
     *
     * @param Request $request
     * @return VoucherResource
     */
    public function index(Request $request)
    {
        $perPage = min((int) $request->input('per_page', 50), 100);

        $vouchers = QueryBuilder::for(Voucher::class)
            ->allowedIncludes(self::ALLOWED_INCLUDES)
            ->allowedFilters(self::ALLOWED_FILTERS)
            ->allowedSorts(self::ALLOWED_SORTS)
            ->paginate($perPage);

        return ApiResponseService::success(
            VoucherResource::collection($vouchers)->toArray($request),
            [
                'current_page' => $vouchers->currentPage(),
                'total' => $vouchers->total(),
                'last_page' => $vouchers->lastPage(),
                'per_page' => $vouchers->perPage(),
                'from' => $vouchers->firstItem(),
                'to' => $vouchers->lastItem(),
            ]
        );
    }

    /**
     * Store a new voucher in the system.
     *
     * @param  Request  $request
     * @return VoucherResource
     */
    public function store(CreateVoucherRequest $request)
    {
        $data = $request->validated();

        $voucher = Voucher::create($data);

        return ApiResponseService::created(VoucherResource::make($voucher)->toArray($request));
    }

    /**
     * Show the specified voucher.
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

        return ApiResponseService::success(VoucherResource::make($voucherQuery)->toArray($request));
    }

    /**
     * Update the specified voucher in the system.
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

        return ApiResponseService::success(VoucherResource::make($voucher->fresh())->toArray($request));
    }

    /**
     * Remove the specified voucher from the system.
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

        return ApiResponseService::noContent();
    }
}
