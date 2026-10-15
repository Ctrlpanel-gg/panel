<?php

namespace App\Classes;

use App\Facades\Currency;
use Spatie\LaravelSettings\Settings;

class GatewayFeeSettings
{
    public const KEYS = ['fee_type', 'fee_percent', 'fee_fixed', 'fee_min', 'fee_max'];

    /**
     * Whether the given settings class belongs to a payment gateway extension.
     * Gateways follow the naming convention "<Name>Settings" ↔ "<Name>Extension",
     * where the extension is a subclass of PaymentExtension.
     */
    public static function isGatewaySettings(string $settingsClass): bool
    {
        if (!class_exists($settingsClass) || !is_subclass_of($settingsClass, Settings::class)) {
            return false;
        }

        $extensionClass = preg_replace('/Settings$/', 'Extension', $settingsClass);

        return class_exists($extensionClass) && is_subclass_of($extensionClass, PaymentExtension::class);
    }

    /**
     * Section definition for the core-managed fee fields.
     *
     * @return array<string, array<string, string>>
     */
    public static function sections(): array

    {
        return [
            'fees' => [
                'label' => 'Payment Fee',
                'description' => 'Optional commission charged to the customer for accepting this payment method',
            ],
        ];
    }

    /**
     * Option metadata injected into the gateway settings page by the core.
     *
     * @return array<string, array<string, mixed>>
     */
    public static function optionDefinitions(): array

    {
        return [
            'fee_type' => [
                'type' => 'select',
                'label' => 'Fee Type',
                'description' => 'How the payment fee is calculated: none, a percentage of the amount, or a fixed amount',
                'options' => [
                    'none' => 'None',
                    'percent' => 'Percentage',
                    'fixed' => 'Fixed amount',
                ],
                'section' => 'fees',
            ],
            'fee_percent' => [
                'type' => 'number',
                'label' => 'Fee Percentage',
                'description' => 'Percentage of the payment amount charged to the customer whenthe fee type is Percentage',
                'step' => '0.01',
                'section' => 'fees',
                'visible_when' => [
                    'fee_type' => 'percent',
                ],
                'suffix' => [
                    'depends_on' => 'fee_type',
                    'map' => ['percent' => '%'],
                ],
            ],
            'fee_fixed' => [
                'type' => 'number',
                'label' => 'Fixed Fee',
                'description' => 'Fixed amount in the store currency charged to the customer whenthe fee type is Fixed',
                'step' => '0.01',
                'section' => 'fees',
                'visible_when' => [
                    'fee_type' => 'fixed',
                ],
                'mustBeConverted' => true,
            ],
            'fee_min' => [
                'type' => 'number',
                'label' => 'Minimum Fee',
                'description' => 'Minimum commission applied when the fee is a percentage (in the store currency)',
                'step' => '0.01',
                'section' => 'fees',
                'visible_when' => [
                    'fee_type' => 'percent',
                ],
                'mustBeConverted' => true,
            ],
            'fee_max' => [
                'type' => 'number',
                'label' => 'Maximum Fee',
                'description' => 'Maximum commission applied whenthe fee is a percentage (in the store currency)',
                'step' => '0.01',
                'section' => 'fees',
                'visible_when' => [
                    'fee_type' => 'percent',
                ],
                'mustBeConverted' => true,
            ],
        ];
    }

    /**
     * Read the current fee values from the settings repository without requiring
     * the settings class to declare fee properties. Missing rows fall back
     * to the defaults so fresh installs need no fee-specific migrations.
     *
     * @return array<string, mixed>
     */
    public static function values(Settings $settings): array
    {
        $payload = $settings->settingsConfig()->getRepository()->getPropertiesInGroup($settings->settingsConfig()->getGroup());

        return [
            'fee_type' => (string) ($payload['fee_type'] ?? 'none'),
            'fee_percent' => (float) ($payload['fee_percent'] ?? 0),
            'fee_fixed' => (int) ($payload['fee_fixed'] ?? 0),
            'fee_min' => (int) ($payload['fee_min'] ?? 0),
            'fee_max' => (int) ($payload['fee_max'] ?? 0),
        ];
    }

    /**
     * Persist fee values from a settings form submission, applying the same
     * thousandths conversion as other currency settings. Rows that do not exist
     * yet are created, so gateways need no fee-specific migrations.
     */
    public static function saveFromRequest(Settings $settings, array $input): void
    {
        $payload = [];

        foreach (self::KEYS as $key) {
            $value = $input[$key] ?? null;

            if (in_array($key, ['fee_fixed', 'fee_min', 'fee_max'], true)) {
                $value = $value === null || $value === '' ? 0 : Currency::prepareForDatabase($value);
            } elseif ($key === 'fee_percent') {
                $value = $value === null || $value === '' ? 0 : (float) $value;
            } else {
                $value = $value === null || $value === '' ? 'none' : (string) $value;
            }

            $payload[$key] = $value;
        }

        $settings->settingsConfig()->getRepository()->updatePropertiesPayload(
            $settings->settingsConfig()->getGroup(),
            $payload
        );
    }
}