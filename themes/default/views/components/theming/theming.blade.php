@php
    function hexToRgb($hex)
    {
        $hex = ltrim($hex, '#');
        if (strlen($hex) == 3) {
            $hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
        }
        $r = hexdec(substr($hex, 0, 2));
        $g = hexdec(substr($hex, 2, 2));
        $b = hexdec(substr($hex, 4, 2));
        return "$r $g $b";
    }

    $theme = app(\App\Settings\DefaultThemeSettings::class);
@endphp

<style>
    :root {
        /* Primary Colors */
        --primary-100: {{ hexToRgb($theme->primary_100) }};
        --primary-200: {{ hexToRgb($theme->primary_200) }};
        --primary-300: {{ hexToRgb($theme->primary_300) }};
        --primary-400: {{ hexToRgb($theme->primary_400) }};
        --primary-500: {{ hexToRgb($theme->primary_500) }};
        --primary-600: {{ hexToRgb($theme->primary_600) }};
        --primary-700: {{ hexToRgb($theme->primary_700) }};

        /* Accent Colors */
        --accent-50: {{ hexToRgb($theme->accent_50) }};
        --accent-100: {{ hexToRgb($theme->accent_100) }};
        --accent-200: {{ hexToRgb($theme->accent_200) }};
        --accent-300: {{ hexToRgb($theme->accent_300) }};
        --accent-400: {{ hexToRgb($theme->accent_400) }};
        --accent-500: {{ hexToRgb($theme->accent_500) }};
        --accent-600: {{ hexToRgb($theme->accent_600) }};
        --accent-700: {{ hexToRgb($theme->accent_700) }};
        --accent-800: {{ hexToRgb($theme->accent_800) }};

        /* Status Colors */
        --success: {{ hexToRgb($theme->success) }};
        --warning: {{ hexToRgb($theme->warning) }};
        --info: {{ hexToRgb($theme->info) }};
        --danger: {{ hexToRgb($theme->danger) }};
        --cyan: {{ hexToRgb($theme->cyan) }};

        /* Neutral Colors */
        --gray-900: {{ hexToRgb($theme->gray_900) }};
        --gray-800: {{ hexToRgb($theme->gray_800) }};
        --gray-700: {{ hexToRgb($theme->gray_700) }};
        --gray-600: {{ hexToRgb($theme->gray_600) }};
        --gray-500: {{ hexToRgb($theme->gray_500) }};
        --gray-400: {{ hexToRgb($theme->gray_400) }};
        --gray-300: {{ hexToRgb($theme->gray_300) }};
        --gray-200: {{ hexToRgb($theme->gray_200) }};
        --gray-100: {{ hexToRgb($theme->gray_100) }};
        --gray-50: {{ hexToRgb($theme->gray_50) }};
        /* Scrollbar variables (used throughout the app) */
        --scroll-size: 8px;
        --scroll-radius: 8px;
        /* Track should be near-transparent, use subtle background color in dark theme */
        --scroll-track: rgb(var(--gray-900) / 0%);
        /* Thumb color derived from gray-600 with alpha, used for scroll handle */
        --scroll-thumb-color: rgb(var(--gray-600) / 0.35);
        --scroll-thumb-hover: rgb(var(--gray-500) / 0.55);
    }
</style>
