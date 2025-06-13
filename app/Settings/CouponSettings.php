<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class CouponSettings extends Settings
{
    public bool $enabled = false;
    public ?bool $delete_coupon_on_expires = false;
    public ?bool $delete_coupon_on_uses_reached = false;
    public ?int $max_uses_per_user = null;

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
            'enabled' => "nullable|boolean",
            'delete_coupon_on_expires' => 'nullable|boolean',
            'delete_coupon_on_uses_reached' => 'nullable|boolean',
            'max_uses_per_user' => 'nullable|integer',
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
            'position' => 10,
            'enabled' => [
                'label' => 'Enable Coupons',
                'type' => 'boolean',
                'description' => 'Enables coupons to be used in the store.'
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
            ],
            'max_uses_per_user' => [
                'label' => 'Max Uses Per User',
                'type' => 'number',
                'description' => 'Maximum number of uses that a user can make of the same coupon.'
            ],
        ];
    }
}
