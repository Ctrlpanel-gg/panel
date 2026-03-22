<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Concerns\InteractsWithScopedApiTokens;
use App\Models\Voucher;
use App\Http\Resources\VoucherResource;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Vouchers\CreateVoucherRequest;
use App\Http\Requests\Api\Vouchers\UpdateVoucherRequest;
use Spatie\QueryBuilder\QueryBuilder;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class VoucherController extends Controller
{
    use InteractsWithScopedApiTokens;

    const ALLOWED_INCLUDES = ['users'];
    const ALLOWED_FILTERS = ['code', 'memo', 'credits', 'uses'];

    /**
     * Show a list of vouchers.
     *
     * @param Request $request
     * @return VoucherResource
     */
    public function index(Request $request)
    {
        $this->ensureGlobalToken($request);

        $vouchers = QueryBuilder::for(Voucher::class)
            ->allowedIncludes(self::ALLOWED_INCLUDES)
            ->allowedFilters(self::ALLOWED_FILTERS)
            ->paginate($request->input('per_page') ?? 50);

        return VoucherResource::collection($vouchers);
    }

    /**
     * Store a new voucher in the system.
     *
     * @param  Request  $request
     * @return VoucherResource
     */
    public function store(CreateVoucherRequest $request)
    {
        $this->ensureGlobalToken($request);

        $data = $request->validated();
        
        $voucher = Voucher::create($data);

        return VoucherResource::make($voucher);
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
    public function show(Request $request, Voucher $voucher)
    {
        $this->ensureGlobalToken($request);

        $voucherQuery = QueryBuilder::for(Voucher::class)
            ->allowedIncludes(self::ALLOWED_INCLUDES)
            ->whereKey($voucher->id)
            ->firstOrFail();

        return VoucherResource::make($voucherQuery);
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
        $this->ensureGlobalToken($request);

        $data = $request->validated();

        $voucher->update($data);

        return VoucherResource::make($voucher->fresh());
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
        $this->ensureGlobalToken($request);

        $voucher->delete();

        return response()->noContent();
    }
}
