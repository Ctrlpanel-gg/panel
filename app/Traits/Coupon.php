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
    public function validateCoupon($requestUser, $couponCode, $productId): JsonResponse
    {
        $coupon = CouponModel::where('code', $couponCode)->first();
        $shopProduct = ShopProduct::findOrFail($productId);
        $coupon_settings = new CouponSettings;
        $response = response()->json([
            'isValid' => false,
            'error' => __('This coupon does not exist.')
        ], 404);

        if (!is_null($coupon)) {
            if (is_string($coupon->value)) {
                $coupon->value = floatval($coupon->value);
            }

            if (is_string($shopProduct->price)) {
                $shopProduct->price = floatval($shopProduct->price);
            }

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

            if ($coupon->isLimitsUsesReached($requestUser, $coupon_settings)) {
                $response = response()->json([
                    'isValid' => false,
                    'error' => __('You have reached the maximum uses of this coupon.')
                ], 422);

                return $response;
            }

            if ($coupon->type === 'amount' && $coupon->value >= $shopProduct->price) {
                $response = response()->json([
                    'isValid' => false,
                    'error' => __('The coupon you are trying to use would give you 100% off, so it cannot be used for this product, sorry.')
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

    public function calcDiscount($productPrice, stdClass $data)
    {

        if ($data->isValid) {
            if (is_string($productPrice)) {
                $productPrice = floatval($productPrice);
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

        return $productPrice;
    }
}
