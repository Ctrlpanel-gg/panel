<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class CouponSettings extends Settings
{
	public ?int $max_uses_per_user;
    public ?bool $delete_coupon_on_expires;
    public ?bool $delete_coupon_on_uses_reached;

    public static function group(): string
    {
        return 'coupon';
    }

		/**
     * Summary of validations array
     * @return array<string, string>
     */
    public static function getValidations()
    {
        return [
            'max_uses_per_user' => 'required|integer',
            'delete_coupon_on_expires' => 'required|boolean',
            'delete_coupon_on_uses_reached' => 'required|boolean',
        ];
    }

		/**
     * Summary of optionInputData array
     * Only used for the settings page
     * @return array<array<'type'|'label'|'description'|'options', string|bool|float|int|array<string, string>>>
     */
    public static function getOptionInputData()
    {
        return [
            "category_icon" => "fas fa-ticket-alt",
            'max_uses_per_user' => [
                'label' => 'Max Uses Per User',
                'type' => 'number',
                'description' => 'Maximum number of uses that a user can make of the same coupon.'
            ],
            'delete_coupon_on_expires' => [
                'label' => 'Auto Delete Expired Coupons',
                'type' => 'boolean',
                'description' => 'Automatically deletes the coupon if it expires.'
            ],
            'delete_coupon_on_uses_reached' => [
                'label' => 'Delete Coupon When Max Uses Reached',
                'type' => 'boolean',
                'description' => 'Delete a coupon as soon as its maximum usage is reached.'
            ]
        ];
    }
}
