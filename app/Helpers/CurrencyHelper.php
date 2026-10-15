<?php

namespace App\Helpers;

use NumberFormatter;

class CurrencyHelper
{
    public function convertForDisplay($amount)
    {
        return $amount / 1000;
    }

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

    public function formatForDisplay($amount, $decimals = 2, $locale = null, $ignoreOverride = false)
    {
        $locale = $this->getEffectiveLocale($locale, $ignoreOverride);
        $display = $this->convertForDisplay($amount);

        // Keep smaller numbers readable for locales where grouping often starts at 10k.
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

    public function formatForCommands($amount)
    {
        return $this->convertForDisplay($amount);
    }
}
