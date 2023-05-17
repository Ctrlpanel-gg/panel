<?php

namespace App\Traits;

use App\Settings\CouponSettings;
use App\Models\Coupon as CouponModel;
use App\Models\ShopProduct;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use stdClass;

trait Coupon
{
    public function validateCoupon(Request $request): JsonResponse
    {
        $coupon = CouponModel::where('code', $request->input('coupon_code'))->first();
        $coupon_settings = new CouponSettings;
        $response = response()->json([
            'isValid' => false,
            'error' => __('This coupon does not exist.')
        ], 404);

        if (!is_null($coupon)) {
            if ($coupon->getStatus() == 'USES_LIMIT_REACHED') {
                $response = response()->json([
                    'isValid' => false,
                    'error' => __('This coupon has reached the maximum amount of uses.')
                ], 422);

                return $response;
            }

            if ($coupon->getStatus() == 'EXPIRED') {
                $response = response()->json([
                    'isValid' => false,
                    'error' => __('This coupon has expired.')
                ], 422);

                return $response;
            }

            if ($coupon->isLimitsUsesReached($request, $coupon_settings)) {
                $response = response()->json([
                    'isValid' => false,
                    'error' => __('You have reached the maximum uses of this coupon.')
                ], 422);

                return $response;
            }

            $response = response()->json([
                'isValid' => true,
                'couponCode' => $coupon->code,
                'couponType' => $coupon->type,
                'couponValue' => $coupon->value
            ]);
        }

        return $response;
    }

    public function calcDiscount(ShopProduct $product, stdClass $data)
    {

        if ($data->isValid) {
            $productPrice = $product->price;

            if (is_string($productPrice)) {
                $productPrice = floatval($product->price);
            }

            if ($data->couponType === 'percentage') {
                return $productPrice - ($productPrice * $data->couponValue / 100);
            }

            if ($data->couponType === 'amount') {
                // There is no discount if the value of the coupon is greater than or equal to the value of the product.
                if ($data->couponValue >= $productPrice) {
                    return $productPrice;
                }
            }

            return $productPrice - $data->couponValue;
        }
    }
}
