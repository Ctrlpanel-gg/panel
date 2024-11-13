<?php

namespace App\Traits;

use App\Settings\CouponSettings;
use App\Models\Coupon as CouponModel;
use App\Models\ShopProduct;
use App\Models\User;
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

            if ($coupon->isMaxUsesReached($requestUser, $coupon_settings)) {
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

    public function isCouponValid(string $couponCode, User $user, string $productId): bool
    {
        if (is_null($couponCode)) return false;

        $coupon = CouponModel::where('code', $couponCode)->firstOrFail();
        $shopProduct = ShopProduct::findOrFail($productId);

        if ($coupon->getStatus() == 'USES_LIMIT_REACHED') {
            return false;
        }

        if ($coupon->getStatus() == 'EXPIRED') {
            return false;
        }

        if ($coupon->isMaxUsesReached($user)) {
            return false;
        }

        if ($coupon->type === 'amount' && $coupon->value >= $shopProduct->price) {
            return false;
        }

        return true;
    }

    public function applyCoupon(string $couponCode, float $price)
    {
        $coupon = CouponModel::where('code', $couponCode)->first();

        if ($coupon->type === 'percentage') {
            return $price - ($price * $coupon->value / 100);
        }

        if ($coupon->type === 'amount') {
            // There is no discount if the value of the coupon is greater than or equal to the value of the product.
            if ($coupon->value >= $price) {
                return $price;
            }
        }

        return $price - $coupon->value;
    }
}
