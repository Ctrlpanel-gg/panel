<?php

namespace App\Helpers;

use NumberFormatter;

class CurrencyHelper
{
    public function convertForDisplay($amount)
    {
        return $amount / 1000;
    }

    /**
     * Gets the effective locale to use for formatting, considering global overrides.
     *
     * @param string|null $locale The requested locale
     * @param bool $ignoreOverride Whether to ignore the global override setting
     * @return string The effective locale to use
     */
    // Simple cache to avoid resolving settings repeatedly in tight loops
    private static array $effectiveLocaleCache = [];

    private function getEffectiveLocale($locale = null, $ignoreOverride = false)
    {
        $cacheKey = ($locale ?? '') . '|' . ($ignoreOverride ? '1' : '0');
        if (isset(self::$effectiveLocaleCache[$cacheKey])) {
            return self::$effectiveLocaleCache[$cacheKey];
        }

        $effectiveLocale = $locale ?: str_replace('_', '-', app()->getLocale());

        if (!$ignoreOverride) {
            $override = resolve(\App\Settings\GeneralSettings::class)->currency_format_override ?? null;
            if ($override) {
                $effectiveLocale = $override;
            }
        }

        // normalize (e.g., bg_BG -> bg-BG)
        $effectiveLocale = str_replace('_', '-', $effectiveLocale);

        self::$effectiveLocaleCache[$cacheKey] = $effectiveLocale;

        return $effectiveLocale;
    }

    /**
     * Formats a currency amount for display.
     *
     * @param mixed $amount The amount to format.
     * @param int $decimals Number of decimal places to use.
     * @param string|null $locale The locale to use for formatting (defaults to current application locale).
     * @param bool $ignoreOverride When true, bypasses the global currency format override setting.
     * @return string The formatted currency string.
     */
    public function formatForDisplay($amount, $decimals = 2, $locale = null, $ignoreOverride = false)
    {
        $locale = $this->getEffectiveLocale($locale, $ignoreOverride);

        $display = $this->convertForDisplay($amount);

        // For Bulgarian ('bg'), Spanish ('es'), and Polish ('pl') locales: For small numbers, prefer comma as decimal separator and no thousands separator.
        // Accept locale variants like 'bg-BG' by matching the prefix.
        $specialLocales = ['bg', 'es', 'pl'];
        $prefix = strtolower(explode('-', $locale)[0]);
        if (in_array($prefix, $specialLocales, true) && $display <= 9999) {
            return number_format($display, $decimals, ',', '');
        }

        // Wrap intl usage in try/catch so environments without the PHP intl extension don't crash.
        try {
            $formatter = new NumberFormatter($locale, NumberFormatter::DECIMAL);
            $formatter->setAttribute(NumberFormatter::MIN_FRACTION_DIGITS, $decimals);
            $formatter->setAttribute(NumberFormatter::MAX_FRACTION_DIGITS, $decimals);
            $result = $formatter->format($display);
            if ($result === false) {
                throw new \RuntimeException('NumberFormatter failed to format');
            }

            return $result;
        } catch (\Throwable $e) {
            // Fallback: simple locale-aware formatting without relying on intl.
            // Determine locale prefix (e.g., "bg-BG" -> "bg")
            $prefix = strtolower(explode('-', (string) $locale)[0]);

            // Locales that commonly use comma as decimal separator
            $europeanStyleLocales = ['bg', 'cs', 'da', 'de', 'es', 'fi', 'fr', 'hu', 'it', 'nl', 'no', 'pl', 'pt', 'ro', 'ru', 'sk', 'sl', 'sv', 'tr'];

            $decimalSeparator = '.';
            $thousandsSeparator = ',';
            if (in_array($prefix, $europeanStyleLocales, true)) {
                $decimalSeparator = ',';
                $thousandsSeparator = '.';
            }

            try {
                logger()->warning('CurrencyHelper::formatForDisplay fell back to PHP number_format', [
                    'locale' => $locale,
                    'prefix' => $prefix,
                    'decimals' => $decimals,
                    'error' => $e->getMessage(),
                ]);
            } catch (\Throwable $logEx) {
                // swallow logging errors to avoid impacting formatting
            }

            return number_format($display, $decimals, $decimalSeparator, $thousandsSeparator);
        }
    }

    public function formatForForm($amount, $decimals = 2)
    {
        return number_format($this->convertForDisplay($amount), $decimals, '.', '');
    }

    public function prepareForDatabase($amount)
    {
        return (int)($amount * 1000);
    }

    public function formatToCurrency(int $amount, $currency_code, $locale = null,)
    {
        $locale = $locale ?: str_replace('_', '-', app()->getLocale());

        try {
            $formatter = new NumberFormatter($locale, NumberFormatter::CURRENCY);
            $result = $formatter->formatCurrency($this->convertForDisplay($amount), $currency_code);
            if ($result === false) {
                throw new \RuntimeException('NumberFormatter failed to format currency');
            }

            return $result;
        } catch (\Throwable $e) {
            // Fallback: return a simple formatted number with the currency code appended
            return number_format($this->convertForDisplay($amount), 2, '.', ',') . ' ' . strtoupper($currency_code);
        }
    }
}
