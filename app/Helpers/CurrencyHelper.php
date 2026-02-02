<?php

namespace App\Helpers;

use NumberFormatter;

class CurrencyHelper
{
    private function convertForDisplay($amount)
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
    private function getEffectiveLocale($locale = null, $ignoreOverride = false)
    {
        $effectiveLocale = $locale ?: str_replace('_', '-', app()->getLocale());

        if (!$ignoreOverride) {
            $override = resolve(\App\Settings\GeneralSettings::class)->currency_format_override ?? null;
            if ($override) {
                $effectiveLocale = $override;
            }
        }

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

        // For Bulgarian ('bg'), Spanish ('es'), and Polish ('pl') locales: For numbers <= 9999, use comma as decimal separator and no thousands separator.
        // This follows common formatting conventions for small numbers in these locales, as per CLDR and local usage.
        // source: https://forum.opencart.com/viewtopic.php?t=144907
        $specialLocales = ['bg', 'es', 'pl'];
        if (in_array($locale, $specialLocales, true) && $display <= 9999) {
            return number_format($display, $decimals, ',', '');
        }
        $formatter = new NumberFormatter($locale, NumberFormatter::DECIMAL);
        $formatter->setAttribute(NumberFormatter::MIN_FRACTION_DIGITS, $decimals);
        $formatter->setAttribute(NumberFormatter::MAX_FRACTION_DIGITS, $decimals);
        return $formatter->format($display);
    }

    public function formatForForm($amount, $decimals = 2)
    {
        return number_format($this->convertForDisplay($amount), $decimals, '.', '');
    }

    public function prepareForDatabase($amount)
    {
        return (int)($amount * 1000);
    }

    public function formatToCurrency(int $amount, $currency_code, $locale = null)
    {
        $locale = $this->getEffectiveLocale($locale, false);

        $formatter = new NumberFormatter($locale, NumberFormatter::CURRENCY);

        return $formatter->formatCurrency($this->convertForDisplay($amount), $currency_code);
    }

    /**
     * Formats the given amount for use in commands.
     *
     * Converts the amount from the smallest currency unit to a float.
     *
     * @param int $amount Amount in the smallest currency unit (e.g., thousandths).
     * @return float Converted amount for commands.
     */
    public function formatForCommands($amount)
    {
        return $this->convertForDisplay($amount);
    }
}
