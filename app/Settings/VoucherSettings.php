<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class VoucherSettings extends Settings
{
    public bool $delete_voucher_on_expires = false;
    public bool $delete_voucher_on_uses_reached = false;

    public static function group(): string
    {
        return 'voucher';
    }

    /**
     * Summary of validations array
     * @return array<string, string>
     */
    public static function getValidations()
    {
        return [
            'delete_voucher_on_expires' => 'nullable|boolean',
            'delete_voucher_on_uses_reached' => 'nullable|boolean',
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
            "category_icon" => "fas fa-money-check-alt",
            'position' => 11,
            'delete_voucher_on_expires' => [
                'label' => 'Auto Delete Expired Vouchers',
                'type' => 'boolean',
                'description' => 'Automatically deletes the voucher if it expires.'
            ],
            'delete_voucher_on_uses_reached' => [
                'label' => 'Delete Voucher When Max Uses Reached',
                'type' => 'boolean',
                'description' => 'Delete a voucher as soon as its maximum usage is reached.'
            ],
        ];
    }
}
