<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class DefaultThemeSettings extends Settings
{
    public ?string $force_theme_mode;
    public string $default_theme;

    // Primary Colors
    public string $primary_100;
    public string $primary_200;
    public string $primary_300;
    public string $primary_400;
    public string $primary_500;
    public string $primary_600;
    public string $primary_700;

    // Accent Colors (9 Steps)
    public string $accent_50;
    public string $accent_100;
    public string $accent_200;
    public string $accent_300;
    public string $accent_400;
    public string $accent_500;
    public string $accent_600;
    public string $accent_700;
    public string $accent_800;

    // Status Colors
    public string $success;
    public string $warning;
    public string $info;
    public string $danger;
    public string $cyan;

    // Neutral Colors
    public string $gray_900;
    public string $gray_800;
    public string $gray_700;
    public string $gray_600;
    public string $gray_500;
    public string $gray_400;
    public string $gray_300;
    public string $gray_200;
    public string $gray_100;
    public string $gray_50;

    public static function group(): string
    {
        return 'theming';
    }
        public static function getOptionInputData()
    {
        return [
            'category_icon' => 'fas fa-paint-roller',
            'force_theme_mode' => [
                'label' => 'Force Theme Mode',
                'description' => 'Forces the selected mode to be applied to the dashboard regarless of user\'s settings, and disables dark / light mode switch',
                'type' => 'select',
                'options' => [
                    null => 'Disabled',
                    'dark' => 'Dark',
                    'light' => 'Light',
                ],
            ],
            'default_theme' => [
                'label' => 'Default Theme',
                'description' => 'Default theme to apply when user doesn\'t have a theme set locally. Dark mode is recommended. `system` makes the dashboard use user\'s system defined theme',
                'type' => 'select',
                'options' => [
                    'system' => 'System',
                    'dark' => 'Dark',
                    'light' => 'Light',
                ],
            ],
            'primary_100' => [
                'type' => 'string',
                'identifier' => 'color',
                'label' => 'Primary 100',
                'description' => 'Lightest primary color. Used for subtle backgrounds and hover states.',
                'options' => [
                    'default' => '#f1f5f9',
                ],
            ],
            'primary_200' => [
                'type' => 'string',
                'identifier' => 'color',
                'label' => 'Primary 200',
                'description' => 'Light primary color. Used for disabled states and secondary backgrounds.',
                'options' => [
                    'default' => '#e2e8f0',
                ],
            ],
            'primary_300' => [
                'type' => 'string',
                'identifier' => 'color',
                'label' => 'Primary 300',
                'description' => 'Medium-light primary color. Used for borders and subtle accents.',
                'options' => [
                    'default' => '#cbd5e1',
                ],
            ],
            'primary_400' => [
                'type' => 'string',
                'identifier' => 'color',
                'label' => 'Primary 400',
                'description' => 'Medium primary color. Used for secondary buttons and interactive elements.',
                'options' => [
                    'default' => '#94a3b8',
                ],
            ],
            'primary_500' => [
                'type' => 'string',
                'identifier' => 'color',
                'label' => 'Primary 500',
                'description' => 'Main primary color. Used for primary buttons, links, and key UI elements.',
                'options' => [
                    'default' => '#64748b',
                ],
            ],
            'primary_600' => [
                'type' => 'string',
                'identifier' => 'color',
                'label' => 'Primary 600',
                'description' => 'Dark primary color. Used for active states and hover effects on primary elements.',
                'options' => [
                    'default' => '#475569',
                ],
            ],
            'primary_700' => [
                'type' => 'string',
                'identifier' => 'color',
                'label' => 'Primary 700',
                'description' => 'Darkest primary color. Used for strong emphasis, focus states, and text on light backgrounds.',
                'options' => [
                    'default' => '#334155',
                ],
            ],
            'accent_50' => [
                'type' => 'string',
                'identifier' => 'color',
                'label' => 'Accent 50',
                'description' => 'Ultra-light accent color. Used for very subtle backgrounds.',
                'options' => [
                    'default' => '#f0f8ff',
                ],
            ],
            'accent_100' => [
                'type' => 'string',
                'identifier' => 'color',
                'label' => 'Accent 100',
                'description' => 'Lightest accent color. Used for subtle backgrounds and hover states.',
                'options' => [
                    'default' => '#e6eaf8',
                ],
            ],
            'accent_200' => [
                'type' => 'string',
                'identifier' => 'color',
                'label' => 'Accent 200',
                'description' => 'Light accent color. Used for disabled states and secondary backgrounds.',
                'options' => [
                    'default' => '#d4d9f7',
                ],
            ],
            'accent_300' => [
                'type' => 'string',
                'identifier' => 'color',
                'label' => 'Accent 300',
                'description' => 'Medium-light accent color. Used for borders and subtle accents.',
                'options' => [
                    'default' => '#b8c5f0',
                ],
            ],
            'accent_400' => [
                'type' => 'string',
                'identifier' => 'color',
                'label' => 'Accent 400',
                'description' => 'Medium accent color. Used for secondary buttons and interactive elements.',
                'options' => [
                    'default' => '#7fa3e8',
                ],
            ],
            'accent_500' => [
                'type' => 'string',
                'identifier' => 'color',
                'label' => 'Accent 500',
                'description' => 'Main accent color. Used for highlight buttons and key UI elements.',
                'options' => [
                    'default' => '#3062a3',
                ],
            ],
            'accent_600' => [
                'type' => 'string',
                'identifier' => 'color',
                'label' => 'Accent 600',
                'description' => 'Dark accent color. Used for active states and hover effects on accent elements.',
                'options' => [
                    'default' => '#2962cc',
                ],
            ],
            'accent_700' => [
                'type' => 'string',
                'identifier' => 'color',
                'label' => 'Accent 700',
                'description' => 'Darkest accent color. Used for strong emphasis and focus states.',
                'options' => [
                    'default' => '#0e1434',
                ],
            ],
            'accent_800' => [
                'type' => 'string',
                'identifier' => 'color',
                'label' => 'Accent 800 (Cyan Glow)',
                'description' => 'Bright cyan glow effect. Used for special highlights and glow effects.',
                'options' => [
                    'default' => '#2ef2ff',
                ],
            ],
            'success' => [
                'type' => 'string',
                'identifier' => 'color',
                'label' => 'Success Color',
                'description' => 'Color used for success messages, positive indicators, and confirmed actions.',
                'options' => [
                    'default' => '#10b981',
                ],
            ],
            'warning' => [
                'type' => 'string',
                'identifier' => 'color',
                'label' => 'Warning Color',
                'description' => 'Color used for warning messages, alerts, and pending actions.',
                'options' => [
                    'default' => '#f59e0b',
                ],
            ],
            'info' => [
                'type' => 'string',
                'identifier' => 'color',
                'label' => 'Info Color',
                'description' => 'Color used for informational messages and general notices.',
                'options' => [
                    'default' => '#0ea5e9',
                ],
            ],
            'danger' => [
                'type' => 'string',
                'identifier' => 'color',
                'label' => 'Danger Color',
                'description' => 'Color used for error messages, danger alerts, and destructive actions.',
                'options' => [
                    'default' => '#ef4444',
                ],
            ],
            'cyan' => [
                'type' => 'string',
                'identifier' => 'color',
                'label' => 'Cyan Color',
                'description' => 'Bright cyan color. Used for special highlights, glow effects, and accents.',
                'options' => [
                    'default' => '#06b6d4',
                ],
            ],
            'gray_50' => [
                'type' => 'string',
                'identifier' => 'color',
                'label' => 'Gray 50',
                'description' => 'Lightest gray. Used for text and elements on dark backgrounds.',
                'options' => [
                    'default' => '#f9fafb',
                ],
            ],
            'gray_100' => [
                'type' => 'string',
                'identifier' => 'color',
                'label' => 'Gray 100',
                'description' => 'Light gray. Used for secondary text and subtle UI elements.',
                'options' => [
                    'default' => '#f4f5f7',
                ],
            ],
            'gray_200' => [
                'type' => 'string',
                'identifier' => 'color',
                'label' => 'Gray 200',
                'description' => 'Medium-light gray. Used for borders and separators in dark mode.',
                'options' => [
                    'default' => '#e5e7eb',
                ],
            ],
            'gray_300' => [
                'type' => 'string',
                'identifier' => 'color',
                'label' => 'Gray 300',
                'description' => 'Medium gray. Used for borders, disabled text, and secondary elements.',
                'options' => [
                    'default' => '#d5d6d7',
                ],
            ],
            'gray_400' => [
                'type' => 'string',
                'identifier' => 'color',
                'label' => 'Gray 400',
                'description' => 'Medium-dark gray. Used for prominent borders and secondary text.',
                'options' => [
                    'default' => '#c6c6c6',
                ],
            ],
            'gray_500' => [
                'type' => 'string',
                'identifier' => 'color',
                'label' => 'Gray 500',
                'description' => 'Medium dark gray. Used for body text and secondary headings.',
                'options' => [
                    'default' => '#707275',
                ],
            ],
            'gray_600' => [
                'type' => 'string',
                'identifier' => 'color',
                'label' => 'Gray 600',
                'description' => 'Dark gray. Used for primary text and headings on dark backgrounds.',
                'options' => [
                    'default' => '#4c4f52',
                ],
            ],
            'gray_700' => [
                'type' => 'string',
                'identifier' => 'color',
                'label' => 'Gray 700',
                'description' => 'Very dark gray. Used for secondary backgrounds and cards.',
                'options' => [
                    'default' => '#24262d',
                ],
            ],
            'gray_800' => [
                'type' => 'string',
                'identifier' => 'color',
                'label' => 'Gray 800',
                'description' => 'Near black gray. Used for primary backgrounds and main UI surfaces.',
                'options' => [
                    'default' => '#1a1c23',
                ],
            ],
            'gray_900' => [
                'type' => 'string',
                'identifier' => 'color',
                'label' => 'Gray 900',
                'description' => 'Darkest gray (near black). Used for main dark mode background.',
                'options' => [
                    'default' => '#121317',
                ],
            ],
        ];
    }
}
